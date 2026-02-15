<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LicensingService
{
    private const LICENSE_FILE = '.license.key';

    // PUBLIC KEY: This is used to verify the license (Owner's signature)
    // In a real scenario, you should replace this with your own public key.
    private const PUBLIC_KEY = <<<EOT
-----BEGIN PUBLIC KEY-----
MCowBQYDK2VwAyEAf2O0U8vO5lVPrK5k5z6T5Zp8Qx9vYf/kG3o5Y6Z7J5g=
-----END PUBLIC KEY-----
EOT;

    /**
     * Get the unique Hardware ID of this machine (Windows focus)
     */
    public function getMachineId(): string
    {
        try {
            // Get Motherboard Serial Number (Windows)
            $mbSerial = shell_exec('wmic baseboard get serialnumber');
            // Get CPU ID
            $cpuId = shell_exec('wmic cpu get processorid');

            $raw = trim($mbSerial) . trim($cpuId);

            // If empty (Linux or some VM), fallback to MAC address
            if (empty(trim($raw)) || stripos($mbSerial, 'SerialNumber') === false) {
                $raw = shell_exec('getmac');
            }

            return strtoupper(substr(hash('sha256', $raw), 0, 16));
        } catch (\Exception $e) {
            return 'TWINX-FALLBACK-ID';
        }
    }

    /**
     * Check if the system has a valid license
     */
    public function isActivated(): bool
    {
        $key = $this->getStoredLicense();
        if (!$key)
            return false;

        return $this->verifyLicense($key);
    }

    /**
     * Verify the license key against the Machine ID and signature
     */
    public function verifyLicense(string $key): bool
    {
        try {
            $decoded = json_decode(base64_decode($key), true);
            if (!$decoded || !isset($decoded['machine_id'], $decoded['signature'])) {
                return false;
            }

            // 1. Check if the Machine ID matches
            if ($decoded['machine_id'] !== $this->getMachineId()) {
                return false;
            }

            // 2. Check Expiry in the KEY (Offline Check)
            if (isset($decoded['expires_at']) && strtotime($decoded['expires_at']) < time()) {
                return false;
            }

            // 3. Optional: Sync with Database (If available on same machine)
            // This allows the manager to "remotely" kill the app if they share the DB
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                try {
                    $db = new \PDO("sqlite:$dbPath");
                    $stmt = $db->prepare("SELECT expires_at, status FROM issued_licenses WHERE machine_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$this->getMachineId()]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($row) {
                        if ($row['status'] !== 'active' || strtotime($row['expires_at']) < time()) {
                            return false;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore DB errors, fall back to offline key verification
                }
            }

            // 4. Signature Verification
            $expectedSignature = 'TWINX-' . hash('sha256', $decoded['machine_id'] . ($decoded['client_name'] ?? '') . ($decoded['expires_at'] ?? ''));

            return $decoded['signature'] === $expectedSignature;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Store the license key locally
     */
    public function saveLicense(string $key): void
    {
        File::put(base_path(self::LICENSE_FILE), $key);
    }

    /**
     * Get stored license key
     */
    public function getStoredLicense(): ?string
    {
        $path = base_path(self::LICENSE_FILE);
        return File::exists($path) ? trim(File::get($path)) : null;
    }

    /**
     * Get details of the current license
     */
    public function getLicenseDetails(): ?array
    {
        $key = $this->getStoredLicense();
        $decoded = $key ? json_decode(base64_decode($key), true) : null;

        $clientName = $decoded['client_name'] ?? 'غير معروف';
        $expiresAt = $decoded['expires_at'] ?? null;
        $status = 'active';

        // Optional: Sync with Database (If available on same machine)
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            try {
                $db = new \PDO("sqlite:$dbPath");
                $stmt = $db->prepare("SELECT client_name, expires_at, status FROM issued_licenses WHERE machine_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$this->getMachineId()]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($row) {
                    $clientName = $row['client_name'];
                    $expiresAt = $row['expires_at'];
                    $status = $row['status'];
                }
            } catch (\Exception $e) {
            }
        }

        $expiry = $expiresAt ? strtotime($expiresAt) : null;
        $daysLeft = $expiry ? ceil(($expiry - time()) / 86400) : null;

        return [
            'client_name' => $clientName,
            'expires_at' => $expiresAt,
            'days_left' => (int) $daysLeft,
            'is_expired' => ($status !== 'active' || ($daysLeft !== null && $daysLeft <= 0)),
        ];
    }
}

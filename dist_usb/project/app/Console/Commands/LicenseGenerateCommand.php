<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LicenseGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate {machine_id : The Machine ID from the activation page} 
                            {client_name : Name of the client} 
                            {days=365 : Validity period in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a signed license key for Twinx ERP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $machineId = $this->argument('machine_id');
        $client = $this->argument('client_name');
        $days = (int) $this->argument('days');

        $expiresAt = now()->addDays($days)->toDateTimeString();

        $payload = [
            'machine_id' => strtoupper($machineId),
            'client_name' => $client,
            'expires_at' => $expiresAt,
            'features' => ['all'],
            'signature' => 'TWINX-' . hash('sha256', $machineId . $client . $expiresAt) // Simplified signature for demo
        ];

        $key = base64_encode(json_encode($payload));

        $this->info('--- LICENSE KEY GENERATED ---');
        $this->line($key);
        $this->info('-----------------------------');
        $this->warn("Client: {$client}");
        $this->warn("Expires: {$expiresAt}");
        $this->info('Copy the above key and send it to the client.');

        return 0;
    }
}

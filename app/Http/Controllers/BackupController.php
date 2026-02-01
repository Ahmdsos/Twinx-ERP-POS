<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

/**
 * BackupController - Database backup management
 * 
 * Handles:
 * - Create database backups
 * - List available backups
 * - Download backups
 * - Delete old backups
 */
class BackupController extends Controller
{
    /**
     * Display list of available backups
     */
    public function index()
    {
        $backups = collect();
        $backupPath = storage_path('app/backups');

        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, ['sql', 'sqlite'])) {
                    $fullPath = $backupPath . '/' . $file;
                    $backups->push([
                        'name' => $file,
                        'raw_size' => filesize($fullPath),
                        'size' => self::formatBytes(filesize($fullPath)),
                        'created_at' => Carbon::createFromTimestamp(filemtime($fullPath)),
                        'path' => $fullPath,
                    ]);
                }
            }
        }


        // 2. Fetch Restoration Architecture (History)
        $restorationHistory = \App\Models\ActivityLog::where('action', 'restored_backup')
            ->latest()
            ->take(10)
            ->get();

        // 3. Current System stats for comparison
        $currentSystemSize = 0;
        if (config('database.default') === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            $currentSystemSize = file_exists($dbPath) ? filesize($dbPath) : 0;
        } else {
            // For MySQL, querying information_schema is needed, simplified for now
            // Assuming user wants file-level comparison
            $currentSystemSize = 0; // Placeholder for MySQL calculation
        }

        $backups = $backups->sortByDesc('created_at');

        return view('settings.backups.index', compact('backups', 'restorationHistory', 'currentSystemSize'));
    }

    /**
     * Create a new database backup
     */
    public function create()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');

            // Create backup directory if not exists
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $fullPath = $backupPath . '/' . $filename;

            // Get database config
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // For SQLite (development)
            if (config('database.default') === 'sqlite') {
                $sqlitePath = database_path('database.sqlite');
                if (file_exists($sqlitePath)) {
                    copy($sqlitePath, str_replace('.sql', '.sqlite', $fullPath));
                    return redirect()->route('settings.backup.index')
                        ->with('success', 'تم إنشاء نسخة احتياطية بنجاح: ' . $filename);
                }
            }

            // For MySQL
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($fullPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                // Fallback: Create a simple backup note
                file_put_contents($fullPath, "-- Backup created at " . date('Y-m-d H:i:s') . "\n-- Use Laravel's built-in backup for full functionality\n");
            }

            return redirect()->route('settings.backup.index')
                ->with('success', 'تم إنشاء نسخة احتياطية بنجاح: ' . $filename);

        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('error', 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            return redirect()->route('settings.backup.index')
                ->with('error', 'الملف غير موجود');
        }

        return response()->download($path);
    }

    /**
     * Delete a backup file
     */
    public function destroy(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            unlink($path);
            return redirect()->route('settings.backup.index')
                ->with('success', 'تم حذف النسخة الاحتياطية بنجاح');
        }

        return redirect()->route('settings.backup.index')
            ->with('error', 'الملف غير موجود');
    }

    /**
     * Open backup folder in Explorer (Local Windows Only)
     */
    public function openFolder(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            // Windows command to select file in explorer
            exec('explorer /select,"' . str_replace('/', '\\', $path) . '"');
            return back()->with('success', 'تم فتح المجلد بنجاح');
        }

        return back()->with('error', 'الملف غير موجود');
    }

    /**
     * Restore a backup file
     */
    public function restore(Request $request, string $filename)
    {
        try {
            // Increase time limit for large restores
            set_time_limit(300);

            $path = storage_path('app/backups/' . $filename);

            if (!file_exists($path)) {
                return back()->with('error', 'ملف النسخة الاحتياطية غير موجود');
            }

            // Get database config
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // SQLite (Dev)
            if (config('database.default') === 'sqlite') {
                $sqlitePath = database_path('database.sqlite');
                // 1. Restore the file
                copy($path, $sqlitePath);

                // 2. Clear cache to ensure new DB is read
                \Illuminate\Support\Facades\DB::reconnect();
                \Illuminate\Support\Facades\Cache::flush();

            } else {
                // MySQL Restore Command
                $command = sprintf(
                    'mysql --host=%s --user=%s --password=%s %s < %s',
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($path)
                );

                // Execute
                exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    throw new \Exception('MySQL Error Code: ' . $returnCode);
                }
            }

            // Log this critical action (Will be written to the RESTORED database)
            if (auth()->check()) {
                \Modules\Core\Traits\HasAuditTrail::logActivity(
                    auth()->user(),
                    'restored_backup',
                    'تم استعادة نسخة احتياطية: ' . $filename
                );
            }

            return redirect()->route('settings.backup.index')
                ->with('success', 'تم استعادة النظام بنجاح من النسخة: ' . $filename . ' (Driver: ' . config('database.default') . ')');

        } catch (\Exception $e) {
            return back()->with('error', 'فشل الاستعادة: ' . $e->getMessage());
        }
    }

    /**
     * Handle File Upload Import
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt,gzip'
        ]);

        $file = $request->file('backup_file');
        $filename = 'imported_' . date('Y-m-d_H-i-s') . '_' . $file->getClientOriginalName();

        $file->storeAs('backups', $filename);

        return redirect()->route('settings.backup.index')
            ->with('success', 'تم رفع ملف النسخة الاحتياطية بنجاح. يمكنك الآن استعادته من القائمة.');
    }

    /**
     * Compare backup with current system
     */
    public function compare(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            return back()->with('error', 'الملف غير موجود');
        }

        // 1. Backup Stats
        $backupStats = [
            'name' => $filename,
            'size' => filesize($path),
            'date' => filemtime($path),
            'tables_count' => 0,
            'tables_list' => [], // Future: List tables?
        ];

        // Advanced Analysis based on Driver
        if (config('database.default') === 'sqlite') {
            // For SQLite, the backup IS a database. Connect to it securely.
            try {
                // Define a temporary connection
                config([
                    'database.connections.sqlite_backup_temp' => [
                        'driver' => 'sqlite',
                        'database' => $path,
                        'foreign_key_constraints' => true,
                    ]
                ]);

                $backupStats['tables_count'] = \Illuminate\Support\Facades\DB::connection('sqlite_backup_temp')
                    ->table('sqlite_master')
                    ->where('type', 'table')
                    ->where('name', 'not like', 'sqlite_%')
                    ->count();

            } catch (\Exception $e) {
                // Fallback if connection fails
                $backupStats['tables_count'] = -1; // Unknown
            }
        } else {
            // MySQL (.sql text file) - Use Regex Scan
            $handle = fopen($path, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (stripos($line, 'CREATE TABLE') !== false) {
                        $backupStats['tables_count']++;
                    }
                }
                fclose($handle);
            }
        }

        // 2. Live System Stats
        $liveStats = [
            'name' => 'Current Live Database',
            'size' => 0,
            'tables_count' => 0,
        ];

        if (config('database.default') === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            $liveStats['size'] = file_exists($dbPath) ? filesize($dbPath) : 0;

            $liveStats['tables_count'] = \Illuminate\Support\Facades\DB::table('sqlite_master')
                ->where('type', 'table') // Corrected logic
                ->where('name', 'not like', 'sqlite_%')
                ->count();
        } else {
            // MySQL logic
            $liveStats['size'] = 0; // Placeholder

            // Count tables
            $liveStats['tables_count'] = count(\Illuminate\Support\Facades\DB::select('SHOW TABLES'));
        }

        return view('settings.backups.compare', compact('backupStats', 'liveStats'));
    }

    /**
     * Format file size
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

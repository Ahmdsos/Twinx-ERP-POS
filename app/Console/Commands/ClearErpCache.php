<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Modules\Reporting\Services\DashboardService;

/**
 * ClearErpCache - Artisan command to clear ERP caches
 */
class ClearErpCache extends Command
{
    protected $signature = 'erp:cache-clear {--dashboard : Clear only dashboard cache} {--all : Clear all ERP caches}';

    protected $description = 'Clear ERP application caches';

    public function handle(): int
    {
        if ($this->option('dashboard')) {
            DashboardService::clearCache();
            $this->info('Dashboard cache cleared.');
            return Command::SUCCESS;
        }

        if ($this->option('all')) {
            CacheService::clearAll();
            $this->info('All ERP caches cleared.');
            return Command::SUCCESS;
        }

        // Default: clear all
        CacheService::clearAll();
        DashboardService::clearCache();
        $this->info('All ERP caches cleared successfully.');

        return Command::SUCCESS;
    }
}

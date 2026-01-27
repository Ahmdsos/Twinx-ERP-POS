<?php

namespace Modules\Reporting\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Reporting\Services\FinancialReportService;
use Modules\Reporting\Services\AgingReportService;
use Modules\Reporting\Services\StockReportService;
use Modules\Reporting\Services\DashboardService;

/**
 * ReportingServiceProvider - Registers Reporting module services and routes
 */
class ReportingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Reporting';
    protected string $moduleNameLower = 'reporting';

    public function register(): void
    {
        // Register services
        $this->app->singleton(FinancialReportService::class);
        $this->app->singleton(AgingReportService::class);
        $this->app->singleton(StockReportService::class);

        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService(
                $app->make(FinancialReportService::class),
                $app->make(AgingReportService::class),
                $app->make(StockReportService::class)
            );
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            $this->moduleNameLower
        );
    }

    public function boot(): void
    {
        $this->registerApiRoutes();
    }

    protected function registerApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/../routes/api.php');
    }

    public function provides(): array
    {
        return [
            FinancialReportService::class,
            AgingReportService::class,
            StockReportService::class,
            DashboardService::class,
        ];
    }
}

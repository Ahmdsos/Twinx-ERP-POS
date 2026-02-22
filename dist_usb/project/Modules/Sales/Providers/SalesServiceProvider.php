<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Sales\Services\SalesService;
use Modules\Inventory\Services\InventoryService;
use Modules\Accounting\Services\JournalService;

/**
 * SalesServiceProvider - Registers Sales module services and routes
 */
class SalesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Sales';
    protected string $moduleNameLower = 'sales';

    public function register(): void
    {
        // Register SalesService with dependencies
        $this->app->singleton(SalesService::class, function ($app) {
            return new SalesService(
                $app->make(InventoryService::class),
                $app->make(JournalService::class)
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
        $this->registerWebRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->moduleNameLower);
    }

    protected function registerWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
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
            SalesService::class,
        ];
    }
}

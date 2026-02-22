<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Services\InventoryService;

/**
 * InventoryServiceProvider - Registers Inventory module services and routes
 */
class InventoryServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Inventory';
    protected string $moduleNameLower = 'inventory';

    public function register(): void
    {
        // Register InventoryService - inject JournalService dependency
        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService(
                $app->make(\Modules\Accounting\Services\JournalService::class)
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
            InventoryService::class,
        ];
    }
}

<?php

namespace Modules\Purchasing\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Purchasing\Services\PurchasingService;

/**
 * PurchasingServiceProvider - Registers Purchasing module services and routes
 */
class PurchasingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Purchasing';
    protected string $moduleNameLower = 'purchasing';

    public function register(): void
    {
        // Register PurchasingService
        $this->app->singleton(PurchasingService::class, function ($app) {
            return new PurchasingService(
                $app->make(\Modules\Inventory\Services\InventoryService::class),
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
            PurchasingService::class,
        ];
    }
}

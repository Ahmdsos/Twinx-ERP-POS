<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Services\LedgerService;

/**
 * AccountingServiceProvider - Registers Accounting module services and routes
 */
class AccountingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Accounting';
    protected string $moduleNameLower = 'accounting';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(JournalService::class, function ($app) {
            return new JournalService();
        });

        $this->app->singleton(LedgerService::class, function ($app) {
            return new LedgerService();
        });

        // Merge module config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            $this->moduleNameLower
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register API routes
        $this->registerApiRoutes();

        // Register Web routes
        $this->registerWebRoutes();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load module views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->moduleNameLower);
    }

    /**
     * Register Web routes
     */
    protected function registerWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * Register API routes
     */
    protected function registerApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            JournalService::class,
            LedgerService::class,
        ];
    }
}

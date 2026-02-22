<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Auth\Services\AuthService;

/**
 * AuthServiceProvider - Registers Auth module services and routes
 */
class AuthServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Auth';
    protected string $moduleNameLower = 'auth';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AuthService as singleton
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
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

        // Load module views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->moduleNameLower);
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
            AuthService::class,
        ];
    }
}

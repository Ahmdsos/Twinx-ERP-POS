<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * CoreServiceProvider - Registers Core module services
 * 
 * This provider handles:
 * - Loading Core module helpers
 * - Registering Core contracts and implementations
 */
class CoreServiceProvider extends ServiceProvider
{
    /**
     * Module name for reference
     */
    protected string $moduleName = 'Core';

    /**
     * Module path
     */
    protected string $moduleNameLower = 'core';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register config
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
        // Load module views if needed
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}

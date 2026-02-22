<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
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
        $this->registerApiRoutes();
        $this->registerWebRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->moduleNameLower);

        // Fix 20 (M-08): Validate critical account codes at boot
        if (!$this->app->runningInConsole()) {
            $this->app->booted(function () {
                try {
                    $requiredAccounts = [
                        'acc_cash',
                        'acc_bank',
                        'acc_ar',
                        'acc_ap',
                        'acc_sales_revenue',
                        'acc_cogs',
                        'acc_inventory',
                        'acc_tax_payable',
                        'acc_salaries_exp',
                        'acc_salaries_payable',
                    ];
                    foreach ($requiredAccounts as $key) {
                        $code = \App\Models\Setting::getValue($key);
                        if ($code && !\Modules\Accounting\Models\Account::where('code', $code)->exists()) {
                            \Illuminate\Support\Facades\Log::warning(
                                "ERP Config: Setting '{$key}' references account code '{$code}' which doesn't exist in Chart of Accounts."
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    // Silently ignore during migrations or when tables don't exist yet
                }
            });
        }
    }

    protected function registerWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }

    protected function registerApiRoutes(): void
    {
        $path = __DIR__ . '/../routes/api.php';
        if (file_exists($path)) {
            Route::prefix('api')
                ->middleware('api')
                ->group($path);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}

<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\ActivityLogController;
use Modules\Core\Http\Controllers\RoleController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\BackupController;
use Modules\Core\Http\Controllers\CurrencyController;
use Modules\Core\Http\Controllers\BulkActionsController;
use Modules\Core\Http\Controllers\NotificationsController;
use Modules\Core\Http\Controllers\ActivationController;
use Modules\Core\Http\Controllers\ExportController;

/*
|--------------------------------------------------------------------------
| Core Web Routes
|--------------------------------------------------------------------------
*/

// Activation Routes
Route::get('activate', [ActivationController::class, 'index'])->name('system.activate');
Route::post('activate', [ActivationController::class, 'activate'])->name('system.activate.submit');

Route::middleware(['auth', 'license'])->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin / Settings
    Route::middleware(['can:settings.manage'])->group(function () {

        // Activity Log
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('activity-log/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-log.show');

        // User Management
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);

        // System Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [SettingsController::class, 'systemReset'])->name('settings.reset');

        // Backup Management
        Route::prefix('settings/backup')->name('settings.backup.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/create', [BackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
            Route::get('/open-folder/{filename}', [BackupController::class, 'openFolder'])->name('open_folder');
            Route::get('/compare/{filename}', [BackupController::class, 'compare'])->name('compare');
            Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/restore/{filename}', [BackupController::class, 'restore'])->name('restore');
            Route::post('/upload', [BackupController::class, 'upload'])->name('upload');
        });
    });

    // Multi-Currency
    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
        Route::post('/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('set-default');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert');
    });

    // Bulk Actions
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::delete('/products', [BulkActionsController::class, 'deleteProducts'])->name('products.delete');
        Route::put('/products', [BulkActionsController::class, 'updateProducts'])->name('products.update');
        Route::put('/products/category', [BulkActionsController::class, 'moveProductsCategory'])->name('products.category');
        Route::get('/products/export', [BulkActionsController::class, 'exportProducts'])->name('products.export');
        Route::delete('/customers', [BulkActionsController::class, 'deleteCustomers'])->name('customers.delete');
        Route::put('/customers', [BulkActionsController::class, 'updateCustomers'])->name('customers.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationsController::class, 'index'])->name('index');
        Route::get('/settings', [NotificationsController::class, 'settings'])->name('settings');
        Route::put('/settings', [NotificationsController::class, 'updateSettings'])->name('settings.update');
        Route::post('/{id}/read', [NotificationsController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationsController::class, 'markAllAsRead'])->name('read-all');
    });

    // EXPORT ROUTES
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/products', [ExportController::class, 'products'])->name('products');
        Route::get('/customers', [ExportController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [ExportController::class, 'suppliers'])->name('suppliers');
        Route::get('/categories', [ExportController::class, 'categories'])->name('categories');
        Route::get('/brands', [ExportController::class, 'brands'])->name('brands');
        Route::get('/units', [ExportController::class, 'units'])->name('units');
        Route::get('/warehouses', [ExportController::class, 'warehouses'])->name('warehouses');
        Route::get('/inventory/template', [ExportController::class, 'inventoryTemplate'])->name('inventory.template');
        Route::get('/inventory/json-sample', [ExportController::class, 'inventoryJsonSample'])->name('inventory.json-sample');
        Route::get('/products/pdf', [ExportController::class, 'productsPdf'])->name('products.pdf');
        Route::get('/customers/pdf', [ExportController::class, 'customersPdf'])->name('customers.pdf');
        Route::get('/suppliers/pdf', [ExportController::class, 'suppliersPdf'])->name('suppliers.pdf');
    });
});

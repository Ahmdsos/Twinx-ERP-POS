<?php

use Illuminate\Support\Facades\Route;
use Modules\Reporting\Http\Controllers\ReportController;

Route::middleware(['auth', 'can:reports.view'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    // Financial
    Route::get('/financial/profit-loss', [ReportController::class, 'financial'])->name('financial.pl');
    Route::get('/financial/balance-sheet', [ReportController::class, 'financial'])->name('financial.bs');
    Route::get('/financial/ledger/{id}', [ReportController::class, 'ledger'])->name('financial.ledger');

    // Inventory
    Route::get('/shifts', [ReportController::class, 'shifts'])->name('shifts');
    Route::get('/inventory/valuation', [ReportController::class, 'inventory'])->name('inventory.valuation');
    Route::get('/inventory/low-stock', [ReportController::class, 'lowStock'])->name('inventory.low-stock');

    // Sales
    Route::get('/sales/by-product', [ReportController::class, 'salesByProduct'])->name('sales.by-product');
    Route::get('/sales/by-customer', [ReportController::class, 'salesByCustomer'])->name('sales.by-customer');

    // Purchases
    Route::get('/purchases/by-supplier', [ReportController::class, 'purchasesBySupplier'])->name('purchases.by-supplier');
});

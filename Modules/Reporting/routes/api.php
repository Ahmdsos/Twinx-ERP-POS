<?php

use Illuminate\Support\Facades\Route;
use Modules\Reporting\Http\Controllers\Api\FinancialReportController;
use Modules\Reporting\Http\Controllers\Api\AgingReportController;
use Modules\Reporting\Http\Controllers\Api\StockReportController;
use Modules\Reporting\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| Reporting Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ========================================
    // Dashboard
    // ========================================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('api.v1.dashboard');

    // ========================================
    // Financial Reports
    // ========================================
    Route::prefix('reports/financial')->name('api.v1.reports.financial.')->group(function () {
        Route::get('trial-balance', [FinancialReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('profit-loss', [FinancialReportController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
    });

    // ========================================
    // Aging Reports
    // ========================================
    Route::prefix('reports/aging')->name('api.v1.reports.aging.')->group(function () {
        Route::get('ar', [AgingReportController::class, 'arAging'])->name('ar');
        Route::get('ap', [AgingReportController::class, 'apAging'])->name('ap');
    });

    // ========================================
    // Stock Reports
    // ========================================
    Route::prefix('reports/stock')->name('api.v1.reports.stock.')->group(function () {
        Route::get('valuation', [StockReportController::class, 'valuation'])->name('valuation');
        Route::get('low-stock', [StockReportController::class, 'lowStock'])->name('low-stock');
        Route::get('movements', [StockReportController::class, 'movements'])->name('movements');
        Route::get('by-warehouse', [StockReportController::class, 'byWarehouse'])->name('by-warehouse');
    });
});

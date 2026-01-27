<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\Api\AccountController;
use Modules\Accounting\Http\Controllers\Api\JournalEntryController;
use Modules\Accounting\Http\Controllers\Api\LedgerController;

/*
|--------------------------------------------------------------------------
| Accounting Module API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1
| All routes require Sanctum authentication
|
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ========================================
    // Chart of Accounts
    // ========================================
    Route::prefix('accounts')->name('api.v1.accounts.')->group(function () {
        Route::get('types', [AccountController::class, 'types'])->name('types');
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::get('{account}', [AccountController::class, 'show'])->name('show');
        Route::put('{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('{account}', [AccountController::class, 'destroy'])->name('destroy');
    });

    // ========================================
    // Journal Entries
    // ========================================
    Route::prefix('journal-entries')->name('api.v1.journal-entries.')->group(function () {
        Route::get('/', [JournalEntryController::class, 'index'])->name('index');
        Route::post('/', [JournalEntryController::class, 'store'])->name('store');
        Route::get('{journalEntry}', [JournalEntryController::class, 'show'])->name('show');
        Route::delete('{journalEntry}', [JournalEntryController::class, 'destroy'])->name('destroy');

        // Actions
        Route::post('{journalEntry}/post', [JournalEntryController::class, 'post'])->name('post');
        Route::post('{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('reverse');
    });

    // ========================================
    // Ledger & Reports
    // ========================================
    Route::prefix('ledger')->name('api.v1.ledger.')->group(function () {
        Route::get('trial-balance', [LedgerController::class, 'trialBalance'])->name('trial-balance');
        Route::get('balances-by-type', [LedgerController::class, 'balancesByType'])->name('balances-by-type');
        Route::get('profit-loss', [LedgerController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('account/{account}', [LedgerController::class, 'accountLedger'])->name('account-ledger');
        Route::get('balance/{account}', [LedgerController::class, 'accountBalance'])->name('account-balance');
    });
});

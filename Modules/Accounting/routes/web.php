<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountController;
use Modules\Accounting\Http\Controllers\JournalEntryController;
use Modules\Accounting\Http\Controllers\ExpenseController;
use Modules\Accounting\Http\Controllers\ExpenseCategoryController;
use Modules\Accounting\Http\Controllers\TreasuryTransactionController;

Route::middleware(['auth', 'can:finance.manage'])->group(function () {
    // Chart of Accounts
    Route::resource('accounts', AccountController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::get('accounts-tree', [AccountController::class, 'tree'])->name('accounts.tree');

    // Journal Entries - Full CRUD + Actions
    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('journal-entries/{journal_entry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');

    // Treasury (Cash/Bank)
    Route::prefix('treasury')->name('treasury.')->group(function () {
        Route::get('/', [TreasuryTransactionController::class, 'index'])->name('index');
        Route::get('/payment/create', [TreasuryTransactionController::class, 'createPayment'])->name('create-payment');
        Route::post('/payment', [TreasuryTransactionController::class, 'storePayment'])->name('store-payment');
        Route::get('/receipt/create', [TreasuryTransactionController::class, 'createReceipt'])->name('create-receipt');
        Route::post('/receipt', [TreasuryTransactionController::class, 'storeReceipt'])->name('store-receipt');
        Route::get('/{transaction}', [TreasuryTransactionController::class, 'show'])->name('show');
    });
});

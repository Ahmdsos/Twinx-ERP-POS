<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchasing\Http\Controllers\Api\SupplierController;
use Modules\Purchasing\Http\Controllers\Api\PurchaseOrderController;
use Modules\Purchasing\Http\Controllers\Api\GrnController;
use Modules\Purchasing\Http\Controllers\Api\PurchaseInvoiceController;
use Modules\Purchasing\Http\Controllers\Api\SupplierPaymentController;

/*
|--------------------------------------------------------------------------
| Purchasing Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ========================================
    // Suppliers
    // ========================================
    Route::prefix('suppliers')->name('api.v1.suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::put('{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::get('{supplier}/balance', [SupplierController::class, 'balance'])->name('balance');
    });

    // ========================================
    // Purchase Orders
    // ========================================
    Route::prefix('purchase-orders')->name('api.v1.purchase-orders.')->group(function () {
        Route::get('statuses', [PurchaseOrderController::class, 'statuses'])->name('statuses');
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::post('{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
    });

    // ========================================
    // Goods Received Notes
    // ========================================
    Route::prefix('grns')->name('api.v1.grns.')->group(function () {
        Route::get('/', [GrnController::class, 'index'])->name('index');
        Route::post('receive', [GrnController::class, 'receive'])->name('receive');
        Route::get('{grn}', [GrnController::class, 'show'])->name('show');
    });

    // ========================================
    // Purchase Invoices
    // ========================================
    Route::prefix('purchase-invoices')->name('api.v1.purchase-invoices.')->group(function () {
        Route::get('pending', [PurchaseInvoiceController::class, 'pending'])->name('pending');
        Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('index');
        Route::post('from-grn', [PurchaseInvoiceController::class, 'createFromGrn'])->name('from-grn');
        Route::get('{purchaseInvoice}', [PurchaseInvoiceController::class, 'show'])->name('show');
    });

    // ========================================
    // Supplier Payments
    // ========================================
    Route::prefix('supplier-payments')->name('api.v1.supplier-payments.')->group(function () {
        Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
        Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
        Route::get('{supplierPayment}', [SupplierPaymentController::class, 'show'])->name('show');
    });
});

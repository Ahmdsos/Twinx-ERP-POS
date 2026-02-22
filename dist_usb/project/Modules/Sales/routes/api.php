<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\Api\CustomerController;
use Modules\Sales\Http\Controllers\Api\SalesOrderController;
use Modules\Sales\Http\Controllers\Api\DeliveryOrderController;
use Modules\Sales\Http\Controllers\Api\SalesInvoiceController;
use Modules\Sales\Http\Controllers\Api\CustomerPaymentController;

/*
|--------------------------------------------------------------------------
| Sales Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ========================================
    // Customers
    // ========================================
    Route::prefix('customers')->name('api.v1.customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('{customer}', [CustomerController::class, 'show'])->name('show');
        Route::put('{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('{customer}/balance', [CustomerController::class, 'balance'])->name('balance');
    });

    // ========================================
    // Sales Orders
    // ========================================
    Route::prefix('sales-orders')->name('api.v1.sales-orders.')->group(function () {
        Route::get('statuses', [SalesOrderController::class, 'statuses'])->name('statuses');
        Route::get('/', [SalesOrderController::class, 'index'])->name('index');
        Route::post('/', [SalesOrderController::class, 'store'])->name('store');
        Route::get('{salesOrder}', [SalesOrderController::class, 'show'])->name('show');
        Route::post('{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('confirm');
        Route::post('{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('cancel');
    });

    // ========================================
    // Delivery Orders
    // ========================================
    Route::prefix('delivery-orders')->name('api.v1.delivery-orders.')->group(function () {
        Route::get('/', [DeliveryOrderController::class, 'index'])->name('index');
        Route::post('deliver', [DeliveryOrderController::class, 'deliver'])->name('deliver');
        Route::get('{deliveryOrder}', [DeliveryOrderController::class, 'show'])->name('show');
        Route::post('{deliveryOrder}/ship', [DeliveryOrderController::class, 'ship'])->name('ship');
    });

    // ========================================
    // Sales Invoices
    // ========================================
    Route::prefix('sales-invoices')->name('api.v1.sales-invoices.')->group(function () {
        Route::get('pending', [SalesInvoiceController::class, 'pending'])->name('pending');
        Route::get('/', [SalesInvoiceController::class, 'index'])->name('index');
        Route::post('from-delivery', [SalesInvoiceController::class, 'createFromDelivery'])->name('from-delivery');
        Route::get('{salesInvoice}', [SalesInvoiceController::class, 'show'])->name('show');
    });

    // ========================================
    // Customer Payments
    // ========================================
    Route::prefix('customer-payments')->name('api.v1.customer-payments.')->group(function () {
        Route::get('/', [CustomerPaymentController::class, 'index'])->name('index');
        Route::post('/', [CustomerPaymentController::class, 'store'])->name('store');
        Route::get('{customerPayment}', [CustomerPaymentController::class, 'show'])->name('show');
    });
});

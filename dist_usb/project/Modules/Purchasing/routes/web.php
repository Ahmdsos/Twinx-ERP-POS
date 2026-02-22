<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchasing\Http\Controllers\SupplierController;
use Modules\Purchasing\Http\Controllers\PurchaseOrderController;
use Modules\Purchasing\Http\Controllers\GrnController;
use Modules\Purchasing\Http\Controllers\PurchaseInvoiceController;
use Modules\Purchasing\Http\Controllers\SupplierPaymentController;
use Modules\Purchasing\Http\Controllers\PurchaseReturnController;

Route::middleware(['auth', 'can:purchases.manage'])->group(function () {

    // Suppliers - Full Resource
    Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers-import', [SupplierController::class, 'importForm'])->name('suppliers.import.form');
    Route::get('suppliers-import/sample', [SupplierController::class, 'importSample'])->name('suppliers.import.sample');
    Route::post('suppliers-import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::get('suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class)->parameters(['purchase-orders' => 'purchaseOrder']);
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::get('purchase-orders/product-info', [PurchaseOrderController::class, 'getProductInfo'])->name('purchase-orders.product-info');

    // Goods Received Notes (GRN)
    Route::resource('grns', GrnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('grns/{grn}/cancel', [GrnController::class, 'cancel'])->name('grns.cancel');

    // Purchase Invoices
    Route::resource('purchase-invoices', PurchaseInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('purchase-invoices/{purchaseInvoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoices.print');
    Route::post('purchase-invoices/{purchaseInvoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('purchase-invoices.cancel');

    // Supplier Payments
    Route::resource('supplier-payments', SupplierPaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('supplier-payments/{supplierPayment}/print', [SupplierPaymentController::class, 'print'])->name('supplier-payments.print');

    // Purchase Returns
    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);

});

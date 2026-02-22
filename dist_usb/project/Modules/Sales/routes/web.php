<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\CustomerController;
use Modules\Sales\Http\Controllers\QuotationController;
use Modules\Sales\Http\Controllers\SalesOrderController;
use Modules\Sales\Http\Controllers\DeliveryOrderController;
use Modules\Sales\Http\Controllers\SalesInvoiceController;
use Modules\Sales\Http\Controllers\CustomerPaymentController;
use Modules\Sales\Http\Controllers\POSController;
use Modules\Sales\Http\Controllers\SalesReturnController;
use Modules\Sales\Http\Controllers\LoyaltyController;
use Modules\Sales\Http\Controllers\CourierController;
use Modules\Sales\Http\Controllers\MissionController;

Route::middleware(['auth', 'can:sales.manage'])->group(function () {

    // Customers - Full Resource + Statement
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('customers/{customer}/credit-history', [CustomerController::class, 'creditHistory'])->name('customers.credit-history');
    Route::get('customers-import', [CustomerController::class, 'importForm'])->name('customers.import.form');
    Route::get('customers-import/sample', [CustomerController::class, 'importSample'])->name('customers.import.sample');
    Route::post('customers-import', [CustomerController::class, 'import'])->name('customers.import');
    Route::post('customers/{customer}/block', [CustomerController::class, 'block'])->name('customers.block');
    Route::post('customers/{customer}/unblock', [CustomerController::class, 'unblock'])->name('customers.unblock');

    // Quotations
    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::post('quotations/{quotation}/expire', [QuotationController::class, 'expire'])->name('quotations.expire');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Sales Orders
    Route::resource('sales-orders', SalesOrderController::class)->parameters(['sales-orders' => 'salesOrder']);
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm');
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::get('sales-orders/product-info', [SalesOrderController::class, 'getProductInfo'])->name('sales-orders.product-info');
    Route::get('sales-orders/{salesOrder}/print', [SalesOrderController::class, 'print'])->name('sales-orders.print');
    Route::post('sales-orders/{salesOrder}/deliver', [SalesOrderController::class, 'deliver'])->name('sales-orders.deliver');
    Route::post('sales-orders/{salesOrder}/invoice', [SalesOrderController::class, 'invoice'])->name('sales-orders.invoice');

    // Delivery Orders
    Route::resource('deliveries', DeliveryOrderController::class)->parameters(['deliveries' => 'delivery']);
    Route::post('deliveries/{delivery}/ship', [DeliveryOrderController::class, 'ship'])->name('deliveries.ship');
    Route::post('deliveries/{delivery}/complete', [DeliveryOrderController::class, 'complete'])->name('deliveries.complete');
    Route::post('deliveries/{delivery}/cancel', [DeliveryOrderController::class, 'cancel'])->name('deliveries.cancel');

    // Sales Invoices
    Route::resource('sales-invoices', SalesInvoiceController::class)
        ->parameters(['sales-invoices' => 'salesInvoice']);
    Route::get('sales-invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])->name('sales-invoices.print');
    Route::post('sales-invoices/{salesInvoice}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales-invoices.cancel');

    // Sales Returns
    Route::resource('sales-returns', SalesReturnController::class);
    Route::post('sales-returns/{salesReturn}/approve', [SalesReturnController::class, 'approve'])->name('sales-returns.approve');
    Route::get('api/customers/{customer}/invoices', [SalesReturnController::class, 'getCustomerInvoices'])->name('api.customers.invoices');
    Route::get('api/invoices/{invoice}/lines', [SalesReturnController::class, 'getInvoiceLines'])->name('api.invoices.lines');

    // Customer Payments
    Route::resource('customer-payments', CustomerPaymentController::class)->parameters(['customer-payments' => 'customerPayment']);
    Route::get('customer-payments/{customerPayment}/print', [CustomerPaymentController::class, 'print'])->name('customer-payments.print');
    Route::get('customer-payments/customer/{customer}/invoices', [CustomerPaymentController::class, 'getCustomerInvoices'])->name('customer-payments.customer-invoices');

    // ==========================================
    // COURIERS (شركات الشحن)
    // ==========================================
    Route::middleware(['can:couriers.manage'])->group(function () {
        Route::resource('couriers', CourierController::class);
        Route::patch('couriers/{courier}/toggle-status', [CourierController::class, 'toggleStatus'])->name('couriers.toggle-status');
    });

    // ==========================================
    // LOYALTY PROGRAM
    // ==========================================
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::get('/settings', [LoyaltyController::class, 'settings'])->name('settings');
        Route::put('/settings', [LoyaltyController::class, 'updateSettings'])->name('settings.update');
        Route::get('/report', [LoyaltyController::class, 'report'])->name('report');
        Route::get('/customer/{customer}', [LoyaltyController::class, 'show'])->name('show');
        Route::post('/add-points', [LoyaltyController::class, 'addPoints'])->name('add-points');
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints'])->name('redeem');
        Route::get('/api/customer/{customer}', [LoyaltyController::class, 'getCustomerLoyalty'])->name('api.customer');
    });

    // ==========================================
    // MISSION CONTROL (Logistics Settlement)
    // ==========================================
    Route::get('mission-control', [MissionController::class, 'index'])->name('mission.control');
    Route::post('mission-control/{delivery}/settle', [MissionController::class, 'settle'])->name('mission.settle');

});

// ==========================================
// POS - Point of Sale (Restricted to Cashiers & above)
// ==========================================
Route::middleware(['auth', 'license', 'can:sales.create'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [POSController::class, 'index'])->name('index');
    Route::get('/search', [POSController::class, 'searchProducts'])->name('search')->middleware('throttle:pos-api');
    Route::get('/products/search', [POSController::class, 'searchProducts'])->name('products.search')->middleware('throttle:pos-api');
    Route::get('/customers/search', [POSController::class, 'searchCustomers'])->name('customers.search')->middleware('throttle:pos-api');
    Route::get('/customers/{id}/brief', [POSController::class, 'getCustomerBrief'])->name('customers.brief')->middleware('throttle:pos-api');
    Route::get('/customers/{customer}/quotation-prices', [Modules\Sales\Http\Controllers\Api\POSPriceController::class, 'getCustomerPrices'])->name('customers.quotation-prices');
    Route::post('/customers/quick-create', [POSController::class, 'quickCreateCustomer'])->name('customers.quick-create');
    Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout')->middleware('throttle:pos-checkout');
    Route::get('/receipt/{invoice}', [POSController::class, 'receipt'])->name('receipt');
    Route::get('/summary', [POSController::class, 'dailySummary'])->name('summary');
    Route::post('/hold', [POSController::class, 'holdSale'])->name('hold');
    Route::get('/held', [POSController::class, 'getHeldSales'])->name('held');
    Route::post('/resume', [POSController::class, 'resumeSale'])->name('resume');

    // Shift Management
    Route::post('/shift/open', [POSController::class, 'openShift'])->name('shift.open');
    Route::post('/shift/close', [POSController::class, 'closeShift'])->name('shift.close');
    Route::get('/shift/status', [POSController::class, 'shiftStatus'])->name('shift.status');
    Route::get('/shift/{shift}/report', [POSController::class, 'shiftReport'])->name('shift.report');
    Route::get('/shift/stats', [POSController::class, 'getShiftStats'])->name('shift.stats');
    Route::get('/recent-transactions', [POSController::class, 'lastTransactions'])->name('recent');
    Route::post('/sales-return', [POSController::class, 'salesReturn'])->name('return');
    Route::get('/invoice/search', [POSController::class, 'searchInvoice'])->name('invoice.search');
    Route::post('/pin/validate', [POSController::class, 'validateRefundPin'])->name('pin.validate');

    // Security & Reporting
    Route::post('/pin/price-override', [POSController::class, 'validatePriceOverridePin'])->name('pin.priceOverride');
    Route::post('/cart/log-deletion', [POSController::class, 'logCartDeletion'])->name('cart.logDeletion');
    Route::get('/x-report', [POSController::class, 'xReport'])->name('xReport');
    Route::get('/last-transactions', [POSController::class, 'lastTransactions'])->name('lastTransactions');
    Route::post('/drawer/open', [POSController::class, 'openCashDrawer'])->name('drawer.open');
    Route::post('/expenses', [POSController::class, 'saveExpense'])->name('expenses.store');

    // Delivery Management
    Route::get('/delivery/list', [POSController::class, 'listDeliveryOrders'])->name('delivery.list');
    Route::post('/delivery/status', [POSController::class, 'updateDeliveryStatus'])->name('delivery.status');
});

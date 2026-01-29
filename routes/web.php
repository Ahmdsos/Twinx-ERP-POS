<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (login, register, logout)
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('login', function () {
    $credentials = request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials)) {
        request()->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
})->name('login.submit');

Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Redirect root to dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // ACCOUNTING MODULE
    // ==========================================

    // Chart of Accounts
    Route::resource('accounts', AccountController::class);
    Route::get('accounts-tree', [AccountController::class, 'tree'])->name('accounts.tree');

    // Journal Entries - Full CRUD + Actions
    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('journal-entries/{journal_entry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');

    // ==========================================
    // SALES MODULE
    // ==========================================

    // Customers - Full Resource + Statement
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');

    // Quotations (عروض الأسعار)
    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Sales Orders - Full Resource + Actions
    Route::resource('sales-orders', SalesOrderController::class)->parameters(['sales-orders' => 'salesOrder']);
    Route::post('sales-orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm');
    Route::post('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::get('sales-orders/product-info', [SalesOrderController::class, 'getProductInfo'])->name('sales-orders.product-info');

    // Delivery Orders
    Route::resource('deliveries', DeliveryOrderController::class)->parameters(['deliveries' => 'delivery']);
    Route::post('deliveries/{delivery}/ship', [DeliveryOrderController::class, 'ship'])->name('deliveries.ship');
    Route::post('deliveries/{delivery}/complete', [DeliveryOrderController::class, 'complete'])->name('deliveries.complete');
    Route::post('deliveries/{delivery}/cancel', [DeliveryOrderController::class, 'cancel'])->name('deliveries.cancel');

    // Sales Invoices
    Route::resource('sales-invoices', SalesInvoiceController::class)->parameters(['sales-invoices' => 'salesInvoice']);
    Route::get('sales-invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])->name('sales-invoices.print');
    Route::post('sales-invoices/{salesInvoice}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales-invoices.cancel');

    // Customer Payments
    Route::resource('customer-payments', CustomerPaymentController::class)->parameters(['customer-payments' => 'customerPayment']);
    Route::get('customer-payments/{customerPayment}/print', [CustomerPaymentController::class, 'print'])->name('customer-payments.print');
    Route::get('customer-payments/customer/{customer}/invoices', [CustomerPaymentController::class, 'getCustomerInvoices'])->name('customer-payments.customer-invoices');

    // ==========================================
    // PURCHASING MODULE
    // ==========================================

    // Suppliers - Full Resource
    Route::resource('suppliers', SupplierController::class);

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

    // ==========================================
    // INVENTORY MODULE
    // ==========================================

    // Products - Full Resource
    Route::resource('products', ProductController::class);

    // Categories - Resource (except show - inline editing)
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);

    // Warehouses - Full Resource
    Route::resource('warehouses', WarehouseController::class)->except(['create']);

    // Units - Resource (except show)
    Route::resource('units', UnitController::class)->except(['show', 'create', 'edit']);

    // Stock Management
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/', [StockController::class, 'store'])->name('store');
        Route::get('/adjust', [StockController::class, 'adjust'])->name('adjust');
        Route::post('/adjust', [StockController::class, 'processAdjust'])->name('adjust.process');
        Route::get('/transfer', [StockController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [StockController::class, 'processTransfer'])->name('transfer.process');
        Route::get('/get-stock', [StockController::class, 'getStock'])->name('get-stock');
    });

    // ==========================================
    // REPORTS
    // ==========================================
    Route::get('reports/financial', fn() => view('reports.financial'))->name('reports.financial');
    Route::get('reports/stock', fn() => view('reports.stock'))->name('reports.stock');

    // Summary Reports (Sprint 10)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('customer-sales', [ReportController::class, 'customerSalesSummary'])->name('customer-sales');
        Route::get('supplier-purchases', [ReportController::class, 'supplierPurchaseSummary'])->name('supplier-purchases');

        // Financial Reports (Sprint 12)
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');

        // Aging Reports
        Route::get('ar-aging', [ReportController::class, 'arAging'])->name('ar-aging');
        Route::get('ap-aging', [ReportController::class, 'apAging'])->name('ap-aging');
    });

    // ==========================================
    // COURIERS (شركات الشحن)
    // ==========================================
    Route::resource('couriers', CourierController::class);
    Route::patch('couriers/{courier}/toggle-status', [CourierController::class, 'toggleStatus'])->name('couriers.toggle-status');

    // ==========================================
    // ADMIN / SETTINGS
    // ==========================================
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('activity-log/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-log.show');
});


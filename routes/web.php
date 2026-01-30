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
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\BulkActionsController;
use App\Http\Controllers\BackupController;

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
    // POS - Point of Sale
    // ==========================================
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/search', [POSController::class, 'searchProducts'])->name('search');
        Route::get('/barcode', [POSController::class, 'findByBarcode'])->name('barcode');
        Route::post('/checkout', [POSController::class, 'checkout'])->name('checkout');
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
        Route::get('/shift/report', [POSController::class, 'shiftReportQuick'])->name('shift.report.quick');
        Route::get('/recent-transactions', [POSController::class, 'recentTransactions'])->name('recent');
        Route::get('/returns', [POSController::class, 'showReturns'])->name('returns');
        Route::post('/sales-return', [POSController::class, 'salesReturn'])->name('return');
        Route::get('/daily-report', [POSController::class, 'showDailyReport'])->name('daily-report');
    });

    // ==========================================
    // Product Images
    // ==========================================
    Route::prefix('products/{product}/images')->name('products.images.')->group(function () {
        Route::post('/', [ProductImageController::class, 'store'])->name('store');
        Route::post('/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('primary');
        Route::post('/order', [ProductImageController::class, 'updateOrder'])->name('order');
        Route::delete('/{image}', [ProductImageController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // Barcode Generation
    // ==========================================
    Route::prefix('barcode')->name('barcode.')->group(function () {
        Route::get('/product/{product}', [BarcodeController::class, 'show'])->name('show');
        Route::get('/product/{product}/label', [BarcodeController::class, 'label'])->name('label');
        Route::get('/product/{product}/print', [BarcodeController::class, 'printPreview'])->name('print');
        Route::post('/product/{product}/generate', [BarcodeController::class, 'generate'])->name('generate');
        Route::post('/batch', [BarcodeController::class, 'batch'])->name('batch');
    });

    // ==========================================
    // EXPORT ROUTES (Excel, PDF)
    // ==========================================
    Route::prefix('export')->name('export.')->group(function () {
        // Products
        Route::get('/products/excel', [ExportController::class, 'productsExcel'])->name('products.excel');
        Route::get('/products/pdf', [ExportController::class, 'productsPdf'])->name('products.pdf');

        // Customers
        Route::get('/customers/excel', [ExportController::class, 'customersExcel'])->name('customers.excel');
        Route::get('/customers/pdf', [ExportController::class, 'customersPdf'])->name('customers.pdf');

        // Suppliers
        Route::get('/suppliers/excel', [ExportController::class, 'suppliersExcel'])->name('suppliers.excel');
        Route::get('/suppliers/pdf', [ExportController::class, 'suppliersPdf'])->name('suppliers.pdf');
    });

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
    Route::get('customers/{customer}/credit-history', [CustomerController::class, 'creditHistory'])->name('customers.credit-history');
    Route::get('customers-import', [CustomerController::class, 'importForm'])->name('customers.import.form');
    Route::get('customers-import/sample', [CustomerController::class, 'importSample'])->name('customers.import.sample');
    Route::post('customers-import', [CustomerController::class, 'import'])->name('customers.import');
    Route::post('customers/{customer}/block', [CustomerController::class, 'block'])->name('customers.block');
    Route::post('customers/{customer}/unblock', [CustomerController::class, 'unblock'])->name('customers.unblock');

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

    // ==========================================
    // INVENTORY MODULE
    // ==========================================

    // Products - Full Resource
    Route::resource('products', ProductController::class);
    Route::get('products-import', [ProductController::class, 'importForm'])->name('products.import.form');
    Route::get('products-import/sample', [ProductController::class, 'importSample'])->name('products.import.sample');
    Route::post('products-import', [ProductController::class, 'import'])->name('products.import');

    // Categories - Resource (except show - inline editing)
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);
    Route::get('categories-import', [CategoryController::class, 'importForm'])->name('categories.import.form');
    Route::get('categories-import/sample', [CategoryController::class, 'importSample'])->name('categories.import.sample');
    Route::post('categories-import', [CategoryController::class, 'import'])->name('categories.import');

    // Warehouses - Full Resource
    Route::resource('warehouses', WarehouseController::class)->except(['create']);
    Route::get('warehouses-import', [WarehouseController::class, 'importForm'])->name('warehouses.import.form');
    Route::get('warehouses-import/sample', [WarehouseController::class, 'importSample'])->name('warehouses.import.sample');
    Route::post('warehouses-import', [WarehouseController::class, 'import'])->name('warehouses.import');

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
        Route::get('sales-by-product', [ReportController::class, 'salesByProduct'])->name('sales-by-product');
        Route::get('inventory-valuation', [ReportController::class, 'inventoryValuation'])->name('inventory-valuation');
        Route::get('profit-margin', [ReportController::class, 'profitMarginAnalysis'])->name('profit-margin');

        // Export routes
        Route::get('sales-by-product/export', [ReportController::class, 'exportSalesByProduct'])->name('sales-by-product.export');
        Route::get('inventory-valuation/export', [ReportController::class, 'exportInventoryValuation'])->name('inventory-valuation.export');

        // Phase 5 Reports Enhancement
        Route::get('cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('daily-sales', [ReportController::class, 'dailySales'])->name('daily-sales');
        Route::get('sales-by-cashier', [ReportController::class, 'salesByCashier'])->name('sales-by-cashier');
        Route::get('best-selling', [ReportController::class, 'bestSellingProducts'])->name('best-selling');
        Route::get('current-stock', [ReportController::class, 'currentStock'])->name('current-stock');
        Route::get('stock-movements', [ReportController::class, 'stockMovements'])->name('stock-movements');
        Route::get('low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
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

    // ==========================================
    // SYSTEM SETTINGS
    // ==========================================
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Backup Management
    Route::prefix('settings/backup')->name('settings.backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // PHASE 6: MULTI-CURRENCY
    // ==========================================
    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
        Route::post('/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('set-default');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert');
    });

    // ==========================================
    // PHASE 6: LOYALTY PROGRAM
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
    // PHASE 6: BULK ACTIONS
    // ==========================================
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::delete('/products', [BulkActionsController::class, 'deleteProducts'])->name('products.delete');
        Route::put('/products', [BulkActionsController::class, 'updateProducts'])->name('products.update');
        Route::put('/products/category', [BulkActionsController::class, 'moveProductsCategory'])->name('products.category');
        Route::get('/products/export', [BulkActionsController::class, 'exportProducts'])->name('products.export');
        Route::delete('/customers', [BulkActionsController::class, 'deleteCustomers'])->name('customers.delete');
        Route::put('/customers', [BulkActionsController::class, 'updateCustomers'])->name('customers.update');
    });

    // ==========================================
    // NOTIFICATIONS
    // ==========================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationsController::class, 'index'])->name('index');
        Route::get('/settings', [\App\Http\Controllers\NotificationsController::class, 'settings'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\NotificationsController::class, 'updateSettings'])->name('settings.update');
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationsController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationsController::class, 'markAllAsRead'])->name('read-all');
    });

    // ==========================================
    // CSV IMPORT
    // ==========================================
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ImportController::class, 'index'])->name('index');
        Route::post('/products', [\App\Http\Controllers\ImportController::class, 'importProducts'])->name('products');
        Route::post('/customers', [\App\Http\Controllers\ImportController::class, 'importCustomers'])->name('customers');
        Route::post('/suppliers', [\App\Http\Controllers\ImportController::class, 'importSuppliers'])->name('suppliers');
        Route::post('/categories', [\App\Http\Controllers\ImportController::class, 'importCategories'])->name('categories');
        Route::post('/warehouses', [\App\Http\Controllers\ImportController::class, 'importWarehouses'])->name('warehouses');
        Route::post('/units', [\App\Http\Controllers\ImportController::class, 'importUnits'])->name('units');
        Route::get('/template/{type}', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('template');
    });
});





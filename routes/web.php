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

    // Journal Entries (placeholder routes - to be implemented in Sprint 11)
    Route::get('journals', fn() => view('accounting.journals.index'))->name('journals.index');
    Route::get('journals/create', fn() => view('accounting.journals.create'))->name('journals.create');

    // ==========================================
    // SALES MODULE
    // ==========================================

    // Customers - Full Resource + Statement
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');

    // Sales Orders (placeholder routes - to be implemented in Sprint 8)
    Route::get('sales-orders', fn() => view('sales.orders.index'))->name('sales-orders.index');
    Route::get('sales-orders/create', fn() => view('sales.orders.create'))->name('sales-orders.create');

    // Sales Invoices
    Route::get('sales-invoices', fn() => view('sales.invoices.index'))->name('sales-invoices.index');

    // ==========================================
    // PURCHASING MODULE
    // ==========================================

    // Suppliers - Full Resource
    Route::resource('suppliers', SupplierController::class);

    // Purchase Orders (placeholder routes - to be implemented in Sprint 9)
    Route::get('purchase-orders', fn() => view('purchasing.orders.index'))->name('purchase-orders.index');
    Route::get('purchase-orders/create', fn() => view('purchasing.orders.create'))->name('purchase-orders.create');

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
});

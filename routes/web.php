<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;

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

    // Chart of Accounts
    Route::resource('accounts', AccountController::class);

    // Journal Entries (placeholder routes - to be implemented)
    Route::get('journals', fn() => view('accounting.journals.index'))->name('journals.index');
    Route::get('journals/create', fn() => view('accounting.journals.create'))->name('journals.create');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Suppliers (placeholder routes)
    Route::get('suppliers', fn() => view('purchasing.suppliers.index'))->name('suppliers.index');
    Route::get('suppliers/create', fn() => view('purchasing.suppliers.create'))->name('suppliers.create');

    // Products
    Route::resource('products', ProductController::class);

    // Categories & Warehouses (placeholder routes)
    Route::get('categories', fn() => view('inventory.categories.index'))->name('categories.index');
    Route::get('warehouses', fn() => view('inventory.warehouses.index'))->name('warehouses.index');

    // Sales Orders (placeholder routes)
    Route::get('sales-orders', fn() => view('sales.orders.index'))->name('sales-orders.index');
    Route::get('sales-orders/create', fn() => view('sales.orders.create'))->name('sales-orders.create');
    Route::get('sales-invoices', fn() => view('sales.invoices.index'))->name('sales-invoices.index');

    // Purchase Orders (placeholder routes)
    Route::get('purchase-orders', fn() => view('purchasing.orders.index'))->name('purchase-orders.index');
    Route::get('purchase-orders/create', fn() => view('purchasing.orders.create'))->name('purchase-orders.create');

    // Reports
    Route::get('reports/financial', fn() => view('reports.financial'))->name('reports.financial');
    Route::get('reports/stock', fn() => view('reports.stock'))->name('reports.stock');
});

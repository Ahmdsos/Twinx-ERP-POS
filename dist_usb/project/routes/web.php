<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This file handles the main application entry points.
| All module-specific routes have been moved to their respective
| module route files (Modules/X/routes/web.php).
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


    if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        request()->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
})->name('login.submit');

Route::post('logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Initial Redirect
Route::get('/', fn() => redirect()->route('dashboard'));

// ==========================================
// Theme Toggle (stores preference in session)
// ==========================================
Route::post('/theme/toggle', function () {
    $theme = request()->input('theme', 'dark');
    if (!in_array($theme, ['dark', 'light'])) {
        $theme = 'dark';
    }
    session(['theme' => $theme]);
    return response()->json(['theme' => $theme]);
})->name('theme.toggle');

// ==========================================
// Mobile Barcode Scanner
// ==========================================
Route::get('/scanner', function () {
    return view('scanner.mobile');
})->name('scanner');

Route::get('/api/scanner/lookup', function (\Illuminate\Http\Request $request) {
    $code = trim($request->input('code', ''));
    if (empty($code)) {
        return response()->json(['found' => false, 'error' => 'No code provided']);
    }

    $product = \Modules\Inventory\Models\Product::with(['category', 'unit', 'brand'])
        ->where('barcode', $code)
        ->orWhere('sku', $code)
        ->first();

    if (!$product) {
        return response()->json(['found' => false]);
    }

    return response()->json([
        'found' => true,
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'selling_price' => $product->selling_price,
            'cost_price' => $product->cost_price,
            'tax_rate' => $product->tax_rate,
            'stock' => $product->total_stock,
            'is_low_stock' => $product->isLowStock(),
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'unit' => $product->unit?->name,
            'price_distributor' => $product->price_distributor,
            'price_wholesale' => $product->price_wholesale,
            'price_half_wholesale' => $product->price_half_wholesale,
            'price_quarter_wholesale' => $product->price_quarter_wholesale,
            'price_special' => $product->price_special,
            'is_active' => $product->is_active,
            'image_url' => $product->primary_image_url,
        ],
    ]);
})->name('scanner.lookup');


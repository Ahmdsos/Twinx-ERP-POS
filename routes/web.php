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

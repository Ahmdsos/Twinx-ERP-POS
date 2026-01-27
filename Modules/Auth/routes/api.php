<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1/auth
| Protected routes require Sanctum authentication
|
*/

Route::prefix('v1/auth')->name('api.v1.auth.')->group(function () {

    // Public routes (no authentication required)
    Route::post('login', [AuthController::class, 'login'])
        ->name('login');

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('user', [AuthController::class, 'user'])
            ->name('user');

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::post('logout-all', [AuthController::class, 'logoutAll'])
            ->name('logout-all');

        Route::post('refresh', [AuthController::class, 'refresh'])
            ->name('refresh');
    });
});

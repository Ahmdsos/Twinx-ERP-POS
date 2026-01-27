<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\Api\ProductController;
use Modules\Inventory\Http\Controllers\Api\WarehouseController;
use Modules\Inventory\Http\Controllers\Api\CategoryController;
use Modules\Inventory\Http\Controllers\Api\StockController;

/*
|--------------------------------------------------------------------------
| Inventory Module API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // ========================================
    // Categories
    // ========================================
    Route::prefix('categories')->name('api.v1.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('{category}', [CategoryController::class, 'show'])->name('show');
        Route::put('{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // ========================================
    // Products
    // ========================================
    Route::prefix('products')->name('api.v1.products.')->group(function () {
        Route::get('types', [ProductController::class, 'types'])->name('types');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('{product}', [ProductController::class, 'show'])->name('show');
        Route::put('{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('{product}/stock', [ProductController::class, 'stock'])->name('stock');
    });

    // ========================================
    // Warehouses
    // ========================================
    Route::prefix('warehouses')->name('api.v1.warehouses.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Route::put('{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
        Route::get('{warehouse}/stock', [WarehouseController::class, 'stock'])->name('stock');
    });

    // ========================================
    // Stock Operations
    // ========================================
    Route::prefix('stock')->name('api.v1.stock.')->group(function () {
        Route::get('movements', [StockController::class, 'movements'])->name('movements');
        Route::get('movement-types', [StockController::class, 'movementTypes'])->name('movement-types');
        Route::post('receive', [StockController::class, 'receive'])->name('receive');
        Route::post('issue', [StockController::class, 'issue'])->name('issue');
        Route::post('transfer', [StockController::class, 'transfer'])->name('transfer');
        Route::post('adjust', [StockController::class, 'adjust'])->name('adjust');
    });
});

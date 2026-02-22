<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\CategoryController;
use Modules\Inventory\Http\Controllers\WarehouseController;
use Modules\Inventory\Http\Controllers\UnitController;
use Modules\Inventory\Http\Controllers\BrandController;
use Modules\Inventory\Http\Controllers\StockController;
use Modules\Inventory\Http\Controllers\ProductImageController;
use Modules\Inventory\Http\Controllers\BarcodeController;

Route::middleware(['auth', 'can:inventory.manage'])->group(function () {

    // Products (Custom routes MUST be before resource)
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products-import', [ProductController::class, 'importForm'])->name('products.import.form');
    Route::get('products-import/sample', [ProductController::class, 'importSample'])->name('products.import.sample');
    Route::post('products-import', [ProductController::class, 'import'])->name('products.import');

    // Products - Full Resource
    Route::resource('products', ProductController::class);

    // Categories - Full Resource
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::resource('categories', CategoryController::class);
    Route::get('categories-import', [CategoryController::class, 'importForm'])->name('categories.import.form');
    Route::get('categories-import/sample', [CategoryController::class, 'importSample'])->name('categories.import.sample');
    Route::post('categories-import', [CategoryController::class, 'import'])->name('categories.import');

    // Warehouses - Full Resource
    Route::get('warehouses/export', [WarehouseController::class, 'export'])->name('warehouses.export');
    Route::resource('warehouses', WarehouseController::class);
    Route::get('warehouses-import', [WarehouseController::class, 'importForm'])->name('warehouses.import.form');
    Route::get('warehouses-import/sample', [WarehouseController::class, 'importSample'])->name('warehouses.import.sample');
    Route::post('warehouses-import', [WarehouseController::class, 'import'])->name('warehouses.import');

    // Units - Resource (except show)
    Route::resource('units', UnitController::class)->except(['show', 'create', 'edit']);

    // Brands - Full Resource
    Route::get('brands/export', [BrandController::class, 'export'])->name('brands.export');
    Route::get('brands-import', [BrandController::class, 'importForm'])->name('brands.import.form');
    Route::get('brands-import/sample', [BrandController::class, 'importSample'])->name('brands.import.sample');
    Route::post('brands-import', [BrandController::class, 'import'])->name('brands.import');
    Route::resource('brands', BrandController::class);

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

});

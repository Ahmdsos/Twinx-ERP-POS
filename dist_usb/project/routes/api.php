<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Purchasing\Models\Supplier;

/*
|--------------------------------------------------------------------------
| API Routes - Twinx ERP
|--------------------------------------------------------------------------
| Rate limited to 60 requests per minute by default.
| Use Sanctum for token authentication.
*/

// Public health check
Route::get('/health', fn() => response()->json([
    'status' => 'ok',
    'version' => '1.0.0',
    'timestamp' => now()->toISOString(),
]));

// API Version 1 - Requires authentication via Sanctum token
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ==========================================
    // REPORTS & STATS API
    // ==========================================
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard-stats', function () {
            return response()->json([
                'total_customers' => Customer::count(),
                'total_products' => Product::count(),
                'total_categories' => Category::count(),
                'pending_invoices' => SalesInvoice::whereIn('status', ['pending', 'draft'])->count(),
            ]);
        });
    });
});

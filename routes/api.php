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
    // PRODUCTS API
    // ==========================================
    Route::prefix('products')->group(function () {
        Route::get('/', function (Request $request) {
            $products = Product::when($request->category_id, fn($q, $cat) => $q->where('category_id', $cat))
                ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->where('is_active', true)
                ->orderBy('name')
                ->paginate($request->per_page ?? 20);

            return response()->json($products);
        });

        Route::get('/{product}', function (Product $product) {
            return response()->json($product->load(['category', 'unit']));
        });

        Route::get('/sku/{sku}', function (string $sku) {
            $product = Product::where('sku', $sku)->firstOrFail();
            return response()->json($product->load(['category', 'unit']));
        });
    });

    // ==========================================
    // CATEGORIES API
    // ==========================================
    Route::get('/categories', function () {
        return response()->json(
            Category::orderBy('name')->get(['id', 'code', 'name'])
        );
    });

    // ==========================================
    // CUSTOMERS API
    // ==========================================
    Route::prefix('customers')->group(function () {
        Route::get('/', function (Request $request) {
            $customers = Customer::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->where('is_active', true)
                ->orderBy('name')
                ->paginate($request->per_page ?? 20);

            return response()->json($customers);
        });

        Route::get('/{customer}', function (Customer $customer) {
            return response()->json($customer);
        });

        Route::get('/{customer}/balance', function (Customer $customer) {
            return response()->json([
                'customer_id' => $customer->id,
                'balance' => $customer->getCurrentBalance(),
                'credit_limit' => $customer->credit_limit,
                'available_credit' => $customer->credit_limit
                    ? $customer->credit_limit - $customer->getCurrentBalance()
                    : null,
            ]);
        });
    });

    // ==========================================
    // SUPPLIERS API
    // ==========================================
    Route::prefix('suppliers')->group(function () {
        Route::get('/', function (Request $request) {
            $suppliers = Supplier::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->where('is_active', true)
                ->orderBy('name')
                ->paginate($request->per_page ?? 20);

            return response()->json($suppliers);
        });

        Route::get('/{supplier}', function (Supplier $supplier) {
            return response()->json($supplier);
        });
    });

    // ==========================================
    // INVOICES API
    // ==========================================
    Route::prefix('invoices')->group(function () {
        Route::get('/sales', function (Request $request) {
            $invoices = SalesInvoice::with('customer:id,name,code')
                ->when($request->customer_id, fn($q, $c) => $q->where('customer_id', $c))
                ->when($request->status, fn($q, $s) => $q->where('status', $s))
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);

            return response()->json($invoices);
        });

        Route::get('/sales/{invoice}', function (SalesInvoice $invoice) {
            return response()->json($invoice->load(['customer', 'items.product']));
        });
    });

    // ==========================================
    // REPORTS API
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

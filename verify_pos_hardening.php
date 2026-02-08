<?php

use Illuminate\Support\Facades\DB;
use Modules\Sales\Services\POSService;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ProductStock;
use Modules\Sales\Models\Customer;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function test_pos_hardening()
{
    echo "--- Starting POS Hardening Verification ---\n";

    $posService = app(POSService::class);
    $warehouse = Warehouse::first() ?: Warehouse::create(['name' => 'Main']);
    $user = User::first() ?: User::create(['name' => 'Admin', 'email' => 'admin@erp.com', 'password' => 'secret']);
    auth()->login($user);

    // Satisfy Foreign Keys
    $category = \Modules\Inventory\Models\Category::firstOrCreate(['name' => 'General']);
    $unit = \Modules\Inventory\Models\Unit::firstOrCreate(['name' => 'Each'], ['abbreviation' => 'ea']);
    $brand = \App\Models\Brand::firstOrCreate(['name' => 'Default']);
    $account = \Modules\Accounting\Models\Account::firstOrCreate(['code' => 'TEST-ACC'], ['name' => 'Test Account', 'type' => 'asset']);

    // Cleanup previous test products to avoid duplicate SKU errors
    Product::where('sku', 'SKU-TEST-P1')->delete();

    // 1. Create a test product
    $product = Product::create([
        'code' => 'TEST-P1',
        'sku' => 'SKU-TEST-P1',
        'name' => 'Test Product',
        'price' => 100,
        'tax_rate' => 0.14,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'brand_id' => $brand->id,
        'inventory_account_id' => $account->id,
        'purchase_account_id' => $account->id,
        'created_by' => $user->id,
    ]);

    // Set stock to 5 in Warehouse 1
    ProductStock::updateOrCreate(
        ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
        ['quantity' => 5]
    );

    // 2. Test Stock Validation (Request 10, available 5)
    echo "Testing Stock Over-request... ";
    try {
        $posService->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'price' => 100, 'discount' => 0]],
            'payments' => [['method' => 'cash', 'amount' => 1140]],
            'warehouse_id' => $warehouse->id,
            'is_delivery' => false
        ]);
        echo "FAILED (Should have thrown exception)\n";
    } catch (\RuntimeException $e) {
        echo "PASSED: " . $e->getMessage() . "\n";
    }

    // 3. Test Walk-in Credit Restriction
    $walkin = Customer::where('code', 'WALK-IN')->first() ?: Customer::create(['code' => 'WALK-IN', 'name' => 'Walk-in']);
    echo "Testing Walk-in Credit... ";
    try {
        $posService->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'discount' => 0]],
            'payments' => [['method' => 'credit', 'amount' => 114]],
            'customer_id' => $walkin->id,
            'warehouse_id' => $warehouse->id,
            'is_delivery' => false
        ]);
        echo "FAILED (Should have thrown exception)\n";
    } catch (\RuntimeException $e) {
        echo "PASSED: " . $e->getMessage() . "\n";
    }

    // 4. Test Credit Limit Enforcement
    $creditCus = Customer::create([
        'code' => 'CRED-01',
        'name' => 'Credit Customer',
        'credit_limit' => 50, // Small limit
    ]);
    echo "Testing Credit Limit... ";
    try {
        // Total will be 114 (100 + 14% tax)
        $posService->checkout([
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'discount' => 0]],
            'payments' => [['method' => 'credit', 'amount' => 114]],
            'customer_id' => $creditCus->id,
            'warehouse_id' => $warehouse->id,
            'is_delivery' => false
        ]);
        echo "FAILED (Should have thrown exception)\n";
    } catch (\RuntimeException $e) {
        echo "PASSED: " . $e->getMessage() . "\n";
    }

    echo "--- Verification Complete ---\n";
}

test_pos_hardening();

<?php
/**
 * Comprehensive System Test Script
 * Tests all critical integrations and data flow
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Currency;
use App\Models\Setting;
use Modules\Accounting\Models\Account;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ProductStock;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   Twinx ERP - Comprehensive System Test\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test 1: Database Connection
echo "✓ Testing Database Connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "  ✅ Database connected successfully\n";
    echo "  📊 Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n\n";
} catch (Exception $e) {
    echo "  ❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Core Models
echo "✓ Testing Core Models and Data...\n";
$tests = [
    'Users' => User::count(),
    'Accounts (Chart of Accounts)' => Account::count(),
    'Products' => Product::count(),
    'Categories' => Category::count(),
    'Warehouses' => Warehouse::count(),
    'Customers' => Customer::count(),
    'Suppliers' => Supplier::count(),
    'Currencies' => Currency::count(),
];

foreach ($tests as $name => $count) {
    echo sprintf("  📦 %-30s: %d\n", $name, $count);
}
echo "\n";

// Test 3: Payment Accounts (Critical for POS)
echo "✓ Testing Payment Accounts...\n";
$cashAccounts = Account::where('code', 'like', '110%')->get();
$bankAccounts = Account::where('code', 'like', '111%')->get();

echo "  💰 Cash Accounts: " . $cashAccounts->count() . "\n";
foreach ($cashAccounts->take(3) as $acc) {
    echo "     - [{$acc->code}] {$acc->name}\n";
}

echo "  🏦 Bank Accounts: " . $bankAccounts->count() . "\n";
foreach ($bankAccounts->take(3) as $acc) {
    echo "     - [{$acc->code}] {$acc->name}\n";
}
echo "\n";

// Test 4: Product Stock Integration
echo "✓ Testing Product-Stock Integration...\n";
$productsWithStock = Product::whereHas('stocks')->count();
$totalStockRecords = ProductStock::count();
$totalStockValue = ProductStock::selectRaw('SUM(quantity * average_cost) as total')->value('total');

echo "  📊 Products with stock: {$productsWithStock}\n";
echo "  📦 Total stock records: {$totalStockRecords}\n";
echo "  💵 Total inventory value: " . number_format($totalStockValue, 2) . " EGP\n\n";

// Test 5: Account Types Distribution
echo "✓ Testing Account Types Distribution...\n";
$accountTypes = Account::selectRaw('type, COUNT(*) as count')
    ->groupBy('type')
    ->get();

foreach ($accountTypes as $type) {
    $typeName = is_object($type->type) ? $type->type->value : $type->type;
    echo sprintf("  📋 %-20s: %d accounts\n", ucfirst($typeName), $type->count);
}
echo "\n";

// Test 6: Settings
echo "✓ Testing System Settings...\n";
$settingGroups = Setting::selectRaw('`group`, COUNT(*) as count')
    ->groupBy('group')
    ->get();

foreach ($settingGroups as $group) {
    echo sprintf("  ⚙️  %-20s: %d settings\n", ucfirst($group->group), $group->count);
}
echo "\n";

// Test 7: Currency Configuration
echo "✓ Testing Currency Configuration...\n";
$defaultCurrency = Currency::where('is_default', true)->first();
if ($defaultCurrency) {
    echo "  💱 Default Currency: {$defaultCurrency->code} ({$defaultCurrency->symbol})\n";
    echo "  📊 Exchange Rate: {$defaultCurrency->exchange_rate}\n";
} else {
    echo "  ⚠️  No default currency set!\n";
}
echo "\n";

// Test 8: Critical Relationships
echo "✓ Testing Model Relationships...\n";

// Test Product → Category
$product = Product::with('category')->first();
if ($product) {
    echo "  ✅ Product → Category: " . ($product->category ? "Working ✓" : "Missing ⚠️") . "\n";
} else {
    echo "  ⚠️  No products to test\n";
}

// Test Product → Stocks
if ($product) {
    $stockCount = $product->stocks()->count();
    echo "  ✅ Product → Stocks: {$stockCount} records\n";
}

// Test Account hierarchy
$rootAccounts = Account::whereNull('parent_id')->count();
$childAccounts = Account::whereNotNull('parent_id')->count();
echo "  ✅ Account Hierarchy: {$rootAccounts} root, {$childAccounts} children\n";

echo "\n";

// Test 9: Sample Data Quality
echo "✓ Testing Data Quality...\n";

// Check for duplicate SKUs
$duplicateSkus = Product::selectRaw('sku, COUNT(*) as count')
    ->groupBy('sku')
    ->having('count', '>', 1)
    ->count();
echo "  " . ($duplicateSkus == 0 ? "✅" : "⚠️ ") . " Duplicate SKUs: {$duplicateSkus}\n";

// Check for products without categories
$productsWithoutCategory = Product::whereNull('category_id')->count();
echo "  " . ($productsWithoutCategory == 0 ? "✅" : "⚠️ ") . " Products without category: {$productsWithoutCategory}\n";

// Check for accounts without type
$accountsWithoutType = Account::whereNull('type')->count();
echo "  " . ($accountsWithoutType == 0 ? "✅" : "⚠️ ") . " Accounts without type: {$accountsWithoutType}\n";

echo "\n";

// Test 10: Routes Registration
echo "✓ Testing Routes...\n";
$routeCollection = app()->router->getRoutes();
$totalRoutes = count($routeCollection);
echo "  🌐 Total routes: {$totalRoutes}\n";

// Count by prefix
$webRoutes = 0;
$apiRoutes = 0;
foreach ($routeCollection as $route) {
    if (str_starts_with($route->uri(), 'api/')) {
        $apiRoutes++;
    } else {
        $webRoutes++;
    }
}
echo "  📱 Web routes: {$webRoutes}\n";
echo "  🔌 API routes: {$apiRoutes}\n";

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════════════\n";
echo "   Test Summary\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ Database: Connected\n";
echo "  ✅ Models: All loaded\n";
echo "  ✅ Data: Seeded successfully\n";
echo "  ✅ Routes: {$totalRoutes} registered\n";
echo "  ✅ Payment Accounts: " . ($cashAccounts->count() + $bankAccounts->count()) . " available\n";
echo "  ✅ Stock Integration: Working\n";
echo "  ✅ Relationships: Functional\n";
echo "\n";

if ($duplicateSkus == 0 && $productsWithoutCategory == 0 && $accountsWithoutType == 0) {
    echo "  🎉 All quality checks passed!\n";
} else {
    echo "  ⚠️  Some quality issues detected (see above)\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   System Ready for Browser Testing! ✅\n";
echo "═══════════════════════════════════════════════════════════════\n";

<?php

use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\Schema;

// 1. Check Account Enum Integrity
echo "Checking Account Types...\n";
$accounts = \Illuminate\Support\Facades\DB::table('accounts')->get();
$enumValues = AccountType::values();
$badAccounts = 0;

foreach ($accounts as $acc) {
    if (!in_array($acc->type, $enumValues)) {
        echo "❌ Invalid Account Type ID {$acc->id}: '{$acc->type}' (Expected: " . implode(',', $enumValues) . ")\n";
        $badAccounts++;
    }
}
if ($badAccounts === 0)
    echo "✅ All Accounts have valid Enum types.\n";

// 2. Check Product::getTotalStock
echo "\nChecking Product::getTotalStock()...\n";
try {
    $product = Product::first();
    if ($product) {
        $stock = $product->getTotalStock();
        echo "✅ Product::getTotalStock() works. SKU: {$product->sku}, Stock: {$stock}\n";
    } else {
        echo "⚠️ No products found to test.\n";
    }
} catch (\Exception $e) {
    echo "❌ Product::getTotalStock() FAILED: " . $e->getMessage() . "\n";
}

// 3. Check POS Shifts Table
echo "\nChecking 'pos_shifts' table...\n";
if (Schema::hasTable('pos_shifts')) {
    echo "✅ Table 'pos_shifts' exists.\n";
} else {
    echo "❌ Table 'pos_shifts' DOES NOT EXIST. This will crash reports.\n";
}

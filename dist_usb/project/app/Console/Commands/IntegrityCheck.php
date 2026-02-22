<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;
use Modules\Inventory\Models\Product;

class IntegrityCheck extends Command
{
    protected $signature = 'audit:integrity';
    protected $description = 'Check system data integrity and missing methods';

    public function handle()
    {
        $this->info('🔍 Starting System Integrity Check...');

        // 1. Check Account Enum Integrity
        $this->warn('Checking Account Types...');
        $accounts = DB::table('accounts')->get();
        $enumValues = array_column(AccountType::cases(), 'value');
        $badAccounts = 0;

        foreach ($accounts as $acc) {
            if (!in_array($acc->type, $enumValues)) {
                $this->error("❌ Invalid Account Type ID {$acc->id}: '{$acc->type}' (Expected: " . implode(',', $enumValues) . ")");
                $badAccounts++;
            }
        }
        if ($badAccounts === 0)
            $this->info("✅ All Accounts have valid Enum types.");

        // 2. Check Product::getTotalStock
        $this->warn('Checking Product::getTotalStock()...');
        try {
            $product = Product::first();
            if ($product) {
                // Ensure helper method exists and returns a number
                if (!method_exists($product, 'getTotalStock')) {
                    $this->error("❌ Product::getTotalStock() method MISSING.");
                } else {
                    $stock = $product->getTotalStock();
                    $this->info("✅ Product::getTotalStock() works. SKU: {$product->sku}, Stock: {$stock}");
                }
            } else {
                $this->warn("⚠️ No products found to test.");
            }
        } catch (\Exception $e) {
            $this->error("❌ Product::getTotalStock() FAILED: " . $e->getMessage());
        }

        // 3. Check POS Shifts Table
        $this->warn("Checking 'pos_shifts' table...");
        if (Schema::hasTable('pos_shifts')) {
            $this->info("✅ Table 'pos_shifts' exists.");
        } else {
            $this->error("❌ Table 'pos_shifts' DOES NOT EXIST. This will crash reports.");
        }

        $this->info('🏁 Check Complete.');
    }
}

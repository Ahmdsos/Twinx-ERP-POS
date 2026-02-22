<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-reset {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all transactional data while preserving Chart of Accounts and basic configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('WARNING: This will delete ALL transactional data (Sales, Purchases, Inventory, HR, Journals). Do you want to continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->warn('Starting system wipe...');

        try {
            // 1. Disable Foreign Key Checks (MUST happen before transaction for SQLite)
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            DB::transaction(function () {
                // 2. Transactional Tables to Wipe
                $tables = [
                    'journal_entry_lines',
                    'journal_entries',

                    // Sales & Customers
                    'sale_items',
                    'sales',
                    'sale_return_items',
                    'sales_returns',
                    'quotations',
                    'quotation_customer',
                    'pos_held_sales',
                    'pos_shifts',
                    'customers', // Added: Customers Master
                    'customer_payments',

                    // Purchases & Suppliers
                    'purchase_items',
                    'purchase_invoice_lines',
                    'purchase_invoices', // Corrected
                    'purchase_order_lines',
                    'purchase_orders',     // Corrected
                    'purchase_return_lines',
                    'purchase_returns',   // Corrected
                    'grn_lines',
                    'grns',                           // Added
                    'supplier_payment_allocations',
                    'supplier_payments',
                    'suppliers',

                    // Inventory & Products
                    'stock_movements',
                    'product_stock',
                    'product_images',
                    'product_batches',
                    'product_serials',
                    'products',
                    'brands',
                    'categories',
                    'units',  // Units might be master data but often user-defined
                    'warehouses', // Process: Wipe Warehouses (Re-seeded below)

                    // Logistics
                    'couriers',
                    'shipments',
                    'shipment_status_histories',

                    // HR & Expenses
                    'expenses',
                    // HR & Expenses
                    'expenses',
                    'hr_payroll_items', // Corrected from payroll_items
                    'hr_payrolls',      // Corrected from payrolls
                    'hr_attendance',    // Added missing table
                    'hr_delivery_drivers', // Added missing table
                    'hr_leaves',
                    'hr_documents',
                    'hr_employees', // Process: Wipe Employees (Users preserved)

                    // Finance
                    'treasury_transactions',

                    // Logs
                    'activity_logs',
                    'security_audit_logs',
                    'price_override_logs',
                    'notifications',
                    'personal_access_tokens'
                ];

                foreach ($tables as $table) {
                    if (Schema::hasTable($table)) {
                        $count = DB::table($table)->count();
                        if ($count > 0) {
                            $this->line("Wiping table: {$table} ({$count} rows)...");
                            try {
                                // Use DELETE instead of TRUNCATE for better SQLite FK handling within transaction
                                DB::table($table)->delete();

                                // Verify empty
                                $newCount = DB::table($table)->count();
                                if ($newCount === 0) {
                                    $this->info("✔ {$table} wiped successfully.");
                                } else {
                                    $this->error("✘ FAILED to wipe {$table}. Remaining: {$newCount}");
                                }
                            } catch (\Exception $e) {
                                $this->error("✘ EXCEPTION wiping {$table}: " . $e->getMessage());
                            }
                        } else {
                            $this->line("Skipping empty table: {$table}");
                        }
                    } else {
                        // Only warn if it's a critical table we expect
                        if (in_array($table, ['hr_employees', 'warehouses', 'products'])) {
                            $this->warn("⚠ Table not found: {$table}");
                        }
                    }
                }

                // 3. Reset Financial Balances
                if (Schema::hasTable('accounts')) {
                    $this->line('Resetting account balances to 0...');
                    DB::table('accounts')->update(['balance' => 0]);
                }

                // Special handling for dependent tables if not in array
                if (Schema::hasTable('product_batches'))
                    DB::table('product_batches')->delete();
                if (Schema::hasTable('product_serials'))
                    DB::table('product_serials')->delete();


                // 5. Re-seed Default Warehouse (Critical for System Stability)
                if (Schema::hasTable('warehouses')) {
                    $this->line('Re-seeding default warehouse...');
                    DB::table('warehouses')->insert([
                        'id' => 1,
                        'code' => 'MAIN',
                        'name' => 'المخزن الرئيسي',
                        'address' => 'المقر الرئيسي',
                        'is_default' => true,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            // 5. Re-enable Foreign Key Checks (After transaction)
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            $this->info('System successfully reset to production-ready state (Chart of Accounts preserved).');
        } catch (\Exception $e) {
            $this->error('Reset failed: ' . $e->getMessage());
        }
    }
}

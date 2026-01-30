<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Performance Indexes Migration
 * Adds indexes for frequently queried columns to improve query performance
 * Safely checks for existing indexes AND tables before creating
 */
return new class extends Migration {
    /**
     * Check if an index exists on SQLite
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("PRAGMA index_list('$table')");
        foreach ($indexes as $index) {
            if ($index->name === $indexName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Safely add an index if table exists and index doesn't exist
     */
    private function addIndexIfNotExists(string $table, $columns, ?string $indexName = null): void
    {
        // Skip if table doesn't exist yet (modules may not have migrated)
        if (!Schema::hasTable($table)) {
            return;
        }

        $cols = is_array($columns) ? $columns : [$columns];
        $name = $indexName ?? $table . '_' . implode('_', $cols) . '_index';

        if (!$this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                $t->index($cols, $name);
            });
        }
    }

    public function up(): void
    {
        // Products - frequently searched
        $this->addIndexIfNotExists('products', 'sku');
        $this->addIndexIfNotExists('products', 'category_id');
        $this->addIndexIfNotExists('products', 'is_active');

        // Customers - searched by code
        $this->addIndexIfNotExists('customers', 'code');
        $this->addIndexIfNotExists('customers', 'is_active');

        // Suppliers - searched by code
        $this->addIndexIfNotExists('suppliers', 'code');
        $this->addIndexIfNotExists('suppliers', 'is_active');

        // Accounts - filtered by type
        $this->addIndexIfNotExists('accounts', 'code');
        $this->addIndexIfNotExists('accounts', 'type');
        $this->addIndexIfNotExists('accounts', 'is_active');
        $this->addIndexIfNotExists('accounts', 'parent_id');

        // Journal Entries - filtered by status, date
        $this->addIndexIfNotExists('journal_entries', 'entry_number');
        $this->addIndexIfNotExists('journal_entries', 'entry_date');
        $this->addIndexIfNotExists('journal_entries', 'status');

        // Journal Entry Lines - by account
        $this->addIndexIfNotExists('journal_entry_lines', 'account_id');

        // Sales Orders
        $this->addIndexIfNotExists('sales_orders', 'order_number');
        $this->addIndexIfNotExists('sales_orders', 'order_date');
        $this->addIndexIfNotExists('sales_orders', 'status');
        $this->addIndexIfNotExists('sales_orders', 'customer_id');

        // Sales Invoices
        $this->addIndexIfNotExists('sales_invoices', 'invoice_number');
        $this->addIndexIfNotExists('sales_invoices', 'invoice_date');
        $this->addIndexIfNotExists('sales_invoices', 'status');
        $this->addIndexIfNotExists('sales_invoices', 'customer_id');
        $this->addIndexIfNotExists('sales_invoices', 'due_date');

        // Purchase Orders
        $this->addIndexIfNotExists('purchase_orders', 'order_number');
        $this->addIndexIfNotExists('purchase_orders', 'order_date');
        $this->addIndexIfNotExists('purchase_orders', 'status');
        $this->addIndexIfNotExists('purchase_orders', 'supplier_id');

        // Purchase Invoices
        $this->addIndexIfNotExists('purchase_invoices', 'invoice_number');
        $this->addIndexIfNotExists('purchase_invoices', 'invoice_date');
        $this->addIndexIfNotExists('purchase_invoices', 'status');
        $this->addIndexIfNotExists('purchase_invoices', 'supplier_id');
        $this->addIndexIfNotExists('purchase_invoices', 'due_date');

        // Stock Movements
        $this->addIndexIfNotExists('stock_movements', 'product_id');
        $this->addIndexIfNotExists('stock_movements', 'warehouse_id');
        $this->addIndexIfNotExists('stock_movements', 'movement_date');
    }

    public function down(): void
    {
        // Indexes will be dropped when tables are dropped
        // No action needed for SQLite
    }
};

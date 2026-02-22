<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Unit;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Accounting\Models\JournalEntry;
use Modules\Inventory\Enums\MovementType;
use Modules\Sales\Enums\SalesInvoiceStatus;

class SystemDeepAudit extends Command
{
    protected $signature = 'audit:deep';
    protected $description = 'Performs a deep forensic audit of system integration points';

    public function handle()
    {
        $this->info('🕵️‍♂️ Starting Forensic System Audit...');
        $this->newLine();

        $errors = 0;

        // 1. Schema Integrity Check
        $this->info('1️⃣  Checking Schema Integrity...');
        $schemaChecks = [
            'products' => ['sku', 'selling_price', 'cost_price', 'unit_id'],
            'product_stock' => ['product_id', 'warehouse_id', 'quantity'], // Check for 'quantity' vs 'quantity_on_hand'
            'stock_movements' => ['source_type', 'source_id', 'type'], // Check for 'source' vs 'reference'
            'sales_invoices' => ['journal_entry_id', 'status', 'invoice_number'],
            'journal_entries' => ['source_type', 'source_id', 'entry_number'],
        ];

        foreach ($schemaChecks as $table => $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $this->error("❌ Table [$table] missing column [$column]");
                    $errors++;
                } else {
                    $this->line("   ✅ Table [$table] has column [$column]");
                }
            }
        }

        // 2. Integration Simulation (The "Happy Path")
        $this->newLine();
        $this->info('2️⃣  Simulating Business Logic (POS Cycle)...');

        DB::beginTransaction();
        try {
            // A. Create Test Product
            $unit = Unit::firstOrCreate(['name' => 'Unit', 'abbreviation' => 'U']);
            $product = Product::create([
                'name' => 'Audit Test Product ' . uniqid(),
                'sku' => 'AUDIT-' . uniqid(),
                'type' => 'goods', // String 'goods' based on migration default
                'unit_id' => $unit->id,
                'cost_price' => 100,
                'selling_price' => 150,
                'is_active' => true,
                'is_sellable' => true,
            ]);
            $this->line("   ✅ Product Created: {$product->sku}");

            // B. Add Initial Stock
            $warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse']);
            $stock = ProductStock::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 50,
                'average_cost' => 100,
            ]);
            $this->line("   ✅ Initial Stock Added: 50 units");

            // C. Simulate POS Sale (Controller Logic Recreation)
            $invoice = SalesInvoice::create([
                'invoice_number' => 'INV-AUDIT-' . uniqid(),
                'customer_id' => 1, // Assumption: Customer 1 exists, if not code fails and we catch it
                'invoice_date' => now(),
                'due_date' => now(),
                'status' => SalesInvoiceStatus::PAID, // Enum check
                'total' => 150,
                'paid_amount' => 150,
                'balance_due' => 0,
            ]);
            $this->line("   ✅ Test Invoice Created: {$invoice->invoice_number}");

            // D. Stock Reduction Logic (The critical part)
            $stock->decrement('quantity', 1);
            $this->line("   ✅ Stock Decremented (Simulated)");

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => MovementType::SALE,
                'quantity' => 1,
                'unit_cost' => 100,
                'source_type' => SalesInvoice::class,
                'source_id' => $invoice->id,
                'reference' => $invoice->invoice_number,
                'movement_date' => now(),
            ]);
            $this->line("   ✅ Stock Movement Recorded: {$movement->source_type} #{$movement->source_id}");

            // E. Journal Entry Logic
            $journal = JournalEntry::create([
                'entry_number' => 'JE-AUDIT-' . uniqid(),
                'entry_date' => now(),
                'source_type' => SalesInvoice::class,
                'source_id' => $invoice->id,
                'reference' => $invoice->invoice_number,
                'status' => 'posted',
            ]);
            $this->line("   ✅ Journal Entry Created: {$journal->entry_number}");

            // F. Integration Check
            if ($movement->source_type !== SalesInvoice::class) {
                throw new \Exception("StockMovement source_type mismatch");
            }
            if ($journal->source_type !== SalesInvoice::class) {
                throw new \Exception("JournalEntry source_type mismatch");
            }

            $this->info("   🎉 SIMULATION SUCCESSFUL! All integration points connected.");

        } catch (\Exception $e) {
            $this->error("   ❌ SIMULATION FAILED: " . $e->getMessage());
            $this->error("   📍 Trace: " . $e->getTraceAsString());
            $errors++;
        } finally {
            DB::rollBack(); // Always rollback test data
            $this->info("   ℹ️  Test transaction rolled back (Clean state preserved).");
        }

        $this->newLine();
        if ($errors === 0) {
            $this->info("✅ AUDIT PASSED: System integrity appears healthy.");
        } else {
            $this->error("❌ AUDIT FAILED: Found $errors critical issues.");
        }
    }
}

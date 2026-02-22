<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create grns table - Goods Received Notes
 * Links PO to actual receipt of goods
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->id();

            // GRN number (auto-generated: GRN-2026-000001)
            $table->string('grn_number', 30)->unique();

            // Link to Purchase Order
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->restrictOnDelete();

            // Supplier (denormalized for quick access)
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            // Receiving warehouse
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            // Dates
            $table->date('received_date');

            // Status
            $table->string('status', 20)->default('draft');

            // Supplier's delivery reference
            $table->string('supplier_delivery_note', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Journal entry (for inventory valuation)
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            // Audit
            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('received_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};

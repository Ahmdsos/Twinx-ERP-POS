<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create stock_movements table - Transaction history for inventory
 * 
 * Each movement records a change in stock quantity.
 * Used for FIFO costing and audit trail.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Movement number (auto-generated: SM-2026-000001)
            $table->string('movement_number', 30)->unique();

            // Movement date
            $table->date('movement_date');

            // Movement type
            $table->string('type', 30);

            // Product and warehouse
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            // For transfers - destination warehouse
            $table->foreignId('to_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            // Quantity moved (positive for in, negative for out)
            $table->decimal('quantity', 15, 4);

            // Unit cost at time of movement
            $table->decimal('unit_cost', 15, 4)->default(0);

            // Total value of movement
            $table->decimal('total_cost', 15, 2)->default(0);

            // Remaining quantity for FIFO (decreases as stock is consumed)
            $table->decimal('remaining_quantity', 15, 4)->default(0);

            // Source document reference (polymorphic)
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // For linked journal entry
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('movement_date');
            $table->index('type');
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['source_type', 'source_id']);
            $table->index(['product_id', 'remaining_quantity']); // For FIFO queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

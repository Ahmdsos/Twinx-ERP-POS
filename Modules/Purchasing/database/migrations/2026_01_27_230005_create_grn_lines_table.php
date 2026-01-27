<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create grn_lines table - GRN Line Items
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('grn_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grn_id')
                ->constrained('grns')
                ->cascadeOnDelete();

            // Link to PO line for tracking
            $table->foreignId('purchase_order_line_id')
                ->nullable()
                ->constrained('purchase_order_lines')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // Received quantity
            $table->decimal('quantity', 15, 4);

            // Unit cost at time of receipt (for FIFO tracking)
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('line_total', 15, 2)->default(0);

            // Stock movement created
            $table->foreignId('stock_movement_id')
                ->nullable()
                ->constrained('stock_movements')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_lines');
    }
};

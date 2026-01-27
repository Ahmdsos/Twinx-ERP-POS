<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create delivery_order_lines table
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_order_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_order_id')
                ->constrained('delivery_orders')
                ->cascadeOnDelete();

            // Link to SO line
            $table->foreignId('sales_order_line_id')
                ->nullable()
                ->constrained('sales_order_lines')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // Delivered quantity
            $table->decimal('quantity', 15, 4);

            // Cost at time of delivery (for COGS)
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('line_cost', 15, 2)->default(0);

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
        Schema::dropIfExists('delivery_order_lines');
    }
};

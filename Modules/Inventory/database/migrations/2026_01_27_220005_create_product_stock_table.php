<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create product_stock table - Current stock levels per warehouse
 * 
 * This table stores the current quantity and value of stock
 * for each product in each warehouse.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_stock', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            // Current quantity on hand
            $table->decimal('quantity', 15, 4)->default(0);

            // Reserved quantity (for pending orders)
            $table->decimal('reserved_quantity', 15, 4)->default(0);

            // Available = quantity - reserved
            $table->decimal('available_quantity', 15, 4)
                ->storedAs('quantity - reserved_quantity');

            // Total cost value of stock (for weighted average)
            $table->decimal('total_cost', 15, 2)->default(0);

            // Average cost per unit
            $table->decimal('average_cost', 15, 4)->default(0);

            // Last movement date
            $table->timestamp('last_movement_at')->nullable();

            $table->timestamps();

            // Unique constraint - one record per product per warehouse
            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product Serials Table
 * For tracking individual serial numbers
 * Only creates if required tables exist
 */
return new class extends Migration {
    public function up(): void
    {
        // Skip if required tables don't exist yet
        if (!Schema::hasTable('products') || !Schema::hasTable('warehouses')) {
            return;
        }

        if (Schema::hasTable('product_serials')) {
            return;
        }

        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'sold', 'reserved', 'returned', 'damaged'])->default('available');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();

            // References to transactions
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->unsignedBigInteger('sales_invoice_id')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};

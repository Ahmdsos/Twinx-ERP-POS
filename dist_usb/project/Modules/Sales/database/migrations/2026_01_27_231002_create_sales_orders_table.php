<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create sales_orders table - SO Header
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            // SO number (auto-generated: SO-2026-000001)
            $table->string('so_number', 30)->unique();

            // Customer
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Dates
            $table->date('order_date');
            $table->date('expected_date')->nullable();

            // Source warehouse for delivery
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            // Status
            $table->string('status', 20)->default('draft');

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // Currency
            $table->string('currency', 3)->default('EGP');
            $table->decimal('exchange_rate', 10, 4)->default(1);

            // Reference
            $table->string('reference', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable(); // Notes for customer

            // Shipping info
            $table->text('shipping_address')->nullable();
            $table->string('shipping_method', 50)->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('order_date');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};

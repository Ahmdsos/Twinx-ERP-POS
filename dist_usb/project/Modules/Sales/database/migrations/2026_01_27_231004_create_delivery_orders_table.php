<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create delivery_orders table - DO for shipping goods
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            // DO number (auto-generated: DO-2026-000001)
            $table->string('do_number', 30)->unique();

            // Link to Sales Order
            $table->foreignId('sales_order_id')
                ->constrained('sales_orders')
                ->restrictOnDelete();

            // Customer (denormalized)
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Source warehouse
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            // Dates
            $table->date('delivery_date');
            $table->date('shipped_date')->nullable();

            // Status
            $table->string('status', 20)->default('draft');

            // Shipping info
            $table->text('shipping_address');
            $table->string('shipping_method', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();

            // Driver/Carrier
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Journal entry (for COGS)
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            // Audit
            $table->foreignId('delivered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Make sales_order_id nullable for POS direct sales compatibility
            $table->unsignedBigInteger('sales_order_id')->nullable()->change();

            // Add sales_invoice_id for direct linking with POS invoices
            if (!Schema::hasColumn('delivery_orders', 'sales_invoice_id')) {
                $table->foreignId('sales_invoice_id')
                    ->after('sales_order_id')
                    ->nullable()
                    ->constrained('sales_invoices')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable(false)->change();
            $table->dropForeign(['sales_invoice_id']);
            $table->dropColumn('sales_invoice_id');
        });
    }
};

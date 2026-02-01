<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create sales_invoices table
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();

            // Invoice number (auto-generated: INV-2026-000001)
            $table->string('invoice_number', 30)->unique();

            // Customer
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Link to SO and/or DO
            $table->foreignId('sales_order_id')
                ->nullable()
                ->constrained('sales_orders')
                ->nullOnDelete();

            $table->foreignId('delivery_order_id')
                ->nullable()
                ->constrained('delivery_orders')
                ->nullOnDelete();

            // Dates
            $table->date('invoice_date');
            $table->date('due_date');

            // Status
            $table->string('status', 20)->default('draft');
            $table->string('source', 20)->default('manual'); // pos, manual, ecommerce

            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);

            // Currency
            $table->string('currency', 3)->default('EGP');
            $table->decimal('exchange_rate', 10, 4)->default(1);

            // Notes
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Journal entry (for Revenue & AR)
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('due_date');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};

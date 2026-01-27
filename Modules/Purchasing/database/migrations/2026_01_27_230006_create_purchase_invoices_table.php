<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create purchase_invoices table - Supplier Bills
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();

            // Invoice number (auto-generated: PI-2026-000001)
            $table->string('invoice_number', 30)->unique();

            // Supplier's invoice number
            $table->string('supplier_invoice_number', 50)->nullable();

            // Supplier
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            // Link to GRN (optional - invoice might come before goods)
            $table->foreignId('grn_id')
                ->nullable()
                ->constrained('grns')
                ->nullOnDelete();

            // Link to PO
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->nullOnDelete();

            // Dates
            $table->date('invoice_date');
            $table->date('due_date');

            // Status
            $table->string('status', 20)->default('draft');

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

            // Journal entry (for AP)
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
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};

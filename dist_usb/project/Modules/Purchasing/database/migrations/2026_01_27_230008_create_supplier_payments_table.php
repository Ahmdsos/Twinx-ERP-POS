<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create supplier_payments table - Payments to suppliers
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();

            // Payment number (auto-generated: PV-2026-000001)
            $table->string('payment_number', 30)->unique();

            // Supplier
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            // Payment date
            $table->date('payment_date');

            // Amount
            $table->decimal('amount', 15, 2);

            // Payment method
            $table->string('payment_method', 30)->default('bank_transfer');

            // Bank / Cash account
            $table->foreignId('payment_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            // Reference (cheque number, transfer reference)
            $table->string('reference', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Journal entry
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_date');
            $table->index('supplier_id');
        });

        // Pivot table for payment allocation to invoices
        Schema::create('supplier_payment_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_payment_id')
                ->constrained('supplier_payments')
                ->cascadeOnDelete();

            $table->foreignId('purchase_invoice_id')
                ->constrained('purchase_invoices')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->timestamps();

            $table->unique(['supplier_payment_id', 'purchase_invoice_id'], 'payment_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create customer_payments table - Receipts from customers
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();

            // Receipt number (auto-generated: RV-2026-000001)
            $table->string('receipt_number', 30)->unique();

            // Customer
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Payment date
            $table->date('payment_date');

            // Amount
            $table->decimal('amount', 15, 2);

            // Payment method
            $table->string('payment_method', 30)->default('cash');

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
            $table->index('customer_id');
        });

        // Pivot table for payment allocation to invoices
        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_payment_id')
                ->constrained('customer_payments')
                ->cascadeOnDelete();

            $table->foreignId('sales_invoice_id')
                ->constrained('sales_invoices')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->timestamps();

            $table->unique(['customer_payment_id', 'sales_invoice_id'], 'customer_payment_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_allocations');
        Schema::dropIfExists('customer_payments');
    }
};

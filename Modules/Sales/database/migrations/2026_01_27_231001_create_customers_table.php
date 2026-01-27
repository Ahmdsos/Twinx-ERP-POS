<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create customers table - Customer Master
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Customer code (auto-generated: CUS-001)
            $table->string('code', 20)->unique();

            // Company/Person name
            $table->string('name');
            $table->enum('type', ['individual', 'company'])->default('company');

            // Contact info
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();

            // Billing Address
            $table->text('billing_address')->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_country', 100)->default('Egypt');
            $table->string('billing_postal', 20)->nullable();

            // Shipping Address (if different)
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_country', 100)->nullable();
            $table->string('shipping_postal', 20)->nullable();

            // Business info
            $table->string('tax_number', 50)->nullable();  // VAT/Tax ID

            // Payment/Credit terms
            $table->integer('payment_terms')->default(30);  // Days
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Linked accounting account (Accounts Receivable sub-account)
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // Sales representative
            $table->foreignId('sales_rep_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Contact person
            $table->string('contact_person')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

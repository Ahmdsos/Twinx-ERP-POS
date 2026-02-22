<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create suppliers table - Vendor/Supplier Master
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Supplier code (auto-generated: SUP-001)
            $table->string('code', 20)->unique();

            // Company/Person name
            $table->string('name');

            // Contact info
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('fax', 20)->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Egypt');
            $table->string('postal_code', 20)->nullable();

            // Business info
            $table->string('tax_number', 50)->nullable();  // VAT/Tax ID
            $table->string('commercial_register', 50)->nullable();

            // Payment terms
            $table->integer('payment_terms')->default(30);  // Days
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Linked accounting account (Accounts Payable sub-account)
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
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
        Schema::dropIfExists('suppliers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete(); // Default Expense Account
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('expense_date');

            $table->foreignId('category_id')->constrained('expense_categories');
            $table->foreignId('payment_account_id')->constrained('accounts'); // Cash or Bank Account

            $table->decimal('amount', 15, 2); // Net Amount
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->string('payee')->nullable(); // Who was paid?
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();

            $table->string('status')->default('draft'); # draft, approved, rejected

            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};

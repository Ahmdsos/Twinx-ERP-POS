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
        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('type'); // 'receipt', 'payment'
            $table->decimal('amount', 15, 2);

            // The treasury account (Cash Box / Bank Account)
            $table->foreignId('treasury_account_id')->constrained('accounts')->restrictOnDelete();

            // The counter account (Expense / Customer / Supplier / Income)
            $table->foreignId('counter_account_id')->constrained('accounts')->restrictOnDelete();

            $table->string('description')->nullable();
            $table->string('reference')->nullable();

            // Link to Journal Entry
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_transactions');
    }
};

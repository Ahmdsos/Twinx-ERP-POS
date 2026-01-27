<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create journal_entry_lines table - Transaction Details
 * 
 * Each line represents a debit or credit to a specific account.
 * The sum of all debits must equal the sum of all credits.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            // Parent journal entry
            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->cascadeOnDelete();

            // Account being affected
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            // Debit amount (or 0 if credit)
            $table->decimal('debit', 15, 2)->default(0);

            // Credit amount (or 0 if debit)
            $table->decimal('credit', 15, 2)->default(0);

            // Line description/memo
            $table->string('description', 255)->nullable();

            // Cost center / department (future use)
            $table->string('cost_center', 50)->nullable();

            // Reference for sub-ledger (customer_id, supplier_id, etc.)
            $table->string('subledger_type', 50)->nullable();
            $table->unsignedBigInteger('subledger_id')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('account_id');
            $table->index(['subledger_type', 'subledger_id']);

            // Ensure either debit or credit is zero (not both positive)
            // This is enforced at application level, not DB level
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create journal_entries table - Transaction Headers
 * 
 * Each journal entry consists of multiple lines (debits and credits)
 * that must balance (total debits = total credits).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            // Entry number (auto-generated: JE-2026-000001)
            $table->string('entry_number', 30)->unique();

            // Transaction date
            $table->date('entry_date');

            // Fiscal year reference
            $table->foreignId('fiscal_year_id')
                ->nullable()
                ->constrained('fiscal_years')
                ->nullOnDelete();

            // Reference to source document (e.g., INV-2026-000001)
            $table->string('reference', 50)->nullable();

            // Source type for polymorphic relationship (e.g., 'sales_invoice', 'purchase_invoice')
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // Description/Narration
            $table->text('description')->nullable();

            // Entry status
            $table->string('status', 20)->default('draft'); // draft, posted, reversed

            // Total amounts (denormalized for quick access)
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            // Posted timestamp
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();

            // Reversal reference (if this entry was reversed)
            $table->foreignId('reversed_by_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('entry_date');
            $table->index('status');
            $table->index('reference');
            $table->index(['source_type', 'source_id']);
            $table->index('fiscal_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};

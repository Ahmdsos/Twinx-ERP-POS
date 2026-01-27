<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create accounts table - Chart of Accounts
 * 
 * This table stores the hierarchical chart of accounts.
 * Each account has a type (Asset, Liability, Equity, Revenue, Expense)
 * and can have parent-child relationships for sub-accounts.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Account code (e.g., 1000, 1100, 1110)
            $table->string('code', 20)->unique();

            // Account name
            $table->string('name');

            // Account type (asset, liability, equity, revenue, expense)
            $table->string('type', 20);

            // Parent account for hierarchy (null = root level)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // Description/notes
            $table->text('description')->nullable();

            // Is this a header account (has children, cannot post to directly)
            $table->boolean('is_header')->default(false);

            // Is this account active
            $table->boolean('is_active')->default(true);

            // System account flag (cannot be deleted)
            $table->boolean('is_system')->default(false);

            // Current balance (denormalized for performance)
            // Updated via triggers or jobs
            $table->decimal('balance', 15, 2)->default(0);

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('parent_id');
            $table->index('is_active');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

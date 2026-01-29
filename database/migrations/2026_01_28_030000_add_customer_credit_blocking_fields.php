<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations - Add credit control fields to customers
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Credit limit control
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('balance');
            }

            // Blocking status
            if (!Schema::hasColumn('customers', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('is_active');
            }

            // Block reason
            if (!Schema::hasColumn('customers', 'block_reason')) {
                $table->string('block_reason')->nullable()->after('is_blocked');
            }

            // Blocked date
            if (!Schema::hasColumn('customers', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('block_reason');
            }

            // Blocked by
            if (!Schema::hasColumn('customers', 'blocked_by')) {
                $table->foreignId('blocked_by')->nullable()->after('blocked_at')
                    ->constrained('users')->onDelete('set null');
            }

            // Grace period days (allow orders even if over limit for X days)
            if (!Schema::hasColumn('customers', 'credit_grace_days')) {
                $table->integer('credit_grace_days')->default(0)->after('credit_limit');
            }
        });

        // Add index for faster blocking checks
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['is_blocked', 'is_active'], 'customers_blocking_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_blocking_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'blocked_by')) {
                $table->dropConstrainedForeignId('blocked_by');
            }
            $table->dropColumn([
                'credit_limit',
                'credit_grace_days',
                'is_blocked',
                'block_reason',
                'blocked_at'
            ]);
        });
    }
};

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
        // Skip if customers table doesn't exist yet
        if (!Schema::hasTable('customers')) {
            return;
        }

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

            // Blocked by (without FK constraint to avoid issues)
            if (!Schema::hasColumn('customers', 'blocked_by')) {
                $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_at');
            }

            // Grace period days
            if (!Schema::hasColumn('customers', 'credit_grace_days')) {
                $table->integer('credit_grace_days')->default(0)->after('credit_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $columns = ['credit_limit', 'credit_grace_days', 'is_blocked', 'block_reason', 'blocked_at', 'blocked_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

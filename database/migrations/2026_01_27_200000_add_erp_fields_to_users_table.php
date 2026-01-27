<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add ERP-specific fields to users table
 * 
 * This migration extends the default Laravel users table
 * with fields needed for ERP functionality.
 * 
 * Note: employee_id will be added in Sprint 2 when Employees module is created
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Phone number for contact
            $table->string('phone', 20)->nullable()->after('email');

            // Active status flag
            $table->boolean('is_active')->default(true)->after('phone');

            // Audit fields - self-referencing foreign keys
            $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            // We add the foreign keys separately to handle the self-reference
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Indexes for common queries
            $table->index('is_active');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            // Drop indexes
            $table->dropIndex(['is_active']);
            $table->dropIndex(['phone']);

            // Drop columns
            $table->dropColumn([
                'phone',
                'is_active',
                'created_by',
                'updated_by',
            ]);
        });
    }
};


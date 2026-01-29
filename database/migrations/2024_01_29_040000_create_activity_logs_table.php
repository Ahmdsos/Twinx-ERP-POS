<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Activity Log Migration
 * Tracks user actions for audit trail
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who did the action
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable(); // Stored for historical reference

            // What was affected
            $table->string('subject_type')->nullable(); // e.g., App\Models\Customer
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_name')->nullable(); // Human readable: "Customer: ABC Corp"

            // The action
            $table->string('action'); // created, updated, deleted, viewed, logged_in, etc.
            $table->string('description')->nullable();

            // Data changes (for updates)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

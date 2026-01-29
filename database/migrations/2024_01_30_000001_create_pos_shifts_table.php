<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * POS Shifts Table
 * Tracks cashier shifts for POS operations
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();

            // User who opened the shift
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Shift timing
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            // Cash tracking
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();

            // Sales counts
            $table->integer('total_sales')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_card', 15, 2)->default(0);

            // Status
            $table->enum('status', ['open', 'closed'])->default('open');

            // Notes
            $table->text('closing_notes')->nullable();

            $table->timestamps();

            // Index for quick lookup of open shifts
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};

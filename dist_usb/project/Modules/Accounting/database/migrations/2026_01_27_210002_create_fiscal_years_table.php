<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create fiscal_years table
 * 
 * Tracks accounting periods for financial reporting.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();

            // Fiscal year name (e.g., "2026", "FY 2025-2026")
            $table->string('name', 50);

            // Period dates
            $table->date('start_date');
            $table->date('end_date');

            // Is this the current active fiscal year
            $table->boolean('is_active')->default(true);

            // Is this fiscal year closed (no more entries allowed)
            $table->boolean('is_closed')->default(false);

            // Closing date (when it was closed)
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Ensure no overlapping periods
            $table->unique(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create units table - Units of Measure
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            // Unit name (e.g., "Piece", "Kilogram", "Box")
            $table->string('name', 50);

            // Abbreviation (e.g., "pcs", "kg", "box")
            $table->string('abbreviation', 10)->unique();

            // Is this a base unit (cannot be derived from another)
            $table->boolean('is_base')->default(true);

            // Base unit reference (if derived)
            $table->foreignId('base_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            // Conversion factor to base unit (e.g., 12 pcs = 1 dozen)
            $table->decimal('conversion_factor', 15, 6)->default(1);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

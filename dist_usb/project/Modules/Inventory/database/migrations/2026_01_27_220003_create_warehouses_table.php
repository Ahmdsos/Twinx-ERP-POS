<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create warehouses table - Storage Locations
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();

            // Warehouse code (e.g., "WH-MAIN")
            $table->string('code', 20)->unique();

            // Warehouse name
            $table->string('name');

            // Address
            $table->text('address')->nullable();

            // Contact info
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Manager/responsible person
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Is this the default warehouse
            $table->boolean('is_default')->default(false);

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};

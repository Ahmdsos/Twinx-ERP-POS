<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create categories table - Product Categories
 * 
 * Hierarchical categories for organizing products.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Category name
            $table->string('name');

            // URL-friendly slug
            $table->string('slug')->unique();

            // Parent category for hierarchy
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Description
            $table->text('description')->nullable();

            // Display order
            $table->integer('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

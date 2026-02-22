<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product Images Migration
 * Supports multiple images per product with primary flag
 * Only creates table if products table exists (for FK constraint)
 */
return new class extends Migration {
    public function up(): void
    {
        // Skip if products table doesn't exist yet (created by Inventory module)
        if (!Schema::hasTable('products')) {
            // Create table without FK constraint if products doesn't exist yet
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('filename');
                $table->string('path');
                $table->string('disk')->default('public');
                $table->string('mime_type')->nullable();
                $table->integer('size')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->integer('sort_order')->default(0);
                $table->string('alt_text')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'is_primary']);
                $table->index(['product_id', 'sort_order']);
            });
            return;
        }

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_primary']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};

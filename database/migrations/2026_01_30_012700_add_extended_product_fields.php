<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Extended Product Fields Migration
 * Adds weight, brand, warranty, expiry, batch/serial tracking
 * Only runs if products table exists
 */
return new class extends Migration {
    public function up(): void
    {
        // Skip if products table doesn't exist yet (created by Inventory module)
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            // Physical attributes - check if columns exist first
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 10, 4)->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'weight_unit')) {
                $table->string('weight_unit', 10)->default('kg')->after('weight');
            }
            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 10, 2)->nullable()->after('weight_unit');
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 10, 2)->nullable()->after('length');
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('width');
            }
            if (!Schema::hasColumn('products', 'dimension_unit')) {
                $table->string('dimension_unit', 10)->default('cm')->after('height');
            }

            // Brand & Manufacturer
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand', 100)->nullable()->after('dimension_unit');
            }
            if (!Schema::hasColumn('products', 'manufacturer')) {
                $table->string('manufacturer', 100)->nullable()->after('brand');
            }
            if (!Schema::hasColumn('products', 'manufacturer_part_number')) {
                $table->string('manufacturer_part_number', 100)->nullable()->after('manufacturer');
            }

            // Warranty
            if (!Schema::hasColumn('products', 'warranty_months')) {
                $table->unsignedSmallInteger('warranty_months')->default(0)->after('manufacturer_part_number');
            }
            if (!Schema::hasColumn('products', 'warranty_type')) {
                $table->string('warranty_type', 50)->nullable()->after('warranty_months');
            }

            // Expiry & Shelf Life
            if (!Schema::hasColumn('products', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('warranty_type');
            }
            if (!Schema::hasColumn('products', 'shelf_life_days')) {
                $table->unsignedSmallInteger('shelf_life_days')->nullable()->after('expiry_date');
            }

            // Batch & Serial Tracking
            if (!Schema::hasColumn('products', 'track_batches')) {
                $table->boolean('track_batches')->default(false)->after('shelf_life_days');
            }
            if (!Schema::hasColumn('products', 'track_serials')) {
                $table->boolean('track_serials')->default(false)->after('track_batches');
            }

            // Additional Info
            if (!Schema::hasColumn('products', 'country_of_origin')) {
                $table->string('country_of_origin', 100)->nullable()->after('track_serials');
            }
            if (!Schema::hasColumn('products', 'hs_code')) {
                $table->string('hs_code', 50)->nullable()->after('country_of_origin');
            }
            if (!Schema::hasColumn('products', 'lead_time_days')) {
                $table->unsignedSmallInteger('lead_time_days')->default(0)->after('hs_code');
            }
            if (!Schema::hasColumn('products', 'is_returnable')) {
                $table->boolean('is_returnable')->default(true)->after('lead_time_days');
            }

            // Variants
            if (!Schema::hasColumn('products', 'color')) {
                $table->string('color', 50)->nullable()->after('is_returnable');
            }
            if (!Schema::hasColumn('products', 'size')) {
                $table->string('size', 50)->nullable()->after('color');
            }
            if (!Schema::hasColumn('products', 'tags')) {
                $table->json('tags')->nullable()->after('size');
            }

            // SEO (for e-commerce future)
            if (!Schema::hasColumn('products', 'seo_title')) {
                $table->string('seo_title', 255)->nullable()->after('tags');
            }
            if (!Schema::hasColumn('products', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('seo_title');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $columns = [
            'weight',
            'weight_unit',
            'length',
            'width',
            'height',
            'dimension_unit',
            'brand',
            'manufacturer',
            'manufacturer_part_number',
            'warranty_months',
            'warranty_type',
            'expiry_date',
            'shelf_life_days',
            'track_batches',
            'track_serials',
            'country_of_origin',
            'hs_code',
            'lead_time_days',
            'is_returnable',
            'color',
            'size',
            'tags',
            'seo_title',
            'seo_description'
        ];

        Schema::table('products', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

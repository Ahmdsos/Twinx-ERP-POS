<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Extra Fields to Products
 * Brand, weight, dimensions, expiry tracking, batch/lot, serial numbers
 * Only runs if products table exists (created by Inventory module)
 */
return new class extends Migration {
    public function up(): void
    {
        // Skip if products table doesn't exist yet (created by Inventory module)
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            // Check if columns already exist to avoid errors
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 10, 3)->nullable()->after('manufacturer');
            }
            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 10, 2)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 10, 2)->nullable()->after('length');
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('width');
            }
            if (!Schema::hasColumn('products', 'track_expiry')) {
                $table->boolean('track_expiry')->default(false)->after('is_purchasable');
            }
            if (!Schema::hasColumn('products', 'expiry_warning_days')) {
                $table->integer('expiry_warning_days')->default(30)->after('track_expiry');
            }
            if (!Schema::hasColumn('products', 'track_batch')) {
                $table->boolean('track_batch')->default(false)->after('expiry_warning_days');
            }
            if (!Schema::hasColumn('products', 'track_serial')) {
                $table->boolean('track_serial')->default(false)->after('track_batch');
            }
            if (!Schema::hasColumn('products', 'warranty_months')) {
                $table->integer('warranty_months')->nullable()->after('track_serial');
            }
            if (!Schema::hasColumn('products', 'notes')) {
                $table->text('notes')->nullable()->after('warranty_months');
            }
            if (!Schema::hasColumn('products', 'custom_attributes')) {
                $table->json('custom_attributes')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'brand',
                'manufacturer',
                'weight',
                'length',
                'width',
                'height',
                'track_expiry',
                'expiry_warning_days',
                'track_batch',
                'track_serial',
                'warranty_months',
                'notes',
                'custom_attributes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Extra Fields to Products
 * Brand, weight, dimensions, expiry tracking, batch/lot, serial numbers
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Brand/Manufacturer
            $table->string('brand')->nullable()->after('description');
            $table->string('manufacturer')->nullable()->after('brand');

            // Physical attributes
            $table->decimal('weight', 10, 3)->nullable()->after('manufacturer'); // in kg
            $table->decimal('length', 10, 2)->nullable()->after('weight'); // in cm
            $table->decimal('width', 10, 2)->nullable()->after('length'); // in cm
            $table->decimal('height', 10, 2)->nullable()->after('width'); // in cm

            // Tracking options
            $table->boolean('track_expiry')->default(false)->after('is_purchasable');
            $table->integer('expiry_warning_days')->default(30)->after('track_expiry');
            $table->boolean('track_batch')->default(false)->after('expiry_warning_days');
            $table->boolean('track_serial')->default(false)->after('track_batch');

            // Warranty
            $table->integer('warranty_months')->nullable()->after('track_serial');

            // Additional info
            $table->text('notes')->nullable()->after('warranty_months');
            $table->json('custom_attributes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};

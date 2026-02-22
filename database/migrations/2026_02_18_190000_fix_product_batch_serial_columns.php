<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix product_batches and product_serials columns to match model fillable
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('product_batches', 'lot_number')) {
                $table->string('lot_number')->nullable()->after('batch_number');
            }
            if (!Schema::hasColumn('product_batches', 'manufacturing_date')) {
                $table->date('manufacturing_date')->nullable()->after('lot_number');
            }
            if (!Schema::hasColumn('product_batches', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('product_batches', 'supplier_batch')) {
                $table->string('supplier_batch')->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('product_batches', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });

        Schema::table('product_serials', function (Blueprint $table) {
            if (!Schema::hasColumn('product_serials', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('product_serials', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('product_serials', 'warranty_start')) {
                $table->date('warranty_start')->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('product_serials', 'warranty_end')) {
                $table->date('warranty_end')->nullable()->after('warranty_start');
            }
            if (!Schema::hasColumn('product_serials', 'purchase_invoice_id')) {
                $table->foreignId('purchase_invoice_id')->nullable()->after('warranty_end');
            }
        });
    }

    public function down(): void
    {
        // Not removing columns to avoid data loss
    }
};

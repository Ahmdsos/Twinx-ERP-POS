<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create products table - Product Master
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // SKU - Stock Keeping Unit (unique identifier)
            $table->string('sku', 50)->unique();

            // Barcode (EAN, UPC, etc.)
            $table->string('barcode', 50)->nullable()->index();

            // Product name
            $table->string('name');

            // Description
            $table->text('description')->nullable();

            // Product type (goods, service, consumable)
            $table->string('type', 20)->default('goods');

            // Category
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Units
            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            // Purchase unit (if different from sales unit)
            $table->foreignId('purchase_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            // Pricing
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('min_selling_price', 15, 2)->nullable();

            // Tax settings
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);

            // Inventory settings
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->nullable();

            // Accounting integration
            $table->foreignId('sales_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('purchase_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('inventory_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_purchasable')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('category_id');
            $table->index('is_active');
            $table->index(['is_active', 'is_sellable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

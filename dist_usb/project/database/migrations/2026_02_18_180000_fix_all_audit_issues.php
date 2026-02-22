<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix All Audit Issues Migration
 * 
 * This migration addresses ALL issues found by the system audit:
 * 1. Missing columns in customers table (fillable mismatch)
 * 2. Missing columns in sales_invoices table (fillable mismatch)
 * 3. Missing product_batches table
 * 4. Missing product_serials table
 */
return new class extends Migration {
    public function up(): void
    {
        // ===========================
        // 1. Customer table - Ensure all fillable columns exist
        // ===========================
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'billing_postal')) {
                $table->string('billing_postal', 20)->nullable()->after('billing_city');
            }
            if (!Schema::hasColumn('customers', 'shipping_country')) {
                $table->string('shipping_country', 100)->nullable()->after('shipping_city');
            }
            if (!Schema::hasColumn('customers', 'shipping_postal')) {
                $table->string('shipping_postal', 20)->nullable()->after('shipping_country');
            }
            if (!Schema::hasColumn('customers', 'credit_grace_days')) {
                $table->integer('credit_grace_days')->default(0)->after('credit_limit');
            }
            if (!Schema::hasColumn('customers', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('credit_grace_days');
            }
            if (!Schema::hasColumn('customers', 'sales_rep_id')) {
                $table->foreignId('sales_rep_id')->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('customers', 'billing_country')) {
                $table->string('billing_country', 100)->nullable()->after('billing_city');
            }
        });

        // ===========================
        // 2. Sales Invoices - Ensure delivery_status column exists
        // ===========================
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'delivery_status')) {
                $table->string('delivery_status', 50)->nullable()->after('delivery_fee');
            }
            if (!Schema::hasColumn('sales_invoices', 'source')) {
                $table->string('source', 20)->default('manual')->after('notes');
            }
            if (!Schema::hasColumn('sales_invoices', 'details')) {
                $table->json('details')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('sales_invoices', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('notes');
            }
        });

        // ===========================
        // 3. Create product_batches table if it doesn't exist
        // ===========================
        if (!Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('batch_number');
                $table->date('manufacture_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('quantity', 15, 2)->default(0);
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->string('status', 20)->default('active'); // active, expired, consumed
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'batch_number']);
                $table->index('expiry_date');
            });
        }

        // ===========================
        // 4. Create product_serials table if it doesn't exist
        // ===========================
        if (!Schema::hasTable('product_serials')) {
            Schema::create('product_serials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('serial_number')->unique();
                $table->string('status', 20)->default('available'); // available, sold, returned, defective
                $table->foreignId('purchase_order_id')->nullable();
                $table->foreignId('sales_invoice_id')->nullable();
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
        Schema::dropIfExists('product_batches');

        // Note: We don't remove columns in down() to avoid data loss
    }
};

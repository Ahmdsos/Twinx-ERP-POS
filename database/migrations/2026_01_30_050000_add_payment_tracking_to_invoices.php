<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add payment tracking columns to invoices tables
 * These columns track amount_paid and balance_due for Sales and Purchase invoices
 */
return new class extends Migration {
    public function up(): void
    {
        // Add payment tracking columns to sales_invoices
        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_invoices', 'amount_paid')) {
                    $table->decimal('amount_paid', 15, 2)->default(0)->after('total');
                }
                if (!Schema::hasColumn('sales_invoices', 'balance_due')) {
                    $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
                }
            });
        }

        // Add payment tracking columns to purchase_invoices
        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoices', 'amount_paid')) {
                    $table->decimal('amount_paid', 15, 2)->default(0)->after('total');
                }
                if (!Schema::hasColumn('purchase_invoices', 'balance_due')) {
                    $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('sales_invoices', 'amount_paid')) {
                    $table->dropColumn('amount_paid');
                }
                if (Schema::hasColumn('sales_invoices', 'balance_due')) {
                    $table->dropColumn('balance_due');
                }
            });
        }

        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoices', 'amount_paid')) {
                    $table->dropColumn('amount_paid');
                }
                if (Schema::hasColumn('purchase_invoices', 'balance_due')) {
                    $table->dropColumn('balance_due');
                }
            });
        }
    }
};

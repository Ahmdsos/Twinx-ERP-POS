<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comprehensive migration to ensure ALL required columns exist.
 * This handles columns that were missing from original migrations
 * but are required by the application code.
 *
 * Safe to run on both fresh installs and existing databases.
 */
return new class extends Migration {
    public function up(): void
    {
        // ============================================================
        // 1. sales_invoices — POS & delivery columns
        // ============================================================
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'delivery_fee')) {
                $table->decimal('delivery_fee', 15, 2)->default(0)->after('deleted_at');
            }
            if (!Schema::hasColumn('sales_invoices', 'is_delivery')) {
                $table->boolean('is_delivery')->default(false)->after('delivery_fee');
            }
            if (!Schema::hasColumn('sales_invoices', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('is_delivery');
            }
            if (!Schema::hasColumn('sales_invoices', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('driver_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('sales_invoices', 'pos_shift_id')) {
                $table->unsignedBigInteger('pos_shift_id')->nullable()->after('warehouse_id');
            }
        });

        // ============================================================
        // 2. expenses — POS shift link & user
        // ============================================================
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('deleted_at');
            }
            if (!Schema::hasColumn('expenses', 'pos_shift_id')) {
                $table->unsignedBigInteger('pos_shift_id')->nullable()->after('user_id');
            }
        });

        // ============================================================
        // 3. sales_returns — shift link
        // ============================================================
        if (Schema::hasTable('sales_returns')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_returns', 'shift_id')) {
                    $table->unsignedBigInteger('shift_id')->nullable()->after('deleted_at');
                }
            });
        }

        // ============================================================
        // 4. delivery_orders — POS-specific columns
        // ============================================================
        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_orders', 'courier_id')) {
                    $table->unsignedBigInteger('courier_id')->nullable()->after('deleted_at');
                }
                if (!Schema::hasColumn('delivery_orders', 'driver_id')) {
                    $table->unsignedBigInteger('driver_id')->nullable()->after('courier_id');
                }
                if (!Schema::hasColumn('delivery_orders', 'sales_invoice_id')) {
                    $table->unsignedBigInteger('sales_invoice_id')->nullable()->after('driver_id');
                }
                if (!Schema::hasColumn('delivery_orders', 'recipient_name')) {
                    $table->string('recipient_name')->nullable()->after('sales_invoice_id');
                }
                if (!Schema::hasColumn('delivery_orders', 'recipient_phone')) {
                    $table->string('recipient_phone')->nullable()->after('recipient_name');
                }
            });
        }

        // ============================================================
        // 5. pos_shifts — ensure total_credit column exists
        // ============================================================
        if (Schema::hasTable('pos_shifts')) {
            Schema::table('pos_shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('pos_shifts', 'total_credit')) {
                    $table->decimal('total_credit', 15, 2)->default(0)->after('total_card');
                }
            });
        }

        // ============================================================
        // 6. hr_employees — ensure all professional fields
        // ============================================================
        if (Schema::hasTable('hr_employees')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                if (!Schema::hasColumn('hr_employees', 'birth_date')) {
                    $table->date('birth_date')->nullable()->after('deleted_at');
                }
                if (!Schema::hasColumn('hr_employees', 'gender')) {
                    $table->string('gender', 10)->nullable()->after('birth_date');
                }
                if (!Schema::hasColumn('hr_employees', 'nationality')) {
                    $table->string('nationality', 50)->nullable()->after('gender');
                }
            });
        }

        // ============================================================
        // 7. hr_payrolls — journal entry link
        // ============================================================
        if (Schema::hasTable('hr_payrolls')) {
            Schema::table('hr_payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('hr_payrolls', 'journal_entry_id')) {
                    $table->unsignedBigInteger('journal_entry_id')->nullable()->after('updated_by');
                }
                if (!Schema::hasColumn('hr_payrolls', 'total_advance_deductions')) {
                    $table->decimal('total_advance_deductions', 15, 2)->default(0)->after('journal_entry_id');
                }
            });
        }

        // ============================================================
        // 8. hr_payroll_items — advance deductions
        // ============================================================
        if (Schema::hasTable('hr_payroll_items')) {
            Schema::table('hr_payroll_items', function (Blueprint $table) {
                if (!Schema::hasColumn('hr_payroll_items', 'advance_deductions')) {
                    $table->decimal('advance_deductions', 15, 2)->default(0)->after('notes');
                }
            });
        }

        // ============================================================
        // 9. accounts — Arabic name
        // ============================================================
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('deleted_at');
                }
            });
        }
    }

    public function down(): void
    {
        // These are additive columns; dropping them in reverse
        $drops = [
            'sales_invoices' => ['delivery_fee', 'is_delivery', 'driver_id', 'shipping_address', 'warehouse_id', 'pos_shift_id'],
            'expenses' => ['user_id', 'pos_shift_id'],
            'sales_returns' => ['shift_id'],
            'delivery_orders' => ['courier_id', 'driver_id', 'sales_invoice_id', 'recipient_name', 'recipient_phone'],
            'pos_shifts' => ['total_credit'],
            'accounts' => ['name_ar'],
        ];

        foreach ($drops as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $col) {
                        if (Schema::hasColumn($table->getTable(), $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
    }
};

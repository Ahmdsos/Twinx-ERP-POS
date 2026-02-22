<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_payrolls', 'total_advance_deductions')) {
                $table->decimal('total_advance_deductions', 15, 2)->default(0)->after('total_deductions');
            }
        });

        Schema::table('hr_payroll_items', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_payroll_items', 'advance_deductions')) {
                $table->decimal('advance_deductions', 15, 2)->default(0)->after('deductions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->dropColumn('total_advance_deductions');
        });

        Schema::table('hr_payroll_items', function (Blueprint $table) {
            $table->dropColumn('advance_deductions');
        });
    }
};

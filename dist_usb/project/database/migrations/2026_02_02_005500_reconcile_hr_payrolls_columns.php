<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_payrolls', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            }
            if (!Schema::hasColumn('hr_payrolls', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('hr_payrolls', 'total_allowances')) {
                $table->decimal('total_allowances', 15, 2)->default(0)->after('total_basic');
            }
            if (!Schema::hasColumn('hr_payrolls', 'total_deductions')) {
                $table->decimal('total_deductions', 15, 2)->default(0)->after('total_allowances');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->dropColumn(['journal_entry_id', 'processed_by', 'total_allowances', 'total_deductions']);
        });
    }
};

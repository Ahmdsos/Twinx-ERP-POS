<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'pos_shift_id')) {
                $table->foreignId('pos_shift_id')->nullable()->constrained('pos_shifts')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales_invoices', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('balance_due');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['pos_shift_id']);
            $table->dropColumn(['pos_shift_id', 'payment_method']);
        });
    }
};

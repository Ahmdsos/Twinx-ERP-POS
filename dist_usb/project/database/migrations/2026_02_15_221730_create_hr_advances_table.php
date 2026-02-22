<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('request_date')->default(now());
            $table->integer('repayment_month');
            $table->integer('repayment_year');
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'deducted'])->default('pending');
            $table->text('notes')->nullable();

            // Approvals & Audit
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Accounting Link
            $table->foreignId('journal_entry_id')->nullable()->comment('Link to Payment Voucher');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('hr_payroll_items', function (Blueprint $table) {
            $table->decimal('advance_deductions', 15, 2)->default(0)->after('deductions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_payroll_items', function (Blueprint $table) {
            $table->dropColumn('advance_deductions');
        });
        Schema::dropIfExists('hr_advances');
    }
};

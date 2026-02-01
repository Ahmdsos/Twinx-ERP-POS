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
        // 1. Employees Table
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->string('id_number')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Attendance Table
        Schema::create('hr_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'on_leave'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
        });

        // 3. Payrolls Table
        Schema::create('hr_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->integer('month');
            $table->integer('year');
            $table->date('process_date');
            $table->decimal('total_basic', 15, 2)->default(0);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->enum('status', ['draft', 'processed', 'paid', 'cancelled'])->default('draft');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['month', 'year'], 'unique_payroll_period');
        });

        // 4. Payroll Items (Lines)
        Schema::create('hr_payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('hr_payrolls')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Delivery Drivers Table (Specialized)
        Schema::create('hr_delivery_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('hr_employees')->onDelete('cascade');
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('vehicle_info')->nullable();
            $table->enum('status', ['available', 'on_delivery', 'offline', 'suspended'])->default('available');
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_deliveries')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_delivery_drivers');
        Schema::dropIfExists('hr_payroll_items');
        Schema::dropIfExists('hr_payrolls');
        Schema::dropIfExists('hr_attendance');
        Schema::dropIfExists('hr_employees');
    }
};

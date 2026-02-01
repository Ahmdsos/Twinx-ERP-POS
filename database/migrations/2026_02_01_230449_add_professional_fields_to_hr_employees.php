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
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->date('birth_date')->after('last_name')->nullable();
            $table->string('gender')->after('birth_date')->nullable();
            $table->string('nationality')->after('gender')->nullable();
            $table->string('marital_status')->after('nationality')->nullable();
            $table->string('bank_name')->after('basic_salary')->nullable();
            $table->string('bank_account_number')->after('bank_name')->nullable();
            $table->string('iban')->after('bank_account_number')->nullable();
            $table->string('social_security_number')->after('iban')->nullable();
            $table->string('contract_type')->after('social_security_number')->nullable();
            $table->string('emergency_contact_name')->after('address')->nullable();
            $table->string('emergency_contact_phone')->after('emergency_contact_name')->nullable();
            $table->foreignId('updated_by')->after('created_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'gender',
                'nationality',
                'marital_status',
                'bank_name',
                'bank_account_number',
                'iban',
                'social_security_number',
                'contract_type',
                'emergency_contact_name',
                'emergency_contact_phone',
                'updated_by'
            ]);
        });
    }
};

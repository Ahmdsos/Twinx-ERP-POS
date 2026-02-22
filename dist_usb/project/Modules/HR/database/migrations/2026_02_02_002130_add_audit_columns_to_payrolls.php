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
        Schema::table('hr_payrolls', function (Blueprint $blueprint) {
            $blueprint->foreignId('created_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $blueprint->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['created_by', 'updated_by']);
        });
    }
};

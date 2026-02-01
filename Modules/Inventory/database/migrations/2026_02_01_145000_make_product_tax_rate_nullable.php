<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make column nullable
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->nullable()->default(null)->change();
        });

        // 2. Convert existing 0.00 to NULL to enable inheritance
        DB::table('products')->where('tax_rate', 0)->update(['tax_rate' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert NULLs to 0 before changing schema
        DB::table('products')->whereNull('tax_rate')->update(['tax_rate' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->change();
        });
    }
};

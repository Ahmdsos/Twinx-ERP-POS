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
        Schema::table('products', function (Blueprint $table) {
            // Add new pricing tiers
            // All nullable, defaulting to 0 or null
            $table->decimal('price_distributor', 10, 2)->nullable()->default(0)->after('selling_price');
            $table->decimal('price_wholesale', 10, 2)->nullable()->default(0)->after('price_distributor');
            $table->decimal('price_half_wholesale', 10, 2)->nullable()->default(0)->after('price_wholesale');
            $table->decimal('price_quarter_wholesale', 10, 2)->nullable()->default(0)->after('price_half_wholesale');
            $table->decimal('price_special', 10, 2)->nullable()->default(0)->after('price_quarter_wholesale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_distributor',
                'price_wholesale',
                'price_half_wholesale',
                'price_quarter_wholesale',
                'price_special',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6: Advanced Features Migration
 * Creates tables for Multi-Currency and Loyalty Program
 */
return new class extends Migration {
    public function up(): void
    {
        // ==========================================
        // MULTI-CURRENCY TABLES
        // ==========================================

        // Currencies table
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // e.g., EGP, USD, EUR
            $table->string('name');
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 12, 6)->default(1.00);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('decimal_places')->default(2);
            $table->timestamps();
        });

        // Exchange rate history
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('rate', 12, 6);
            $table->date('effective_date');
            $table->timestamps();

            $table->index(['currency_id', 'effective_date']);
        });

        // ==========================================
        // LOYALTY PROGRAM TABLES
        // ==========================================

        // Loyalty Points Balance per customer
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->integer('total_earned')->default(0);
            $table->integer('total_redeemed')->default(0);
            $table->integer('current_balance')->default(0);
            $table->string('tier')->default('bronze'); // bronze, silver, gold, platinum
            $table->date('tier_expiry')->nullable();
            $table->timestamps();

            $table->unique('customer_id');
        });

        // Loyalty Transactions
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjust']);
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('reference_type')->nullable(); // SalesInvoice, Manual, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });

        // Loyalty Settings
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        // Insert default loyalty settings
        DB::table('loyalty_settings')->insert([
            ['key' => 'points_per_amount', 'value' => '1', 'created_at' => now(), 'updated_at' => now()], // 1 point per 10 EGP
            ['key' => 'amount_per_point', 'value' => '10', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'points_value', 'value' => '0.1', 'created_at' => now(), 'updated_at' => now()], // 1 point = 0.1 EGP
            ['key' => 'min_redeem_points', 'value' => '100', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'expiry_days', 'value' => '365', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert default currency (Egyptian Pound)
        DB::table('currencies')->insert([
            ['code' => 'EGP', 'name' => 'الجنيه المصري', 'symbol' => 'ج.م', 'exchange_rate' => 1.00, 'is_default' => true, 'is_active' => true, 'decimal_places' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'name' => 'دولار أمريكي', 'symbol' => '$', 'exchange_rate' => 0.020, 'is_default' => false, 'is_active' => true, 'decimal_places' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR', 'name' => 'يورو', 'symbol' => '€', 'exchange_rate' => 0.018, 'is_default' => false, 'is_active' => true, 'decimal_places' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SAR', 'name' => 'ريال سعودي', 'symbol' => 'ر.س', 'exchange_rate' => 0.075, 'is_default' => false, 'is_active' => true, 'decimal_places' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_settings');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};

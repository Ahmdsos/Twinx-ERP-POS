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
        Schema::create('price_override_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('Cashier who made the override');
            $table->foreignId('manager_id')->nullable()->constrained('users')->comment('Manager who approved');
            $table->foreignId('shift_id')->nullable()->constrained('pos_shifts');
            $table->foreignId('product_id')->constrained();
            $table->decimal('original_price', 12, 2);
            $table->decimal('override_price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('approval_method')->default('pin'); // pin, manager_login, none
            $table->boolean('is_approved')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['shift_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_override_logs');
    }
};

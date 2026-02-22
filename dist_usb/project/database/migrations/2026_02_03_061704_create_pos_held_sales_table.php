<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * C-04: POS Held Sales - Database persistence for held/parked sales
 * Replaces session-based storage with DB for cross-cashier access
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_held_sales', function (Blueprint $table) {
            $table->id();
            $table->string('hold_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->json('items');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('held'); // held, resumed, cancelled
            $table->timestamp('resumed_at')->nullable();
            $table->foreignId('resumed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_held_sales');
    }
};

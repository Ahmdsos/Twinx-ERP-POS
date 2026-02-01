<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique(); // SR-2024-0001
            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('return_date');
            $table->string('status')->default('draft'); // draft, approved, completed, rejected

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->nullable()->constrained(); // Ensure unit is tracked

            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2); // Refund price
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            $table->string('return_reason')->nullable(); // Damaged, Wrong Item, etc.
            $table->string('item_condition')->default('resalable'); // resalable, damaged

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_lines');
        Schema::dropIfExists('sales_returns');
    }
};

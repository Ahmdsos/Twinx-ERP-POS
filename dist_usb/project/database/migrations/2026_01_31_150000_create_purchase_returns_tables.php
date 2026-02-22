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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('return_date');
            $table->string('status')->default('draft'); // draft, approved, cancelled

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('journal_entry_id')->nullable(); // For accounting linkage

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();

            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2); // Cost price at time of return
            $table->decimal('line_total', 15, 2);

            $table->text('reason')->nullable(); // Specific reason for this item

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_lines');
        Schema::dropIfExists('purchase_returns');
    }
};

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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('quotation_date');
            $table->index('valid_until');
        });

        Schema::create('quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Index
            $table->index('quotation_id');
        });

        // Add quotation_id to sales_orders if not exists
        if (!Schema::hasColumn('sales_orders', 'quotation_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('quotation_id')->nullable()->after('customer_id')
                    ->constrained()->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key from sales_orders
        if (Schema::hasColumn('sales_orders', 'quotation_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('quotation_id');
            });
        }

        Schema::dropIfExists('quotation_lines');
        Schema::dropIfExists('quotations');
    }
};

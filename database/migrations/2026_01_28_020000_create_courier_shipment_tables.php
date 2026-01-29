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
        // Couriers table (شركات الشحن)
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tracking_url_template', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // Shipments table (الشحنات)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('delivery_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('courier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('tracking_number', 100)->nullable();
            $table->date('shipped_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->string('status')->default('pending');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
            $table->index('tracking_number');
            $table->index(['courier_id', 'status']);
        });

        // Shipment Status History (سجل حالة الشحنات)
        Schema::create('shipment_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('shipment_id');
        });

        // Add courier_id to delivery_orders if not exists
        if (!Schema::hasColumn('delivery_orders', 'courier_id')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->foreignId('courier_id')->nullable()->after('warehouse_id')
                    ->constrained()->onDelete('set null');
            });
        }

        // Add tracking_number to delivery_orders if not exists
        if (!Schema::hasColumn('delivery_orders', 'tracking_number')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->string('tracking_number', 100)->nullable()->after('courier_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove courier columns from delivery_orders
        if (Schema::hasColumn('delivery_orders', 'courier_id')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('courier_id');
                $table->dropColumn('tracking_number');
            });
        }

        Schema::dropIfExists('shipment_status_history');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('couriers');
    }
};

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
        Schema::disableForeignKeyConstraints();

        // 0. Cleanup in case of previous failure
        Schema::dropIfExists('customers_new');

        // 1. Create new table with updated constraints (or no check constraint on type)
        Schema::create('customers_new', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Ensure code is unique
            $table->string('type')->default('consumer'); // Basic string, handled by app validation
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();

            // Addresses
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();

            // Financial
            $table->string('tax_number')->nullable();
            $table->integer('payment_terms')->default(0); // Days
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Status and Blocking
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->text('block_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();

            // Meta
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();

            // Timestamps and Tracking
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Copy data from old table to new table
        // We select matching columns explicitly to avoid issues
        $columns = [
            'id',
            'name',
            'code',
            'type',
            'email',
            'phone',
            'mobile',
            'billing_address',
            'billing_city',
            'shipping_address',
            'shipping_city',
            'tax_number',
            'payment_terms',
            'credit_limit',
            'is_active',
            'is_blocked',
            'block_reason',
            'blocked_at',
            'blocked_by',
            'contact_person',
            'notes',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        // Dynamic column selection based on what actually exists in the old table
        $oldColumns = Schema::getColumnListing('customers');
        $selectColumns = array_intersect($columns, $oldColumns);
        $selectString = implode(', ', $selectColumns);

        if (!empty($selectString)) {
            DB::statement("INSERT INTO customers_new ($selectString) SELECT $selectString FROM customers");
        }

        // 3. Drop old table
        Schema::drop('customers');

        // 4. Rename new table to old table name
        Schema::rename('customers_new', 'customers');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this complex migration is risky if data has changed, 
        // but we can try to restore the constraint if needed. 
        // For now, we leave it as is since 'string' type is compatible with strict checks too.
    }
};

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
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->date('expense_date');
                $table->foreignId('category_id')->nullable();
                $table->decimal('amount', 12, 2);
                $table->foreignId('user_id')->constrained('users');
                $table->text('notes')->nullable();
                $table->foreignId('pos_shift_id')->nullable()->constrained('pos_shifts');
                $table->softDeletes();
                $table->timestamps();
            });
        } else {
            // Table exists (likely from Accounting module), ensure POS fields exist
            Schema::table('expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('expenses', 'pos_shift_id')) {
                    $table->foreignId('pos_shift_id')->nullable()->constrained('pos_shifts');
                }
                if (!Schema::hasColumn('expenses', 'user_id')) { // Accounting uses created_by usually
                    $table->foreignId('user_id')->nullable()->constrained('users');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop table if it might have existed before
        if (Schema::hasColumn('expenses', 'pos_shift_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['pos_shift_id']);
                $table->dropColumn('pos_shift_id');
            });
        }
    }
};

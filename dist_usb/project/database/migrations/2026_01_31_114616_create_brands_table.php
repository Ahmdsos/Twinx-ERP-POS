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
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->string('website')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'name'))
                    $table->string('name')->unique();
                if (!Schema::hasColumn('brands', 'description'))
                    $table->text('description')->nullable();
                if (!Schema::hasColumn('brands', 'website'))
                    $table->string('website')->nullable();
                if (!Schema::hasColumn('brands', 'is_active'))
                    $table->boolean('is_active')->default(true);
                if (!Schema::hasColumn('brands', 'deleted_at'))
                    $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

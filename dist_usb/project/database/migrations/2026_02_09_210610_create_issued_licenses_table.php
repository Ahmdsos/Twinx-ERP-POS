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
        Schema::create('issued_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('machine_id');
            $table->text('license_key');
            $table->dateTime('expires_at');
            $table->string('status')->default('active'); // active, revoked
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_licenses');
    }
};

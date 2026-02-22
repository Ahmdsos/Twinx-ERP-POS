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
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->comment('User who performed action');
            $table->foreignId('shift_id')->nullable()->constrained('pos_shifts');
            $table->string('event_type', 50); // failed_pin, cart_delete, void_transaction, shift_sign
            $table->string('event_category', 30)->default('security'); // security, audit, transaction
            $table->string('severity', 20)->default('info'); // info, warning, critical
            $table->text('description');
            $table->json('metadata')->nullable(); // Extra data like product_id, amount, etc.
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('requires_review')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['shift_id', 'event_type']);
            $table->index('requires_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};

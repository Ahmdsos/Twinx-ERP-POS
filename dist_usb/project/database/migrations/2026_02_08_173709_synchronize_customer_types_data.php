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
        // 1. Update existing 'individual' to 'consumer'
        \Illuminate\Support\Facades\DB::table('customers')
            ->where('type', 'individual')
            ->update(['type' => 'consumer']);

        // 2. Update existing 'business' or 'company' to 'company'
        // (Just in case 'business' was used as per previous enum version)
        \Illuminate\Support\Facades\DB::table('customers')
            ->where('type', 'business')
            ->update(['type' => 'company']);

        // 3. Update Quotations target_customer_type as well
        \Illuminate\Support\Facades\DB::table('quotations')
            ->where('target_customer_type', 'individual')
            ->update(['target_customer_type' => 'consumer']);

        \Illuminate\Support\Facades\DB::table('quotations')
            ->where('target_customer_type', 'business')
            ->update(['target_customer_type' => 'company']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse is optional but good for completeness if safe
        \Illuminate\Support\Facades\DB::table('customers')
            ->where('type', 'consumer')
            ->update(['type' => 'individual']);

        \Illuminate\Support\Facades\DB::table('customers')
            ->where('type', 'company')
            ->update(['type' => 'business']);

        \Illuminate\Support\Facades\DB::table('quotations')
            ->where('target_customer_type', 'consumer')
            ->update(['target_customer_type' => 'individual']);

        \Illuminate\Support\Facades\DB::table('quotations')
            ->where('target_customer_type', 'company')
            ->update(['target_customer_type' => 'business']);
    }
};

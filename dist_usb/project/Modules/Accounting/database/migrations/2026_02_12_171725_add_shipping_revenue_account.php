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
        $revenueParent = \Modules\Accounting\Models\Account::where('code', '4000')->first();

        \Modules\Accounting\Models\Account::firstOrCreate(
            ['code' => '4103'],
            [
                'name' => 'Shipping Revenue',
                'name_ar' => 'إيرادات شحن وتوصيل',
                'type' => \Modules\Accounting\Enums\AccountType::REVENUE,
                'parent_id' => $revenueParent?->id,
                'is_active' => true,
                'is_header' => false,
                'is_system' => true,
            ]
        );
    }

    public function down(): void
    {
        \Modules\Accounting\Models\Account::where('code', '4103')->delete();
    }
};

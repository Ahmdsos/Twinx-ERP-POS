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
        $liabilityParent = \Modules\Accounting\Models\Account::where('code', '2000')->first();

        \Modules\Accounting\Models\Account::firstOrCreate(
            ['code' => '2120'],
            [
                'name' => 'Delivery Fees Payable',
                'name_ar' => 'أمانات توصيل / مستحقات طيارين',
                'type' => \Modules\Accounting\Enums\AccountType::LIABILITY,
                'parent_id' => $liabilityParent?->id,
                'is_active' => true,
                'is_header' => false,
                'is_system' => true,
            ]
        );
    }

    public function down(): void
    {
        \Modules\Accounting\Models\Account::where('code', '2120')->delete();
    }
};

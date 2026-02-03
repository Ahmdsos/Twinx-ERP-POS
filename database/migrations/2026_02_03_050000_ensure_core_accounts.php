<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;

return new class extends Migration {
    public function up(): void
    {
        // 4301: Sales Discounts (Revenue Contra - Credit Normal, but we debit it)
        Account::firstOrCreate(
            ['code' => '4301'],
            [
                'name' => 'Sales Discounts',
                'type' => AccountType::REVENUE, // It's a contra-revenue
                'is_postable' => true,
                'is_active' => true,
                'description' => 'Account for tracking sales discounts granted to customers'
            ]
        );

        // 1202: Pending Delivery Settlement (Current Asset)
        Account::firstOrCreate(
            ['code' => '1202'],
            [
                'name' => 'Pending Delivery Settlement',
                'type' => AccountType::ASSET,
                'is_postable' => true,
                'is_active' => true,
                'description' => 'Temporary account for delivery fees until driver settlement'
            ]
        );
    }

    public function down(): void
    {
        // Keep accounts for data integrity
    }
};

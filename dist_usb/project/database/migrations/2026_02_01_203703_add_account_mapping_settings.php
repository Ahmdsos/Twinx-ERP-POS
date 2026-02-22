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
        $settings = [
            ['key' => 'acc_ar', 'value' => '1201', 'label' => 'Accounts Receivable', 'group' => 'accounting', 'type' => 'string'],
            ['key' => 'acc_ap', 'value' => '2101', 'label' => 'Accounts Payable', 'group' => 'accounting', 'type' => 'string'],
            ['key' => 'acc_inventory', 'value' => '1301', 'label' => 'Merchandise Inventory', 'group' => 'accounting', 'type' => 'string'],
            ['key' => 'acc_sales_revenue', 'value' => '4101', 'label' => 'Product Sales Revenue', 'group' => 'accounting', 'type' => 'string'],
            ['key' => 'acc_cogs', 'value' => '5101', 'label' => 'Cost of Goods Sold', 'group' => 'accounting', 'type' => 'string'],
            ['key' => 'acc_tax_payable', 'value' => '2201', 'label' => 'VAT Payable', 'group' => 'accounting', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        \App\Models\Setting::whereIn('key', [
            'acc_ar',
            'acc_ap',
            'acc_inventory',
            'acc_sales_revenue',
            'acc_cogs',
            'acc_tax_payable'
        ])->delete();
    }
};

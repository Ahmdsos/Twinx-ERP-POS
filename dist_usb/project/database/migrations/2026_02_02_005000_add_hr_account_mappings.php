<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'acc_salaries_exp',
                'value' => '5211',
                'label' => 'Salaries Expense Account',
                'group' => 'accounting',
                'type' => 'string'
            ],
            [
                'key' => 'acc_salaries_payable',
                'value' => '2400',
                'label' => 'Salaries Payable Account',
                'group' => 'accounting',
                'type' => 'string'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['acc_salaries_exp', 'acc_salaries_payable'])->delete();
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Accounting\Models\Account;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have some base accounts for expenses if not exists
        // This relies on existing accounts. If empty, we might need to create them or skip linking.
        // Assuming standard Chart of Accounts exists or will be seeded.
        
        $categories = [
            ['name' => 'إيجار', 'code' => 'EXP-RENT', 'description' => 'إيجار المقر'],
            ['name' => 'كهرباء ومياه', 'code' => 'EXP-UTIL', 'description' => 'فواتير المرافق'],
            ['name' => 'نثريات ومهمات مكتبية', 'code' => 'EXP-OFFICE', 'description' => 'أدوات مكتبية وضيافة'],
            ['name' => 'رواتب وأجور', 'code' => 'EXP-WAGES', 'description' => 'رواتب الموظفين'],
            ['name' => 'صيانة وتشغيل', 'code' => 'EXP-MAINT', 'description' => 'صيانة المعدات والمقر'],
            ['name' => 'دعاية وإعلان', 'code' => 'EXP-MKT', 'description' => 'حملات تسويقية'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    // We leave account_id null for now if we don't know the IDs. 
                    // The user or a separate seeding process should link them.
                    'account_id' => null, 
                ]
            );
        }
    }
}

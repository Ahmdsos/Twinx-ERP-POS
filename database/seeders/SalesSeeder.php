<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Sales\Models\Customer;

/**
 * SalesSeeder - Creates sample customers for testing
 */
class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Sales module data...');

        // ========================================
        // Create Sample Customers
        // ========================================

        $customers = [
            [
                'name' => 'شركة الأمل التجارية',
                'type' => 'company',
                'email' => 'sales@alamal-trading.com',
                'phone' => '02-2765-4321',
                'mobile' => '0100-987-6543',
                'billing_address' => '10 شارع طلعت حرب، وسط البلد',
                'billing_city' => 'القاهرة',
                'billing_country' => 'Egypt',
                'tax_number' => '321-654-987',
                'payment_terms' => 30,
                'credit_limit' => 50000,
                'contact_person' => 'محمد أحمد',
            ],
            [
                'name' => 'مؤسسة النجاح للتوزيع',
                'type' => 'company',
                'email' => 'contact@alnajah-dist.eg',
                'phone' => '02-3876-5432',
                'mobile' => '0101-876-5432',
                'billing_address' => '45 شارع الهرم',
                'billing_city' => 'الجيزة',
                'billing_country' => 'Egypt',
                'tax_number' => '432-765-098',
                'payment_terms' => 45,
                'credit_limit' => 75000,
                'contact_person' => 'أحمد سعيد',
            ],
            [
                'name' => 'أحمد محمود - تجارة جملة',
                'type' => 'consumer',
                'email' => 'ahmed.mahmoud@gmail.com',
                'mobile' => '0102-765-4321',
                'billing_address' => '15 سوق العتبة',
                'billing_city' => 'القاهرة',
                'billing_country' => 'Egypt',
                'payment_terms' => 15,
                'credit_limit' => 20000,
            ],
            [
                'name' => 'Golden Star Retail',
                'type' => 'company',
                'email' => 'orders@goldenstar-retail.com',
                'phone' => '+971-4-987-6543',
                'billing_address' => 'Business Bay, Tower 5',
                'billing_city' => 'Dubai',
                'billing_country' => 'UAE',
                'tax_number' => 'TRN-200987654',
                'payment_terms' => 30,
                'credit_limit' => 200000,
                'contact_person' => 'Khalid Hassan',
            ],
            [
                'name' => 'المصرية للاستيراد والتصدير',
                'type' => 'company',
                'email' => 'imports@egyptiantrading.com',
                'phone' => '03-5432-1098',
                'billing_address' => 'ميناء الإسكندرية',
                'billing_city' => 'الإسكندرية',
                'billing_country' => 'Egypt',
                'tax_number' => '543-876-210',
                'payment_terms' => 60,
                'credit_limit' => 150000,
                'contact_person' => 'عمرو فؤاد',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }

        $this->command->info('✓ Created ' . count($customers) . ' sample customers');
    }
}

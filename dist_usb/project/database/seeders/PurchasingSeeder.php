<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Purchasing\Models\Supplier;

/**
 * PurchasingSeeder - Creates sample suppliers for testing
 */
class PurchasingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Purchasing module data...');

        // ========================================
        // Create Sample Suppliers
        // ========================================

        $suppliers = [
            [
                'name' => 'شركة النور للمستلزمات',
                'email' => 'contact@alnoor-supplies.com',
                'phone' => '02-2345-6789',
                'mobile' => '0100-123-4567',
                'address' => '15 شارع الجمهورية، وسط البلد',
                'city' => 'القاهرة',
                'country' => 'Egypt',
                'tax_number' => '123-456-789',
                'payment_terms' => 30,
                'credit_limit' => 100000,
                'contact_person' => 'أحمد محمود',
            ],
            [
                'name' => 'التجارة الحديثة للأدوات',
                'email' => 'info@modern-trade.eg',
                'phone' => '02-3456-7890',
                'mobile' => '0101-234-5678',
                'address' => '25 شارع التحرير',
                'city' => 'الجيزة',
                'country' => 'Egypt',
                'tax_number' => '234-567-890',
                'payment_terms' => 45,
                'credit_limit' => 150000,
                'contact_person' => 'محمد علي',
            ],
            [
                'name' => 'مصنع الإتقان',
                'email' => 'sales@itqan-factory.com',
                'phone' => '03-4567-8901',
                'address' => 'المنطقة الصناعية، برج العرب',
                'city' => 'الإسكندرية',
                'country' => 'Egypt',
                'tax_number' => '345-678-901',
                'payment_terms' => 60,
                'credit_limit' => 250000,
                'contact_person' => 'سمير حسن',
            ],
            [
                'name' => 'Delta Electronics Co.',
                'email' => 'orders@delta-electronics.com',
                'phone' => '+971-4-123-4567',
                'address' => 'Dubai Industrial City',
                'city' => 'Dubai',
                'country' => 'UAE',
                'tax_number' => 'TRN-100234567',
                'payment_terms' => 30,
                'credit_limit' => 500000,
                'contact_person' => 'Ahmed Hassan',
            ],
            [
                'name' => 'Global Tech Supplies',
                'email' => 'procurement@globaltech.com',
                'phone' => '+86-21-5555-1234',
                'address' => 'Pudong New Area',
                'city' => 'Shanghai',
                'country' => 'China',
                'tax_number' => 'CHN-8801234567',
                'payment_terms' => 90,
                'credit_limit' => 1000000,
                'contact_person' => 'Li Wei',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(
                ['email' => $supplierData['email']],
                $supplierData
            );
        }

        $this->command->info('✓ Created ' . count($suppliers) . ' sample suppliers');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Enums\ProductType;

/**
 * InventorySeeder - Creates sample inventory data
 * 
 * Run with: php artisan db:seed --class=InventorySeeder
 */
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Create Units
        $this->createUnits();

        // Create Categories
        $this->createCategories();

        // Create Warehouses
        $this->createWarehouses();

        // Create Sample Products
        $this->createProducts();

        $this->command->info('Inventory data seeded successfully!');
    }

    protected function createUnits(): void
    {
        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pcs', 'is_base' => true],
            ['name' => 'Kilogram', 'abbreviation' => 'kg', 'is_base' => true],
            ['name' => 'Gram', 'abbreviation' => 'g', 'is_base' => false, 'base_unit_abbr' => 'kg', 'factor' => 0.001],
            ['name' => 'Liter', 'abbreviation' => 'L', 'is_base' => true],
            ['name' => 'Milliliter', 'abbreviation' => 'mL', 'is_base' => false, 'base_unit_abbr' => 'L', 'factor' => 0.001],
            ['name' => 'Meter', 'abbreviation' => 'm', 'is_base' => true],
            ['name' => 'Box', 'abbreviation' => 'box', 'is_base' => true],
            ['name' => 'Pack', 'abbreviation' => 'pack', 'is_base' => true],
            ['name' => 'Dozen', 'abbreviation' => 'dz', 'is_base' => false, 'base_unit_abbr' => 'pcs', 'factor' => 12],
            ['name' => 'Carton', 'abbreviation' => 'ctn', 'is_base' => true],
        ];

        foreach ($units as $unitData) {
            $baseUnitId = null;
            $factor = 1;

            if (isset($unitData['base_unit_abbr'])) {
                $baseUnit = Unit::where('abbreviation', $unitData['base_unit_abbr'])->first();
                $baseUnitId = $baseUnit?->id;
                $factor = $unitData['factor'] ?? 1;
            }

            Unit::firstOrCreate(
                ['abbreviation' => $unitData['abbreviation']],
                [
                    'name' => $unitData['name'],
                    'is_base' => $unitData['is_base'],
                    'base_unit_id' => $baseUnitId,
                    'conversion_factor' => $factor,
                ]
            );
        }
    }

    protected function createCategories(): void
    {
        // Root categories
        $electronics = Category::firstOrCreate(['name' => 'Electronics'], ['sort_order' => 1]);
        $office = Category::firstOrCreate(['name' => 'Office Supplies'], ['sort_order' => 2]);
        $consumables = Category::firstOrCreate(['name' => 'Consumables'], ['sort_order' => 3]);

        // Electronics subcategories
        Category::firstOrCreate(['name' => 'Computers'], ['parent_id' => $electronics->id, 'sort_order' => 1]);
        Category::firstOrCreate(['name' => 'Phones'], ['parent_id' => $electronics->id, 'sort_order' => 2]);
        Category::firstOrCreate(['name' => 'Accessories'], ['parent_id' => $electronics->id, 'sort_order' => 3]);

        // Office subcategories
        Category::firstOrCreate(['name' => 'Paper Products'], ['parent_id' => $office->id, 'sort_order' => 1]);
        Category::firstOrCreate(['name' => 'Writing Tools'], ['parent_id' => $office->id, 'sort_order' => 2]);
        Category::firstOrCreate(['name' => 'Furniture'], ['parent_id' => $office->id, 'sort_order' => 3]);
    }

    protected function createWarehouses(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            ['name' => 'Main Warehouse', 'address' => 'Industrial Zone, Cairo', 'phone' => '+201000000001', 'is_default' => true, 'is_active' => true]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-ALEX'],
            ['name' => 'Alexandria Branch', 'address' => 'Free Zone, Alexandria', 'phone' => '+201000000002', 'is_default' => false, 'is_active' => true]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-RETAIL'],
            ['name' => 'Retail Store', 'address' => 'Downtown Cairo', 'phone' => '+201000000003', 'is_default' => false, 'is_active' => true]
        );
    }

    protected function createProducts(): void
    {
        $pcs = Unit::where('abbreviation', 'pcs')->first();
        $box = Unit::where('abbreviation', 'box')->first();
        $pack = Unit::where('abbreviation', 'pack')->first();

        $computers = Category::where('name', 'Computers')->first();
        $phones = Category::where('name', 'Phones')->first();
        $accessories = Category::where('name', 'Accessories')->first();
        $paper = Category::where('name', 'Paper Products')->first();

        // Default Accounting
        $inventoryAcc = \Modules\Accounting\Models\Account::where('code', '1301')->first();
        $salesAcc = \Modules\Accounting\Models\Account::where('code', '4101')->first();
        // Link Purchase to Accounts Payable (Liability) not COGS (Expense)
        $apAcc = \Modules\Accounting\Models\Account::where('code', '2101')->first(); // Accounts Payable

        $defaultAccounts = [
            'inventory_account_id' => $inventoryAcc?->id,
            'sales_account_id' => $salesAcc?->id,
            'purchase_account_id' => $apAcc?->id, // CREDIT AP on Purchase (Dr Inventory / Cr AP)
        ];

        $products = [
            [
                'sku' => 'LAPTOP-001',
                'barcode' => '6281000000001',
                'name' => 'Dell Laptop Core i7',
                'type' => ProductType::GOODS,
                'category_id' => $computers?->id,
                'unit_id' => $pcs->id,
                'cost_price' => 25000,
                'selling_price' => 32000,
                'tax_rate' => 14,
                'reorder_level' => 5,
            ],
            [
                'sku' => 'LAPTOP-002',
                'barcode' => '6281000000002',
                'name' => 'HP Laptop Core i5',
                'type' => ProductType::GOODS,
                'category_id' => $computers?->id,
                'unit_id' => $pcs->id,
                'cost_price' => 18000,
                'selling_price' => 24000,
                'tax_rate' => 14,
                'reorder_level' => 5,
            ],
            [
                'sku' => 'PHONE-001',
                'barcode' => '6281000000003',
                'name' => 'iPhone 15 Pro',
                'type' => ProductType::GOODS,
                'category_id' => $phones?->id,
                'unit_id' => $pcs->id,
                'cost_price' => 45000,
                'selling_price' => 55000,
                'tax_rate' => 14,
                'reorder_level' => 10,
            ],
            [
                'sku' => 'PHONE-002',
                'barcode' => '6281000000004',
                'name' => 'Samsung S24 Ultra',
                'type' => ProductType::GOODS,
                'category_id' => $phones?->id,
                'unit_id' => $pcs->id,
                'cost_price' => 42000,
                'selling_price' => 52000,
                'tax_rate' => 14,
                'reorder_level' => 10,
            ],
            [
                'sku' => 'ACC-001',
                'barcode' => '6281000000005',
                'name' => 'USB-C Cable',
                'type' => ProductType::GOODS,
                'category_id' => $accessories?->id,
                'unit_id' => $pcs->id,
                'cost_price' => 50,
                'selling_price' => 100,
                'tax_rate' => 14,
                'reorder_level' => 50,
            ],
            [
                'sku' => 'PAPER-001',
                'barcode' => '6281000000006',
                'name' => 'A4 Copy Paper (500 sheets)',
                'type' => ProductType::GOODS,
                'category_id' => $paper?->id,
                'unit_id' => $pack->id,
                'cost_price' => 120,
                'selling_price' => 180,
                'tax_rate' => 14,
                'reorder_level' => 100,
            ],
            [
                'sku' => 'SRV-001',
                'name' => 'IT Support - Hourly',
                'type' => ProductType::SERVICE,
                'unit_id' => $pcs->id,
                'cost_price' => 0,
                'selling_price' => 500,
                'tax_rate' => 14,
            ],
        ];

        foreach ($products as $productData) {
            $sku = $productData['sku'];
            Product::firstOrCreate(
                ['sku' => $sku],
                array_merge($productData, $defaultAccounts)
            );
        }
    }
}

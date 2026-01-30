<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Order matters:
     * 1. Roles & Permissions (for user assignment)
     * 2. Chart of Accounts (for accounting records)
     * 3. Inventory (categories, units, warehouses, products)
     * 4. Sales (customers)
     * 5. Purchasing (suppliers)
     */
    public function run(): void
    {
        // Call all essential seeders in correct order
        $this->call([
            RolesAndPermissionsSeeder::class,  // Roles first
            ChartOfAccountsSeeder::class,       // Accounts for payments
            InventorySeeder::class,             // Products, categories, etc.
            SalesSeeder::class,                 // Customers
            PurchasingSeeder::class,            // Suppliers
        ]);

        // Create default admin user (use firstOrCreate to avoid duplicate)
        $admin = User::firstOrCreate(
            ['email' => 'admin@twinx.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role if available and not already assigned
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            if ($adminRole && !$admin->hasRole('admin')) {
                $admin->assignRole($adminRole);
            }
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('   - Admin user: admin@twinx.local / password');
    }
}

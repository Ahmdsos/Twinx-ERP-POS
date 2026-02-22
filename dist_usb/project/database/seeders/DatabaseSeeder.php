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
            SettingsSeeder::class,              // Default settings (inc. accounting integration)
            InventorySeeder::class,             // Products, categories, etc.
            SalesSeeder::class,                 // Customers
            PurchasingSeeder::class,            // Suppliers
        ]);

        // Create or update default admin user (upsert — safe to re-run)
        $admin = User::updateOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@12345'),
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

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║   ✅ Database seeded successfully!       ║');
        $this->command->info('║                                          ║');
        $this->command->info('║   Admin Login:                           ║');
        $this->command->info('║   Email:    admin@local.test             ║');
        $this->command->info('║   Password: Admin@12345                  ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->info('');
    }
}

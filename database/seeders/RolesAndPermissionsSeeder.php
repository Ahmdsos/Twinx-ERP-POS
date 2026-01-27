<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RolesAndPermissionsSeeder - Creates default roles and permissions
 * 
 * This seeder creates the initial RBAC structure for Twinx ERP.
 * Run with: php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by module
        $permissions = [
            // Users module
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Accounting module
            'accounts.view',
            'accounts.create',
            'accounts.edit',
            'accounts.delete',
            'journals.view',
            'journals.create',
            'journals.post',
            'journals.reverse',

            // Products module
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Inventory module
            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'warehouses.manage',

            // Sales module
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'sales_orders.view',
            'sales_orders.create',
            'sales_orders.edit',
            'sales_orders.approve',
            'sales_invoices.view',
            'sales_invoices.create',
            'sales_invoices.void',
            'payments.receive',

            // Purchases module
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.approve',
            'grn.create',
            'purchase_invoices.view',
            'purchase_invoices.create',
            'payments.make',

            // Customers & Suppliers
            'customers.view',
            'customers.create',
            'customers.edit',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',

            // Delivery module
            'delivery.view',
            'delivery.create',
            'delivery.update_status',

            // Reports
            'reports.financial',
            'reports.inventory',
            'reports.sales',
            'reports.purchases',

            // System
            'settings.view',
            'settings.edit',
            'audit_logs.view',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createSuperAdmin($permissions);
        $this->createAdmin();
        $this->createAccountant();
        $this->createSales();
        $this->createPurchasing();
        $this->createWarehouse();
        $this->createDelivery();

        // Create default super admin user
        $this->createDefaultUser();
    }

    private function createSuperAdmin(array $allPermissions): void
    {
        $role = Role::create(['name' => 'super_admin']);
        $role->givePermissionTo($allPermissions);
    }

    private function createAdmin(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'accounts.view',
            'accounts.create',
            'accounts.edit',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'inventory.view',
            'warehouses.manage',
            'customers.view',
            'customers.create',
            'customers.edit',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'reports.financial',
            'reports.inventory',
            'reports.sales',
            'reports.purchases',
            'settings.view',
        ]);
    }

    private function createAccountant(): void
    {
        $role = Role::create(['name' => 'accountant']);
        $role->givePermissionTo([
            'accounts.view',
            'accounts.create',
            'accounts.edit',
            'journals.view',
            'journals.create',
            'journals.post',
            'journals.reverse',
            'sales_invoices.view',
            'purchase_invoices.view',
            'payments.receive',
            'payments.make',
            'customers.view',
            'suppliers.view',
            'reports.financial',
        ]);
    }

    private function createSales(): void
    {
        $role = Role::create(['name' => 'sales']);
        $role->givePermissionTo([
            'products.view',
            'inventory.view',
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'sales_orders.view',
            'sales_orders.create',
            'sales_orders.edit',
            'sales_invoices.view',
            'sales_invoices.create',
            'customers.view',
            'customers.create',
            'customers.edit',
            'delivery.view',
            'reports.sales',
        ]);
    }

    private function createPurchasing(): void
    {
        $role = Role::create(['name' => 'purchasing']);
        $role->givePermissionTo([
            'products.view',
            'inventory.view',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'grn.create',
            'purchase_invoices.view',
            'purchase_invoices.create',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'reports.purchases',
        ]);
    }

    private function createWarehouse(): void
    {
        $role = Role::create(['name' => 'warehouse']);
        $role->givePermissionTo([
            'products.view',
            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'grn.create',
            'delivery.view',
            'delivery.create',
            'reports.inventory',
        ]);
    }

    private function createDelivery(): void
    {
        $role = Role::create(['name' => 'delivery']);
        $role->givePermissionTo([
            'delivery.view',
            'delivery.update_status',
        ]);
    }

    private function createDefaultUser(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@twinx.local',
            'password' => Hash::make('password'),
            'phone' => '+201000000000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super_admin');
    }
}

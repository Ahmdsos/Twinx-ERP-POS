<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'dashboard.view',
            'inventory.view',
            'inventory.manage',
            'purchases.view',
            'purchases.manage', // Suppliers included
            'sales.view',
            'sales.create',
            'sales.manage', // POS included in create
            'finance.view',
            'finance.manage',
            'reports.view',
            'settings.manage',
            'users.manage',
            'hr.manage',
            'couriers.manage',

            // HR & Payroll
            'hr.view',
            'hr.employees.view',
            'hr.employees.manage',
            'hr.attendance.view',
            'hr.attendance.manage',
            'hr.payroll.view',
            'hr.payroll.manage',
            'hr.leave.view',
            'hr.leave.manage',
            'hr.advances.view',
            'hr.advances.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions

        // A. Admin (Super Admin) - Has everything
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // B. Manager - Can do everything except Settings & Users
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'dashboard.view',
            'inventory.view',
            'inventory.manage',
            'purchases.view',
            'purchases.manage',
            'sales.view',
            'sales.create',
            'sales.manage',
            'finance.view',
            'finance.manage',
            'reports.view',
        ]);

        // C. Cashier - Only Sales & Dashboard View (Limited)
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'dashboard.view',
            'sales.view',
            'sales.create',
            'inventory.view', // Needs to search products
        ]);

        // D. Warehouse Keeper - Only Inventory
        $warehouse = Role::firstOrCreate(['name' => 'warehouse_keeper']);
        $warehouse->givePermissionTo([
            'dashboard.view',
            'inventory.view',
            'inventory.manage',
        ]);
    }
}

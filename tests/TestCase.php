<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user with specific permissions for testing.
     *
     * @param array<string> $permissions List of permission names to assign
     */
    protected function createUserWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * Create a super-admin user with all module permissions.
     */
    protected function createAdminUser(): User
    {
        return $this->createUserWithPermissions([
            'sales.manage',
            'sales.create',
            'purchases.manage',
            'inventory.manage',
            'finance.manage',
            'reports.view',
            'settings.manage',
            'hr.view',
            'hr.employees.view',
            'hr.payroll.view',
            'hr.leave.view',
            'couriers.manage',
        ]);
    }
}

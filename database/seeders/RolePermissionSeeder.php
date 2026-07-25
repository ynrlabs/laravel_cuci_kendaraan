<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultRoles = Role::getDefaultRoles();
        $defaultPermissions = Role::getDefaultPermissions();
        $defaultRolePermissions = Role::getDefaultRolePermissions();

        // Create permissions
        foreach ($defaultPermissions as $permKey => $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name'], 'guard_name' => $permData['guard_name']],
                $permData
            );
        }

        // Create roles and assign permissions
        foreach ($defaultRoles as $roleKey => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );

            if (isset($defaultRolePermissions[$roleKey])) {
                $permissions = Permission::whereIn('name', $defaultRolePermissions[$roleKey])->get();
                $role->syncPermissions($permissions);
            }
        }

        $this->command->info('Default roles and permissions seeded successfully!');
    }
}
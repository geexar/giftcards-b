<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Admins
            ['name' => 'show admins', 'guard_name' => 'admin', 'group' => 'admins'],
            ['name' => 'create admin', 'guard_name' => 'admin', 'group' => 'admins'],
            ['name' => 'update admin', 'guard_name' => 'admin', 'group' => 'admins'],
            ['name' => 'delete admin', 'guard_name' => 'admin', 'group' => 'admins'],

            // Roles
            ['name' => 'show roles', 'guard_name' => 'admin', 'group' => 'roles'],
            ['name' => 'create role', 'guard_name' => 'admin', 'group' => 'roles'],
            ['name' => 'update role', 'guard_name' => 'admin', 'group' => 'roles'],
            ['name' => 'delete role', 'guard_name' => 'admin', 'group' => 'roles'],

            // Users
            ['name' => 'show users', 'guard_name' => 'admin', 'group' => 'users'],
            ['name' => 'create user', 'guard_name' => 'admin', 'group' => 'users'],
            ['name' => 'update user', 'guard_name' => 'admin', 'group' => 'users'],
            ['name' => 'delete user', 'guard_name' => 'admin', 'group' => 'users'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'group' => $permission['group'],
                'guard_name' => 'admin',
            ]);
        }

        // Create or get roles
        $superAdmin = Role::firstOrCreate(
            ['guard_name' => 'admin', 'name' => "super admin"]
        );

        $reviewer = Role::firstOrCreate(
            ['guard_name' => 'admin', 'name' => "reviewer"]
        );

        // Assign permissions to roles
        $allPermissions = Permission::all();
        $reviewerPermissions = Permission::where('name', 'NOT LIKE', '%admin%')
            ->where('name', 'NOT LIKE', '%role%')
            ->get();

        $superAdmin->syncPermissions($allPermissions);
        $reviewer->syncPermissions($reviewerPermissions);

        // Give all super admin users the same permissions directly
        $superAdmins = Admin::where('role_id', 1)->get();

        foreach ($superAdmins as $admin) {
            $admin->givePermissionTo($allPermissions);
        }
    }
}

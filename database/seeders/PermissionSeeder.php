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
            'admins' => ['show admins', 'view admin', 'create admin', 'update admin', 'delete admin'],
            'roles'  => ['show roles', 'create role', 'update role', 'delete role'],
            'users'  => ['show users', 'view user', 'create user', 'update user', 'delete user'],
            'products'  => ['show products', 'view product', 'create product', 'update product', 'delete product', 'sync products', 'show sync logs'],
            'orders'   => ['show orders', 'view order', 'update order'],
            'categories'   => ['show categories', 'create category', 'update category', 'delete category'],
            'product status manager'   => ['show product status manager', 'update product status'],
            'banners' => ['show banners', 'create banner', 'update banner', 'delete banner'],
            'faqs'   => ['show faqs', 'create faq', 'update faq', 'delete faq'],
            'articles'   => ['show articles', 'create article', 'update article', 'delete article'],
            'static pages'   => ['show static pages', 'update static page'],
            'countries'   => ['show countries', 'update country'],
            'contact messages'   => ['show contact messages', 'view contact message'],
            'activity logs'   => ['show activity logs'],
            'settings'   => ['update settings'],
            'payment methods'   => ['update payment methods'],
            'transactions'   => ['show transactions'],
            'usdt addresses'   => ['show usdt addresses'],
            'refunds'   => ['show refunds', 'view refund', 'update refund'],
            'products inventory'   => ['show products inventory', 'update product stock'],
            'grouped notifications' => ['show grouped notifications', 'create grouped notification'],
        ];

        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'group' => $group,
                    'guard_name' => 'admin'
                ]);
            }
        }

        // Create or get roles
        $superAdmin = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'admin']);
        $reviewer = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'admin']);

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

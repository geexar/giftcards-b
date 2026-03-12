<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as ModelsRole;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::create([
            'name' => 'Admin 1',
            'email' => 'admin1@npd.com',
            'country_code' => '+966',
            'phone' => '555555555',
            'password' => Hash::make('Npd5000!'),
            'role_id' => 1
        ]);

        $superAdmin = ModelsRole::first();

        $permissions = $superAdmin->permissions;

        $admin->givePermissionTo($permissions);
    }
}

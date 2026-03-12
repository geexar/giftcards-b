<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'uuid' => Str::uuid()->toString(),
                'name' => "user $i",
                'email' => "user$i@npd.com",
                'country_code' => '+20',
                'phone' => "101122334$i",
                'password' => Hash::make('Npd5000!'),
            ]);
        }
    }
}

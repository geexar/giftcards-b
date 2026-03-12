<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            CountrySeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            StaticPageSeeder::class,
            FaqSeeder::class,
            ArticleSeeder::class,
            ContactMessageSeeder::class,
            PaymentMethodSeeder::class,
            IntegrationSeeder::class,
            CategorySeeder::class,
            UsdtNetworkSeeder::class,
        ]);
    }
}

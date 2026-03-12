<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $integrations = [
            [
                'name' => 'gift cards',
                'code' => 'lirat_gift_cards',
                'config' => [
                    'base_price_source' => 'product_price',
                ]
            ],
        ];

        foreach ($integrations as $integration) {
            // first or create
            Integration::firstOrCreate(
                ['code' => $integration['code']],
                [
                    'name' => $integration['name'],
                    'config' => $integration['config'],
                ]
            );
        }
    }
}

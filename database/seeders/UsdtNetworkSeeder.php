<?php

namespace Database\Seeders;

use App\Models\UsdtNetwork;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsdtNetworkSeeder extends Seeder
{
    public function run(): void
    {
        $networks = [
            ['name' => 'Arbitrum One',    'identifier' => 'ARETH',    'fixed_fees' => 1, 'percentage_fees' => 0.4],
            ['name' => 'Ethereum',        'identifier' => 'ETH',      'fixed_fees' => 2, 'percentage_fees' => 0.4],
            ['name' => 'Optimism',        'identifier' => 'OPTIMISM', 'fixed_fees' => 3, 'percentage_fees' => 0.4],
            ['name' => 'Polygon',         'identifier' => 'POLYGON',  'fixed_fees' => 1, 'percentage_fees' => 0.4],
            ['name' => 'Ton Blockchain',  'identifier' => 'TON',      'fixed_fees' => 2, 'percentage_fees' => 0.4],
            ['name' => 'Tron Blockchain', 'identifier' => 'TRX',      'fixed_fees' => 3, 'percentage_fees' => 0.4],
        ];

        foreach ($networks as $network) {
            UsdtNetwork::create($network);
        }
    }
}

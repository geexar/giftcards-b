<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'id' => 1,
                'name' => 'Wallet',
                'code' => 'wallet',
                'is_active' => true,
                'active_for_checkout' => true,
                'active_for_top_up' => false,
            ],
            [
                'id' => 2,
                'name' => 'Stripe',
                'code' => 'stripe',
                'is_active' => true,
                'active_for_checkout' => true,
                'active_for_top_up' => true,
            ],
            [
                'id' => 3,
                'name' => 'USDT',
                'code' => 'usdt_ccpayment',
                'is_active' => false,
                'active_for_checkout' => false,
                'active_for_top_up' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            // first or create
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'is_active' => $method['is_active'],
                    'active_for_checkout' => $method['active_for_checkout'],
                    'active_for_top_up' => $method['active_for_top_up'],
                ]
            );
        }
    }
}

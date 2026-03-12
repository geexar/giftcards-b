<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Order Limits
            ['group' => 'order_limits', 'key' => 'max_units_per_order', 'value' => '100'],

            // Inventory
            ['group' => 'inventory', 'key' => 'stock_threshold', 'value' => '10'],

            // Markup Fees
            ['group' => 'markup_fees', 'key' => 'markup_fees_type', 'value' => 'percentage'],
            ['group' => 'markup_fees', 'key' => 'markup_fees', 'value' => '5'],


            // Contact Support
            ['group' => 'contact_support', 'key' => 'email', 'value' => 'info@npd.com'],
            ['group' => 'contact_support', 'key' => 'whatsapp', 'value' => '+123456789'],
            ['group' => 'contact_support', 'key' => 'telegram', 'value' => '@npd_support'],

            // Social Media
            ['group' => 'social_media', 'key' => 'facebook', 'value' => 'https://facebook.com/'],
            ['group' => 'social_media', 'key' => 'x', 'value' => 'https://x.com/'],
            ['group' => 'social_media', 'key' => 'tiktok', 'value' => 'https://tiktok.com/'],
            ['group' => 'social_media', 'key' => 'youtube', 'value' => 'https://youtube.com/'],
            ['group' => 'social_media', 'key' => 'snapchat', 'value' => 'https://snapchat.com/'],
            ['group' => 'social_media', 'key' => 'linkedin', 'value' => 'https://linkedin.com/'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate([
                'group' => $setting['group'],
                'key' => $setting['key'],
            ], [
                'value' => $setting['value'],
            ]);
        }
    }
}

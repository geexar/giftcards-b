<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy_policy',
                'body' => [
                    'en' => '<h1>Privacy Policy</h1><p>This is the privacy policy content in English...</p>',
                    'ar' => '<h1>سياسة الخصوصية</h1><p>هذا هو محتوى سياسة الخصوصية باللغة العربية...</p>'
                ]
            ],
            [
                'slug' => 'refund_policy',
                'body' => [
                    'en' => '<h1>Refund Policy</h1><p>This is the refund policy content in English...</p>',
                    'ar' => '<h1>سياسة الاسترداد</h1><p>هذا هو محتوى سياسة الاسترداد باللغة العربية...</p>'
                ]
            ],
            [
                'slug' => 'terms_conditions',
                'body' => [
                    'en' => '<h1>Terms and Conditions</h1><p>These are the terms and conditions in English...</p>',
                    'ar' => '<h1>الشروط والأحكام</h1><p>هذه هي الشروط والأحكام باللغة العربية...</p>'
                ]
            ],
            [
                'slug' => 'about_us',
                'body' => [
                    'en' => '<h1>About Us</h1><p>This is the about us content in English...</p>',
                    'ar' => '<h1>معلومات عنا</h1><p>هذا هو محتوى معلومات عنا باللغة العربية...</p>'
                ]
            ]
        ];

        foreach ($pages as $page) {
            StaticPage::create([
                'slug' => $page['slug'],
                'body' => $page['body']
            ]);
        }
    }
}

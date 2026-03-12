<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the available image paths
        $imagePaths = collect([
            public_path('assets/images/img-1.jpg'),
            public_path('assets/images/img-2.jpg'),
            public_path('assets/images/img-3.jpg'),
            public_path('assets/images/img-4.jpg'),
            public_path('assets/images/img-5.jpg'),
        ])->filter(function ($path) {
            return file_exists($path); // Ensure only existing files are used
        })->values();

        if ($imagePaths->isEmpty()) {
            throw new \Exception('No images found in public/assets/images/');
        }

        $articles = [
            [
                'title' => [
                    'en' => 'Introduction to Our Platform',
                    'ar' => 'مقدمة عن منصتنا'
                ],
                'body' => [
                    'en' => 'This article introduces the core features of our platform and how it benefits users...',
                    'ar' => 'تتناول هذه المقالة الميزات الأساسية لمنصتنا وكيفية استفادة المستخدمين منها...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Top 5 Tips for Getting Started',
                    'ar' => 'أفضل 5 نصائح للبدء'
                ],
                'body' => [
                    'en' => 'Learn the best practices for new users to make the most of our services...',
                    'ar' => 'تعرف على أفضل الممارسات للمستخدمين الجدد للاستفادة القصوى من خدماتنا...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Understanding Our Technology',
                    'ar' => 'فهم تقنيتنا'
                ],
                'body' => [
                    'en' => 'A deep dive into the technology powering our platform...',
                    'ar' => 'نظرة عميقة على التكنولوجيا التي تدعم منصتنا...'
                ],
            ],
            [
                'title' => [
                    'en' => 'How to Maximize Your Account',
                    'ar' => 'كيفية تحقيق أقصى استفادة من حسابك'
                ],
                'body' => [
                    'en' => 'Tips and tricks to optimize your account settings for a better experience...',
                    'ar' => 'نصائح وحيل لتحسين إعدادات حسابك لتجربة أفضل...'
                ],
            ],
            [
                'title' => [
                    'en' => 'The Future of Our Industry',
                    'ar' => 'مستقبل صناعتنا'
                ],
                'body' => [
                    'en' => 'Exploring trends and predictions for the future of our industry...',
                    'ar' => 'استكشاف الاتجاهات والتوقعات لمستقبل صناعتنا...'
                ],
            ],
            [
                'title' => [
                    'en' => 'User Success Stories',
                    'ar' => 'قصص نجاح المستخدمين'
                ],
                'body' => [
                    'en' => 'Real stories from users who achieved great results with our platform...',
                    'ar' => 'قصص حقيقية من المستخدمين الذين حققوا نتائج رائعة مع منصتنا...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Troubleshooting Common Issues',
                    'ar' => 'حل المشكلات الشائعة'
                ],
                'body' => [
                    'en' => 'A guide to resolving common problems you might encounter...',
                    'ar' => 'دليل لحل المشكلات الشائعة التي قد تواجهها...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Comparing Our Features',
                    'ar' => 'مقارنة ميزاتنا'
                ],
                'body' => [
                    'en' => 'A detailed comparison of our features with other platforms...',
                    'ar' => 'مقارنة تفصيلية لميزاتنا مع منصات أخرى...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Advanced Usage Techniques',
                    'ar' => 'تقنيات الاستخدام المتقدمة'
                ],
                'body' => [
                    'en' => 'Learn advanced techniques to get even more from our services...',
                    'ar' => 'تعلم تقنيات متقدمة للاستفادة أكثر من خدماتنا...'
                ],
            ],
            [
                'title' => [
                    'en' => 'Community Engagement',
                    'ar' => 'التفاعل مع المجتمع'
                ],
                'body' => [
                    'en' => 'How to connect with our community and share your experiences...',
                    'ar' => 'كيفية التواصل مع مجتمعنا ومشاركة تجاربك...'
                ],
            ],
        ];

        foreach ($articles as $articleData) {
            // Create the article with translations
            $article = Article::create([
                'title' => $articleData['title'],
                'body' => $articleData['body'],
            ]);

            // Attach a random image from the available images
            $randomImagePath = $imagePaths->random();
            $article->addMedia($randomImagePath)
                ->preservingOriginal()
                ->toMediaCollection();
        }
    }
}
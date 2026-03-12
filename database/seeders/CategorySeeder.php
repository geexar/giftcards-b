<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $promotedCount = 0;
        $maxPromoted = 4; // only 3–4 promoted categories

        // Available images
        $imagePaths = collect([
            public_path('assets/images/img-1.jpg'),
            public_path('assets/images/img-2.jpg'),
            public_path('assets/images/img-3.jpg'),
            public_path('assets/images/img-4.jpg'),
            public_path('assets/images/img-5.jpg'),
        ])->filter(fn($path) => file_exists($path))->values();

        // Main categories with subcategories and sub-subcategories
        $categoriesData = [
            [
                'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
                'short_description' => ['en' => 'All electronic items', 'ar' => 'جميع الإلكترونيات'],
                'description' => ['en' => 'Electronics category', 'ar' => 'فئة الإلكترونيات'],
                'sub' => [
                    [
                        'name' => ['en' => 'Mobile Phones', 'ar' => 'هواتف محمولة'],
                        'sub' => [
                            ['name' => ['en' => 'Smartphones', 'ar' => 'الهواتف الذكية']],
                            ['name' => ['en' => 'Feature Phones', 'ar' => 'الهواتف التقليدية']],
                        ],
                    ],
                    [
                        'name' => ['en' => 'Computers', 'ar' => 'أجهزة الكمبيوتر'],
                        'sub' => [
                            ['name' => ['en' => 'Laptops', 'ar' => 'الحواسيب المحمولة']],
                            ['name' => ['en' => 'Desktops', 'ar' => 'الحواسيب المكتبية']],
                        ],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Fashion', 'ar' => 'موضة'],
                'short_description' => ['en' => 'Latest fashion trends', 'ar' => 'أحدث صيحات الموضة'],
                'description' => ['en' => 'Fashion category', 'ar' => 'فئة الموضة'],
                'sub' => [
                    [
                        'name' => ['en' => 'Men', 'ar' => 'رجال'],
                        'sub' => [
                            ['name' => ['en' => 'Shirts', 'ar' => 'قمصان']],
                            ['name' => ['en' => 'Jeans', 'ar' => 'جينز']],
                        ],
                    ],
                    [
                        'name' => ['en' => 'Women', 'ar' => 'نساء'],
                        'sub' => [
                            ['name' => ['en' => 'Dresses', 'ar' => 'فساتين']],
                            ['name' => ['en' => 'Handbags', 'ar' => 'حقائب يد']],
                        ],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Home & Garden', 'ar' => 'المنزل والحديقة'],
                'short_description' => ['en' => 'Home essentials', 'ar' => 'منتجات المنزل الأساسية'],
                'description' => ['en' => 'Home & Garden category', 'ar' => 'فئة المنزل والحديقة'],
                'sub' => [
                    [
                        'name' => ['en' => 'Furniture', 'ar' => 'أثاث'],
                        'sub' => [
                            ['name' => ['en' => 'Living Room', 'ar' => 'غرفة المعيشة']],
                            ['name' => ['en' => 'Bedroom', 'ar' => 'غرفة النوم']],
                        ],
                    ],
                    [
                        'name' => ['en' => 'Garden', 'ar' => 'الحديقة'],
                        'sub' => [
                            ['name' => ['en' => 'Plants', 'ar' => 'نباتات']],
                            ['name' => ['en' => 'Garden Tools', 'ar' => 'أدوات الحديقة']],
                        ],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Sports', 'ar' => 'رياضة'],
                'short_description' => ['en' => 'Sports equipment', 'ar' => 'معدات رياضية'],
                'description' => ['en' => 'Sports category', 'ar' => 'فئة الرياضة'],
                'sub' => [
                    [
                        'name' => ['en' => 'Fitness', 'ar' => 'لياقة بدنية'],
                        'sub' => [
                            ['name' => ['en' => 'Treadmills', 'ar' => 'أجهزة الجري']],
                            ['name' => ['en' => 'Dumbbells', 'ar' => 'أوزان حرة']],
                        ],
                    ],
                    [
                        'name' => ['en' => 'Outdoor', 'ar' => 'الخارجية'],
                        'sub' => [
                            ['name' => ['en' => 'Camping Gear', 'ar' => 'معدات التخييم']],
                            ['name' => ['en' => 'Bicycles', 'ar' => 'دراجات']],
                        ],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Books', 'ar' => 'كتب'],
                'short_description' => ['en' => 'All kinds of books', 'ar' => 'جميع أنواع الكتب'],
                'description' => ['en' => 'Books category', 'ar' => 'فئة الكتب'],
                'sub' => [
                    [
                        'name' => ['en' => 'Fiction', 'ar' => 'روايات'],
                        'sub' => [
                            ['name' => ['en' => 'Mystery', 'ar' => 'الغموض']],
                            ['name' => ['en' => 'Romance', 'ar' => 'رومانسية']],
                        ],
                    ],
                    [
                        'name' => ['en' => 'Non-Fiction', 'ar' => 'غير خيالي'],
                        'sub' => [
                            ['name' => ['en' => 'Biography', 'ar' => 'سيرة ذاتية']],
                            ['name' => ['en' => 'Self-Help', 'ar' => 'تطوير الذات']],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categoriesData as $data) {
            // Randomly decide if main category is promoted
            $isPromoted = false;
            if ($promotedCount < $maxPromoted && rand(0, 1)) {
                $isPromoted = true;
                $promotedCount++;
            }

            // Create main category
            $mainCategory = Category::create([
                'source' => 'local',
                'external_id' => null,
                'name' => $data['name'],
                'type' => 'main',
                'parent_id' => null,
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'is_featured' => true,
                'is_promoted' => $isPromoted,
                'is_trending' => false,
            ]);

            // Attach random image
            if ($imagePaths->count() > 0) {
                $mainCategory->addMedia($imagePaths->random())
                    ->preservingOriginal()
                    ->toMediaCollection();
            }

            // Subcategories (level 2)
            foreach ($data['sub'] as $subData) {
                $subCategory = Category::create([
                    'source' => 'local',
                    'external_id' => null,
                    'name' => $subData['name'],
                    'type' => 'sub',
                    'parent_id' => $mainCategory->id,
                    'short_description' => ['en' => 'Subcategory', 'ar' => 'فئة فرعية'],
                    'description' => ['en' => 'Subcategory description', 'ar' => 'وصف الفئة الفرعية'],
                    'is_featured' => false,
                    'is_promoted' => false,
                    'is_trending' => false,
                ]);

                if ($imagePaths->count() > 0) {
                    $subCategory->addMedia($imagePaths->random())
                        ->preservingOriginal()
                        ->toMediaCollection();
                }

                // Sub-subcategories (level 3)
                foreach ($subData['sub'] as $subSubData) {
                    $subSubCategory = Category::create([
                        'source' => 'local',
                        'external_id' => null,
                        'name' => $subSubData['name'],
                        'type' => 'sub_sub',
                        'parent_id' => $subCategory->id,
                        'short_description' => ['en' => 'Sub-subcategory', 'ar' => 'فئة فرعية فرعية'],
                        'description' => ['en' => 'Sub-subcategory description', 'ar' => 'وصف الفئة الفرعية الفرعية'],
                        'is_featured' => false,
                        'is_promoted' => false,
                        'is_trending' => false,
                    ]);

                    if ($imagePaths->count() > 0) {
                        $subSubCategory->addMedia($imagePaths->random())
                            ->preservingOriginal()
                            ->toMediaCollection();
                    }
                }
            }
        }
    }
}

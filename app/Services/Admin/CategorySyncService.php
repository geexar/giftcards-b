<?php

namespace App\Services\Admin;

use App\Enums\CategorySource;
use App\Enums\CategoryType;
use App\Jobs\SyncApiCategory;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CategorySyncService
{
    public function __construct(private CategoryRepository $categoryRepository) {}

    public function gatherCategoryJobs(): array
    {
        $apiCategories = $this->loadApiCategories();
        $jobs = [];

        foreach ($apiCategories as $main) {
            $jobs[] = new SyncApiCategory($main, null, CategoryType::MAIN);

            foreach ($main['childs'] ?? [] as $sub) {
                $jobs[] = new SyncApiCategory($sub, $main['id'], CategoryType::SUB);

                foreach ($sub['childs'] ?? [] as $subSub) {
                    $jobs[] = new SyncApiCategory($subSub, $sub['id'], CategoryType::SUB_SUB);
                }
            }
        }

        return $jobs;
    }

    public function syncCategory(array $data, ?Category $parent, CategoryType $type): Category
    {
        $existing = $this->categoryRepository->getByExternalId($data['id']);

        if (!$existing) {
            return $this->createCategory($data, $parent, $type);
        }

        return $this->updateCategory($existing, $data);
    }

    public function createCategory(array $data, ?Category $parent, CategoryType $type): Category
    {
        $category = $this->categoryRepository->create([
            'source'      => CategorySource::API,
            'external_id' => $data['id'],
            'parent_id'   => $parent?->id,
            'type'        => $type,
            'name'        => $data['categoryName'],
            'is_active'   => true,
        ]);

        $imageUrl = $data['amazonImage'] ?? null;
        if ($imageUrl) {
            $category->addMediaFromUrl($imageUrl)->toMediaCollection();
        }

        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        // English name changed?
        if ($category->getTranslation('name', 'en') !== $data['categoryName']['en']) {
            $category->update(['name' => $data['categoryName']]);
        }

        // Image changed?
        $imageUrl = $data['amazonImage'] ?? null;
        if ($imageUrl && $this->imageChanged($category->image, $imageUrl)) {
            $category->clearMediaCollection();
            $category->addMediaFromUrl($imageUrl)->toMediaCollection();
        }

        return $category;
    }

    public function loadApiCategories(): Collection
    {
        $englishFile = storage_path('app/private/categories_en.json');
        $arabicFile  = storage_path('app/private/categories_ar.json');

        $englishCategories = json_decode(file_get_contents($englishFile), true);
        $arabicCategories  = json_decode(file_get_contents($arabicFile), true);

        foreach ($englishCategories as $mainIndex => $mainCategoryEn) {
            $mainCategoryAr = $arabicCategories[$mainIndex] ?? [];

            $mainCategoryEn['categoryName'] = [
                'en' => $mainCategoryEn['categoryName'],
                'ar' => $mainCategoryAr['categoryName'] ?? $mainCategoryEn['categoryName'],
            ];

            if (!empty($mainCategoryEn['childs'])) {
                foreach ($mainCategoryEn['childs'] as $subIndex => $subCategoryEn) {
                    $subCategoryAr = $mainCategoryAr['childs'][$subIndex] ?? [];

                    $subCategoryEn['categoryName'] = [
                        'en' => $subCategoryEn['categoryName'],
                        'ar' => $subCategoryAr['categoryName'] ?? $subCategoryEn['categoryName'],
                    ];
                    $subCategoryEn['amazonImage'] ??= null;

                    if (!empty($subCategoryEn['childs'])) {
                        foreach ($subCategoryEn['childs'] as $subSubIndex => $subSubCategoryEn) {
                            $subSubCategoryAr = $subCategoryAr['childs'][$subSubIndex] ?? [];

                            $subSubCategoryEn['categoryName'] = [
                                'en' => $subSubCategoryEn['categoryName'],
                                'ar' => $subSubCategoryAr['categoryName'] ?? $subSubCategoryEn['categoryName'],
                            ];
                            $subSubCategoryEn['amazonImage'] ??= null;

                            $subCategoryEn['childs'][$subSubIndex] = $subSubCategoryEn;
                        }
                    }

                    $mainCategoryEn['childs'][$subIndex] = $subCategoryEn;
                }
            }

            $englishCategories[$mainIndex] = $mainCategoryEn;
        }

        return collect($englishCategories);
    }

    private function imageChanged(?Media $currentImage, string $newImageUrl): bool
    {
        return $currentImage?->file_name !== basename($newImageUrl);
    }
}

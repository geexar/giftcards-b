<?php

namespace App\Services\Admin;

use App\Enums\CategorySource;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ActivityLogService $activityLogService,
        private ProductRepository $productRepository
    ) {}

    public function getCategory(string $id)
    {
        $category = $this->categoryRepository->getById($id);

        if (!$category) {
            throw new NotFoundHttpException('category not found');
        }

        return $category;
    }

    public function create(array $data)
    {
        // Check categories tag count limit
        $this->validateTagsCount($data);

        // Set default source and status
        $data['source'] = CategorySource::LOCAL;
        $data['is_active'] = true;

        // Determine category type based on parent
        $data['type'] = $this->getCategoryType($data['parent_id'] ?? null);



        // Wrap creation in a transaction
        DB::transaction(function () use ($data) {
            // Create category record
            $category = $this->categoryRepository->create($data);

            // Attach image if provided
            if (!empty($data['image'])) {
                $category->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($category, 'category.created');
        });
    }

    public function getCategoryType(?string $parentId): CategoryType
    {
        // If no parent, category is MAIN
        if (!$parentId) {
            return CategoryType::MAIN;
        }

        // Fetch parent category
        $parent = $this->categoryRepository->getById($parentId);

        // Determine type based on parent type
        return match ($parent->type) {
            CategoryType::MAIN     => CategoryType::SUB,      // Parent is MAIN → current is SUB
            CategoryType::SUB      => CategoryType::SUB_SUB,  // Parent is SUB → current is SUB_SUB
            CategoryType::SUB_SUB  => throw new BadRequestHttpException("Cannot create a category under a sub sub category."),
        };
    }

    public function validateTagsCount(array $data, ?string $exceptCategoryId = null): void
    {
        // ---- PROMOTED LIMIT (max: 6) ----
        if (!empty($data['is_promoted'])) {
            $promoted = $this->categoryRepository->getPromotedCategories();

            $count = $promoted
                ->filter(fn($c) => $c->id !== $exceptCategoryId)
                ->count();

            if ($count >= 6) {
                throw new BadRequestHttpException(__('Maximum promoted categories is 6.'));
            }
        }

        // ---- FEATURED CATEGORY (only 1 allowed) ----
        if (!empty($data['is_featured'])) {
            $featured = $this->categoryRepository->getFeaturedCategory();

            if ($featured && $featured->id !== $exceptCategoryId) {
                throw new BadRequestHttpException(__('Only one featured category is allowed.'));
            }
        }

        // ---- TRENDING LIMIT (max: 25) ----
        if (!empty($data['is_trending'])) {
            $trendingProductsCount = $this->productRepository->trendingCount();
            $trendingCategoriesCount = $this->categoryRepository->trendingCount($exceptCategoryId);

            if ($trendingCategoriesCount + $trendingProductsCount >= 25) {
                throw new BadRequestHttpException(__('maximum number of trending items: 25'));
            }
        }
    }


    public function update(string $id, array $data)
    {
        // Fetch category to update
        $category = $this->getCategory($id);

        // Check promoted and featured constraints excluding current category
        $this->validateTagsCount($data, $category->id);


        // For API categories, restrict certain fields from being updated
        if ($category->source == CategorySource::API) {
            $data['parent_id'] = $category->parent_id;
        }

        // if no parent id is provided, make it null for main category 
        $data['parent_id'] = $data['parent_id'] ?? null;

        // Handle parent change and validate hierarchy
        $this->validateParent($category, $data['parent_id']);

        // After validation, recalculate type
        $data['type'] = $this->getCategoryType($data['parent_id']);


        // Perform DB update and media attachment inside transaction
        DB::transaction(function () use ($category, $data) {
            $this->categoryRepository->update($category, $data);

            if (isset($data['image'])) {
                $category->clearMediaCollection();
                $category->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($category, 'category.updated');
        });
    }

    private function validateParent($category, ?string $newParentId): void
    {
        // Skip if parent is unchanged
        if ($newParentId == $category->parent_id) {
            return;
        }

        // Prevent level change if category has products
        if ($category->products()->count() > 0) {
            throw new BadRequestHttpException(__("Cannot change category level because it has products."));
        }

        if ($category->childs()->count() > 0) {
            throw new BadRequestHttpException(__("Cannot change category level because it has sub categories."));
        }

        // check category new parent level
        // if ($newParentId) {
        //     $newParent = $this->categoryRepository->getById($newParentId);

        //     // Ensure parent is higher level than the category
        //     $levelOrder = [
        //         CategoryType::MAIN->value => 1,
        //         CategoryType::SUB->value => 2,
        //         CategoryType::SUB_SUB->value => 3,
        //     ];

        //     if ($levelOrder[$newParent->type->value] >= $levelOrder[$category->type->value]) {
        //         throw new BadRequestHttpException(
        //             __("Parent category must be higher level than the category.")
        //         );
        //     }
        // }
    }

    public function delete(string $id): void
    {
        $category = $this->getCategory($id);

        if ($category->source === CategorySource::API) {
            throw new BadRequestHttpException(__("Cannot delete category imported from API."));
        }

        // Load full subtree
        $category->load('childs.childs');

        if ($this->hasProductsInSubtree($category)) {
            throw new BadRequestHttpException(__("Cannot delete category that has products under it."));
        }

        DB::transaction(function () use ($category) {
            $this->deleteCategoryRecursively($category);
        });
    }


    private function deleteCategoryRecursively(Category $category): void
    {
        // Load children
        $category->load('childs');

        foreach ($category->childs as $child) {
            $this->deleteCategoryRecursively($child);
        }

        // Clear media before delete (important)
        $category->clearMediaCollection();

        $category->delete();

        $this->activityLogService->store($category, 'category.deleted');
    }

    private function hasProductsInSubtree(Category $category): bool
    {
        // Level 0: this category
        if ($category->products()->exists()) {
            return true;
        }

        foreach ($category->childs as $child) {

            if ($child->products()->exists()) {
                return true;
            }

            foreach ($child->childs as $grandchild) {
                if ($grandchild->products()->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCategoryTree(?string $type, ?string $productSource = null): array
    {
        $type = $type ?? CategoryType::MAIN->value;

        $categories = $this->categoryRepository->categoryTree($type, $productSource);

        return $categories->map(fn($category) => $this->mapCategoryWithTotalProducts($category, $productSource))
            ->filter() // Remove nulls
            ->values()
            ->toArray();
    }

    /**
     * Recursively map category and sum products from self + all descendants
     */
    private function mapCategoryWithTotalProducts($category, ?string $productSource = null): ?array
    {
        // Get self products count
        $selfProductsCount = $category->products()
            ->when($productSource, fn($q) => $q->where('source', $productSource))
            ->count();

        // Map children recursively
        $childsData = [];
        $childrenProductsCount = 0;

        if ($category->childs && $category->childs->isNotEmpty() && $category->type != CategoryType::SUB_SUB) {
            foreach ($category->childs as $child) {
                $childData = $this->mapCategoryWithTotalProducts($child, $productSource);
                if ($childData !== null) {
                    $childrenProductsCount += $childData['products_count'];
                    $childsData[] = $childData;
                }
            }
        }

        $totalProducts = $selfProductsCount + $childrenProductsCount;

        // FIX: If we are filtering by source and this branch is empty, return null
        if ($productSource && $totalProducts === 0) {
            return null;
        }

        return [
            'id' => $category->id,
            'source' => $category->source->value,
            'name' => $category->name,
            'is_active' => $category->is_active,
            'products_count' => $totalProducts,
            'childs' => $childsData
        ];
    }


    public function getCategoryHierarchy(?Category $category = null): ?array
    {
        if (!$category) {
            return null;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type->value,
            'parent' => $category->parent ? $this->getCategoryHierarchy($category->parent) : null,
        ];
    }

    public function toggleStatus(string $id)
    {
        $category = $this->getCategory($id);

        DB::transaction(function () use ($category) {
            $category->update(['is_active' => !$category->is_active]);
            $this->activityLogService->store($category, 'category.status_updated');
        });
    }

    public function getTotalProductsCountInCategory(string $categoryId, ?string $productSource = null): int
    {
        $category = $this->getCategory($categoryId);

        $categoryIds = $this->getCategoryAndDescendantIds($category);

        return $this->productRepository->countByCategoryIds($categoryIds, $productSource);
    }

    private function getCategoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];

        $category->load('childs.childs');

        foreach ($category->childs as $child) {
            $ids[] = $child->id;

            foreach ($child->childs as $grandchild) {
                $ids[] = $grandchild->id;
            }
        }

        return $ids;
    }
}

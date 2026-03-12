<?php

namespace App\Services\User;

use App\Http\Resources\Admin\CategoryBasicResource;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\ProductBasicResource;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ProductRepository $productRepository
    ) {}

    public function getCategory(string $id): Category
    {
        $category = $this->categoryRepository->getById($id);

        if (!$this->isCategoryPathActive($category)) {
            throw new NotFoundHttpException('Category not found');
        }

        return $category;
    }

    /**
     * Helper method to check the status of a category and all its ancestors.
     */
    public function isCategoryPathActive(?Category $category): bool
    {
        // If the category doesn't exist or is inactive, it's not active.
        if (!$category || !$category->is_active) {
            return false;
        }

        // Recursively check parents
        $parent = $category->parent;
        while ($parent) {
            if (!$parent->is_active) {
                return false;
            }
            $parent = $parent->parent;
        }

        return true;
    }

    public function getFeaturedCategoryItems()
    {
        $category = $this->categoryRepository->getFeaturedCategory();

        if (!$category || !$this->isCategoryPathActive($category)) {
            return null;
        }

        // Logic for empty categories
        if (!$category->childs()->count() && !$category->products()->count()) {
            return null;
        }

        // if category has sub categories
        if ($category->childs()->count()) {
            $subCategories = $this->categoryRepository->getPaginatedSubCategories($category->id);
            $extra = [
                'category_name' => $category->name,
                'items_type' => 'category'
            ];

            return new BaseCollection($subCategories, CategoryBasicResource::class, $extra);
        }

        // if category has products
        $products = $this->productRepository->getPaginatedCategoryProducts($category->id);
        $extra = [
            'category_name' => $category->name,
            'items_type' => 'product'
        ];

        return new BaseCollection($products, ProductBasicResource::class, $extra);
    }
}

<?php

namespace App\Services\User;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

class TrendingEntityService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ProductRepository $productRepository
    ) {}

    public function getTrendingEntities()
    {
        $categories = $this->categoryRepository->getTrendingCategories();
        $products   = $this->productRepository->getTrendingProducts();

        $combined = $categories->merge($products)->shuffle()
            ->map(function ($entity) {
                return [
                    'id' => $entity->id,
                    'type' => get_class($entity) == Category::class ? 'category' : 'product',
                    'name' => $entity->name,
                    'image' => $entity->image?->getUrl(),
                ];
            });

        return $combined;
    }
}

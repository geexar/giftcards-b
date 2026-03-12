<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

class UserSearchService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository
    ) {}

    public function getSearchResults(string $searchQuery)
    {
        $products = $this->productRepository->searchedProducts($searchQuery);
        $categories = $this->categoryRepository->searchedCategories($searchQuery);

        $combined = $products->merge($categories)
            ->take(10)
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

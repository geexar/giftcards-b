<?php

namespace App\Services\User;

use App\Enums\ProductStatus;
use App\Repositories\ProductRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryService $categoryService
    ) {}

    public function getProduct(string $id)
    {
        $product = $this->productRepository->getById($id);

        if (
            !$product ||
            $product->status !== ProductStatus::ACTIVE ||
            !$this->categoryService->isCategoryPathActive($product->category)
        ) {
            throw new NotFoundHttpException('product not found');
        }

        return $product;
    }


    public function getFeaturedProductsGroupedByMainCategory()
    {
        $products = $this->productRepository->getFeaturedProducts(); // assume collection

        // Group products by main category id
        $grouped = $products->groupBy(function ($product) {
            $category = $product->category;

            // Traverse up to main category
            while ($category->parent) {
                $category = $category->parent;
            }

            return $category->id;
        });

        // Transform into desired format
        $result = $grouped->map(function ($products) {
            $mainCategory = $products->first()->category;

            // Traverse up once to main category
            while ($mainCategory->parent) {
                $mainCategory = $mainCategory->parent;
            }

            return [
                'category' => [
                    'id' => $mainCategory->id,
                    'name' => $mainCategory->name,
                ],
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image?->getUrl(), // adjust if you store in media or url
                    ];
                })->values(),
            ];
        })->values();

        return $result;
    }

    public function getSuggestedProducts(string $productId, int $limit)
    {
        $product = $this->getProduct($productId);

        return $this->productRepository->getSuggestedProducts($product, $limit);
    }
}

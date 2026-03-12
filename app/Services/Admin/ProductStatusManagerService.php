<?php

namespace App\Services\Admin;

use App\Enums\ProductStatus;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductStatusManagerService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getProduct(string $id)
    {
        $product = $this->productRepository->getById($id);

        if (!$product) {
            throw new NotFoundHttpException('product not found');
        }

        return $product;
    }

    public function getCategory(string $id)
    {
        $category = $this->categoryRepository->getById($id);

        if (!$category) {
            throw new NotFoundHttpException('category not found');
        }

        return $category;
    }

    public function toggleCategoryStatus(string $id)
    {
        $category = $this->getCategory($id);

        DB::transaction(function () use ($category) {
            $category->update(['is_active' => !$category->is_active]);
            $this->activityLogService->store($category, 'category.status_updated');
        });
    }

    public function updateProductStatus(string $id, string $status)
    {
        $product = $this->getProduct($id);

        if ($product->status == ProductStatus::DRAFTED) {
            throw new BadRequestHttpException("can't update drafted product with id: {$id}");
        }

        $product->update(['status' => $status]);

        $this->activityLogService->store($product, 'product.status_updated');
    }
}

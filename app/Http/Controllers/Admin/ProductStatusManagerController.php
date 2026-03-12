<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductStatusRequest;
use App\Http\Resources\Admin\CategoryStatusResource;
use App\Http\Resources\Admin\ProductStatusResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ProductRepository;
use App\Services\Admin\CategoryService;
use App\Services\Admin\ProductStatusManagerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductStatusManagerController extends Controller implements HasMiddleware
{
    public function __construct(
        private ProductStatusManagerService $productStatusManagerService,
        private ProductRepository $productRepository,
        private CategoryService $categoryService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show product status manager', only: ['categories', 'products']),
            new Middleware('can:update product status', only: ['updateProductStatus', 'toggleCategoryStatus']),
        ];
    }

    public function categories(Request $request)
    {
        $categories = $this->categoryService->getCategoryTree($request->type, $request->products_source);

        return success($categories);
    }

    public function products()
    {
        $products = $this->productRepository->getPaginatedProductsForStatusManager();

        return success(new BaseCollection($products, ProductStatusResource::class));
    }

    public function updateProductStatus(string $id, UpdateProductStatusRequest $request)
    {
        $this->productStatusManagerService->updateProductStatus($id, $request->status);

        return success(true);
    }


    public function toggleCategoryStatus(string $id)
    {
        $this->productStatusManagerService->toggleCategoryStatus($id);

        return success(true);
    }
}

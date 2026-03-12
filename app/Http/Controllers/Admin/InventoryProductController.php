<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestockProductRequest;
use App\Http\Resources\Admin\ProductStockResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ProductRepository;
use App\Services\Admin\ProductInventoryService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InventoryProductController extends Controller implements HasMiddleware
{
    public function __construct(
        private ProductInventoryService $productInventoryService,
        private ProductRepository $productRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show products inventory', only: ['index', 'show']),
            new Middleware('can:update product stock', only: ['clearProductStock', 'addProductStock']),
        ];
    }

    public function index()
    {
        $products = $this->productRepository->getPaginatedInventoryProducts();

        return success(new BaseCollection($products, ProductStockResource::class));
    }

    public function show(string $id)
    {
        $product = $this->productInventoryService->getProduct($id);

        return success(ProductStockResource::make($product));
    }

    public function clearProductStock(string $id)
    {
        $this->productInventoryService->clearProductStock($id);

        return success(true);
    }

    public function restockProduct(string $id, RestockProductRequest $request)
    {
        $this->productInventoryService->restockProduct($id, $request->validated());

        return success(true);
    }
}

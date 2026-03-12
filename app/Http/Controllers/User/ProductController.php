<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SubscribeProductAvailabilityRequest;
use App\Http\Resources\User\ProductResource;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\ProductBasicResource;
use App\Http\Resources\User\ProductPriceResource;
use App\Repositories\ProductRepository;
use App\Services\User\ProductAvailabilitySubscriptionService;
use App\Services\User\ProductService;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository,
        private ProductAvailabilitySubscriptionService $productAvailabilitySubscriptionService
    ) {}

    public function index()
    {
        $products = $this->productRepository->getProductsForWebsite();

        if (request()->boolean('paginated')) {
            return success(new BaseCollection($products, ProductBasicResource::class));
        }

        return success(ProductBasicResource::collection($products));
    }

    public function show(string $id)
    {
        $product = $this->productService->getProduct($id);

        return success(new ProductResource($product));
    }

    public function suggestedProducts(string $id)
    {
        $products = $this->productService->getSuggestedProducts($id, 10);

        return success(ProductPriceResource::collection($products));
    }

    public function subscribeProductAvailability(SubscribeProductAvailabilityRequest $request)
    {
        $this->productAvailabilitySubscriptionService->subscribe($request->validated());

        return success(true);
    }
}

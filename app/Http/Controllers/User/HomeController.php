<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CategoryBasicResource;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\ProductBasicResource;
use App\Http\Resources\User\ProductPriceResource;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\User\CategoryService;
use App\Services\User\ProductService;
use App\Services\User\TrendingEntityService;
use App\Services\UserSearchService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository,
        private TrendingEntityService $trendingEntityService,
        private CategoryService $categoryService,
        private CategoryRepository $categoryRepository,
        private UserSearchService $userSearchService
    ) {}

    public function search(Request $request)
    {
        $results = $this->userSearchService->getSearchResults($request->search_query);

        return success($results);
    }

    public function promotedCategories()
    {
        $categories = $this->categoryRepository->getPromotedCategories();

        return success(CategoryBasicResource::collection($categories));
    }

    public function featuredProducts()
    {
        $productsGroupedByMainCategory = $this->productService->getFeaturedProductsGroupedByMainCategory();

        return success($productsGroupedByMainCategory);
    }

    public function popularProducts()
    {
        $products = $this->productRepository->getPopularProducts();

        return success(ProductPriceResource::collection($products));
    }

    public function discountedProducts()
    {
        $products = $this->productRepository->getDiscountedProducts();

        return success(new BaseCollection($products, ProductPriceResource::class));
    }

    public function trendingEntities()
    {
        $entites = $this->trendingEntityService->getTrendingEntities();

        return success($entites);
    }

    public function featuredCategoryItems()
    {
        $data  = $this->categoryService->getFeaturedCategoryItems();

        return success($data);
    }

    public function bestSellerProducts()
    {
        $products = $this->productRepository->getBestSellerProducts();

        if (request()->boolean('paginated')) {
            return success(new BaseCollection($products, ProductBasicResource::class));
        }

        return success(ProductBasicResource::collection($products));
    }
}

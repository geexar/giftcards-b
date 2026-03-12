<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CategoryBasicResource;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\CategoryResource;
use App\Repositories\CategoryRepository;
use App\Services\User\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
        private CategoryRepository $categoryRepository,
    ) {}

    public function index()
    {
        $categories = $this->categoryRepository->getCategoriesForWebsite();

        if(request()->boolean('paginated')) {
            return success(new BaseCollection($categories, CategoryBasicResource::class));
        }

        return success(CategoryBasicResource::collection($categories));
    }

    public function show(string $id)
    {
        $category = $this->categoryService->getCategory($id);

        return success(new CategoryResource($category));
    }

    public function mainCategories()
    {
        $categories = $this->categoryRepository->getMainCategoriesForWebsite();

        return success(CategoryBasicResource::collection($categories));
    }

    public function subCategories(Request $request)
    {
        $categories = $this->categoryRepository->getSubCategoriesForWebsite($request->parent_id);

        return success(CategoryBasicResource::collection($categories));
    }
}

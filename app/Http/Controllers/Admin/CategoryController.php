<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\CategoryTreeRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\CategoryRepository;
use App\Services\Admin\CategoryService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    public function __construct(
        private CategoryService $categoryService,
        private CategoryRepository $categoryRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show categories', only: ['index']),
            new Middleware('can:create category', only: ['store']),
            new Middleware('can:update category', only: ['show', 'update', 'toggleStatus']),
            new Middleware('can:delete category', only: ['destroy']),
        ];
    }

    /**
     * List paginated categories
     */
    public function index()
    {
        $categories = $this->categoryRepository->getPaginatedCategoriesForDashboard();

        return success(new BaseCollection($categories, CategoryResource::class));
    }

    /**
     * Create a new category
     */
    public function store(CategoryRequest $request)
    {
        $this->categoryService->create($request->validated());

        return success(true);
    }

    /**
     * Show a specific category
     */
    public function show(string $id)
    {
        $category = $this->categoryService->getCategory($id);

        return success(CategoryResource::make($category->loadCount('products')));
    }

    /**
     * Update an existing category
     */
    public function update(string $id, CategoryRequest $request)
    {
        $this->categoryService->update($id, $request->validated());

        return success(true);
    }

    /**
     * Delete a category
     */
    public function destroy(string $id)
    {
        $this->categoryService->delete($id);

        return success(true);
    }

    /**
     * Get category tree
     */
    public function categoryTree(CategoryTreeRequest $request)
    {
        $categories = $this->categoryService->getCategoryTree($request->type, $request->products_count_source);

        return success($categories);
    }

    public function toggleStatus(string $id)
    {
        $this->categoryService->toggleStatus($id);

        return success(true);
    }
}

<?php

namespace App\Repositories;

use App\Enums\CategoryType;
use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function searchedCategories(string $searchQuery)
    {
        return $this->model
            ->where('is_active', true)
            ->withActiveParent()
            ->where(function ($q) use ($searchQuery) {
                $q->where('name->en', 'like', "%{$searchQuery}%")
                    ->orWhere('name->ar', 'like', "%{$searchQuery}%");
            })
            ->limit(10)
            ->get();
    }

    public function getApiCategories()
    {
        return $this->model->where('source', 'api')->get();
    }

    public function getByExternalId(string $externalId)
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    public function getPaginatedCategoriesForDashboard()
    {
        return $this->model
            ->when(request('type'), fn($q, $type) => $q->where('type', $type))
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                });
            })
            ->when(request()->has('is_active'), fn($q) => $q->where('is_active', request('is_active')))
            ->when(request('parent_id'), fn($q, $parentId) => $q->where('parent_id', $parentId))
            ->withCount('products')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getCategoriesForWebsite()
    {
        $query = $this->model
            ->where('is_active', true)
            ->withActiveParent()
            ->where('parent_id', request('parent_id'))
            ->with('media')
            ->withCount('childs');

        return request()->boolean('paginated')
            ? $query->paginate(page: request('page'), perPage: request('per_page'))
            : $query->get();
    }

    public function getMainCategoriesForWebsite()
    {
        return $this->model
            ->where('type', CategoryType::MAIN->value)
            ->where('is_active', true)
            ->with('media')
            ->withCount('childs')
            ->get();
    }

    public function getSubCategoriesForWebsite(?string $parentId = '0')
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->with('media')
            ->get();
    }

    public function categoryTree(string $type, ?string $productsSource = null)
    {
        return $this->model
            ->where('type', $type)
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                });
            })
            ->when(request()->has('is_active'), fn($q) => $q->where('is_active', request('is_active')))
            ->with([
                'childs' => function ($q) use ($productsSource) {
                    $q->when(request()->has('is_active'), fn($q2) => $q2->where('is_active', request('is_active')));

                    if ($productsSource) {
                        $q->where(function ($query) use ($productsSource) {
                            $query->whereHas('products', fn($q2) => $q2->where('source', $productsSource))
                                ->orWhereHas('childs.products', fn($q3) => $q3->where('source', $productsSource));
                        });
                    }
                    $q->with([
                        'childs' => function ($q2) use ($productsSource) {
                            $q2->when(request()->has('is_active'), fn($q3) => $q3->where('is_active', request('is_active')));

                            if ($productsSource) {
                                $q2->whereHas('products', fn($q3) => $q3->where('source', $productsSource));
                            }
                        }
                    ]);
                }
            ])
            ->when($productsSource, function ($query, $productsSource) {
                $query->where(function ($q) use ($productsSource) {
                    $q->whereHas('products', function ($p) use ($productsSource) {
                        $p->where('source', $productsSource)
                            ->when(request()->has('is_active'), fn($q2) => $q2->where('is_active', request('is_active')));
                    })
                        ->orWhereHas('childs.products', function ($p) use ($productsSource) {
                            $p->where('source', $productsSource)
                                ->when(request()->has('is_active'), fn($q2) => $q2->where('is_active', request('is_active')));
                        })
                        ->orWhereHas('childs.childs.products', function ($p) use ($productsSource) {
                            $p->where('source', $productsSource)
                                ->when(request()->has('is_active'), fn($q2) => $q2->where('is_active', request('is_active')));
                        });
                });
            })
            ->get();
    }

    public function trendingCount(?string $exceptedId = null)
    {
        return $this->model
            ->where('is_trending', true)
            ->where('id', '!=', $exceptedId)
            ->count();
    }

    public function getPromotedCategories()
    {
        return $this->model
            ->where('is_promoted', true)
            ->where('is_active', true)
            ->withActiveParent()
            ->with('media')
            ->get();
    }

    public function getFeaturedCategory()
    {
        return $this->model
            ->where('is_featured', true)
            ->where('is_active', true)
            ->withActiveParent()
            ->with('media')
            ->first();
    }

    public function getTrendingCategories()
    {
        return $this->model
            ->where('is_trending', true)
            ->where('is_active', true)
            ->withActiveParent()
            ->with('media')
            ->get();
    }

    public function getPaginatedSubCategories(string $categoryId)
    {
        return $this->model
            ->where('parent_id', $categoryId)
            ->where('is_active', true)
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }


    public function getDdl(?string $type = null, ?string $parent_id = null, ?bool $only_active = false)
    {
        return $this->model
            ->when($type, fn($query) => $query->where('type', $type))
            ->when($parent_id, fn($query) => $query->where('parent_id', $parent_id))
            ->when($only_active, fn($query) => $query->where('is_active', true))
            ->get();
    }
}

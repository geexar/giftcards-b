<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository extends BaseRepository
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedArticlesForDashboard()
    {
        return $this->model
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title->en', 'like', "%{$search}%")
                        ->orWhere('title->ar', 'like', "%{$search}%");
                });
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getPaginatedArticlesForWebsite()
    {
        return $this->model
            ->where('is_active', true)
            ->when(request('search'), function ($query, $search) {
                return $query->where('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ar', 'like', "%{$search}%");
            })
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }
}

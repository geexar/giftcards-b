<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Http\Resources\Admin\ArticleResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ArticleRepository;
use App\Services\Admin\ArticleService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ArticleController extends Controller implements HasMiddleware
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $articleRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show articles', only: ['index']),
            new Middleware('can:create article', only: ['store']),
            new Middleware('can:update article', only: ['show', 'update', 'toggleStatus']),
            new Middleware('can:delete article', only: ['destroy']),
        ];
    }

    /**
     * List paginated articles
     */
    public function index()
    {
        $articles = $this->articleRepository->getPaginatedArticlesForDashboard();

        return success(new BaseCollection($articles, ArticleResource::class));
    }

    /**
     * Create a new article
     */
    public function store(ArticleRequest $request)
    {
        $this->articleService->create($request->validated());

        return success(true);
    }

    /**
     * Show a specific article
     */
    public function show(string $id)
    {
        $article = $this->articleService->getArticle($id);

        return success(ArticleResource::make($article));
    }

    /**
     * Update an existing article
     */
    public function update(ArticleRequest $request, string $id)
    {
        $this->articleService->update($id, $request->validated());

        return success(true);
    }

    /**
     * Delete an article
     */
    public function destroy(string $id)
    {
        $this->articleService->delete($id);

        return success(true);
    }

    public function toggleStatus(string $id)
    {
        $this->articleService->toggleStatus($id);

        return success(true);
    }
}

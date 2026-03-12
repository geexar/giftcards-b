<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\ArticleResource;
use App\Repositories\ArticleRepository;
use App\Services\User\ArticleService;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $articleRepository
    ) {}

    public function index()
    {
        $articles = $this->articleRepository->getPaginatedArticlesForWebsite();

        return success(new BaseCollection($articles, ArticleResource::class));
    }

    public function show(string $id)
    {
        $article = $this->articleService->getArticle($id);

        return success(new ArticleResource($article));
    }
}

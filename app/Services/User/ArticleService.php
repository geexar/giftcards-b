<?php

namespace App\Services\User;

use App\Repositories\ArticleRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleService
{
    public function __construct(private ArticleRepository $articleRepository,) {}

    public function getArticle(string $id)
    {
        $article = $this->articleRepository->getById($id);

        if (!$article || !$article->is_active) {
            throw new NotFoundHttpException('Article not found');
        }

        return $article;
    }
}

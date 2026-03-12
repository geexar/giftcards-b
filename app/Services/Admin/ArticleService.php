<?php

namespace App\Services\Admin;

use App\Repositories\ArticleRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getArticle(string $id)
    {
        $article = $this->articleRepository->getById($id);

        if (!$article) {
            throw new NotFoundHttpException('Article not found');
        }

        return $article;
    }
    
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $article = $this->articleRepository->create([
                'title' => $data['title'],
                'body' => $data['body'],
            ]);

            if (isset($data['image'])) {
                $article->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($article, 'article.created');
        });
    }

    public function update(string $id, array $data)
    {
        $article = $this->getArticle($id);

        return DB::transaction(function () use ($article, $data) {
            $this->articleRepository->update($article, [
                'title' => $data['title'],
                'body' => $data['body'],
                'is_active' => $data['is_active'],
            ]);

            if (isset($data['image'])) {
                $article->clearMediaCollection();
                $article->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($article, 'article.updated');
        });
    }

    public function delete(string $id)
    {
        $article = $this->getArticle($id);

        DB::transaction(function () use ($article) {
            $article->delete();
            $article->clearMediaCollection();

            $this->activityLogService->store($article, 'article.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $article = $this->getArticle($id);

        DB::transaction(function () use ($article) {
            $article->update(['is_active' => !$article->is_active]);
            $this->activityLogService->store($article, 'article.updated');
        });
    }
}

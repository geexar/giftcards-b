<?php

namespace App\Services\Admin;

use App\Repositories\StaticPageRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StaticPageService
{
    public function __construct(private StaticPageRepository $staticPageRepository, private ActivityLogService $activityLogService) {}

    public function getPage(string $id)
    {
        $page = $this->staticPageRepository->getById($id);

        if (!$page) {
            throw new NotFoundHttpException('Static page not found');
        }

        return $page;
    }

    public function update(string $id, array $data): void
    {
        $page = $this->getPage($id);

        DB::transaction(function () use ($page, $data) {
            $this->staticPageRepository->update($page, [
                'body' => $data['body'],
            ]);

            $this->activityLogService->store($page, 'static_page.updated');
        });
    }
}

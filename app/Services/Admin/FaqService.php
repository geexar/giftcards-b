<?php

namespace App\Services\Admin;

use App\Repositories\FaqRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FaqService
{
    public function __construct(private FaqRepository $faqRepository, private ActivityLogService $activityLogService) {}

    public function getFaq(string $id)
    {
        $faq = $this->faqRepository->getById($id);

        if (!$faq) {
            throw new NotFoundHttpException('FAQ not found');
        }

        return $faq;
    }

    public function create(array $data)
    {
        DB::transaction(function () use ($data) {
            $faq = $this->faqRepository->create($data);

            $this->activityLogService->store($faq, 'faq.created');
        });
    }

    public function update(string $id, array $data)
    {
        $faq = $this->getFaq($id);

        DB::transaction(function () use ($faq, $data) {
            $this->faqRepository->update($faq, $data);

            $this->activityLogService->store($faq, 'faq.updated');
        });
    }

    public function delete(string $id)
    {
        $faq = $this->getFaq($id);

        DB::transaction(function () use ($faq) {
            $faq->delete();

            $this->activityLogService->store($faq, 'faq.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $faq = $this->getFaq($id);

        DB::transaction(function () use ($faq) {
            $faq->update(['is_active' => !$faq->is_active]);

            $this->activityLogService->store($faq, 'faq.updated');
        });
    }
}

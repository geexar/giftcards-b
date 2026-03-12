<?php

namespace App\Services\Admin;

use App\Repositories\BannerRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BannerService
{
    public function __construct(
        private BannerRepository $bannerRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getBanner(string $id)
    {
        $banner = $this->bannerRepository->getById($id);

        if (!$banner) {
            throw new NotFoundHttpException('Banner not found');
        }

        return $banner;
    }

    public function create(array $data)
    {
        DB::transaction(function () use ($data) {
            $banner = $this->bannerRepository->create([
                'type' => $data['type'],
                'name' => $data['name'],
                'link' => $data['link'],
            ]);

            if (isset($data['image'])) {
                $banner->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($banner, 'banner.created');
        });
    }

    public function update(string $id, array $data)
    {
        $banner = $this->getBanner($id);

        DB::transaction(function () use ($banner, $data) {
            $this->bannerRepository->update($banner, [
                'type' => $data['type'],
                'name' => $data['name'],
                'link' => $data['link'],
                'is_active' => $data['is_active'],
            ]);

            if (isset($data['image'])) {
                $banner->clearMediaCollection();
                $banner->addMedia($data['image'])->toMediaCollection();
            }

            $this->activityLogService->store($banner, 'banner.updated');
        });
    }

    public function delete(string $id)
    {
        $banner = $this->getBanner($id);

        DB::transaction(function () use ($banner) {
            $banner->delete();
            $banner->clearMediaCollection();

            $this->activityLogService->store($banner, 'banner.deleted');
        });
    }

    public function toggleStatus(string $id)
    {
        $banner = $this->getBanner($id);

        DB::transaction(function () use ($banner) {
            $banner->update(['is_active' => !$banner->is_active]);
            $this->activityLogService->store($banner, 'banner.updated');
        });
    }
}

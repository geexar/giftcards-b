<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\BannerRepository;
use App\Services\Admin\BannerService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BannerController extends Controller implements HasMiddleware
{
    public function __construct(
        private BannerService $bannerService,
        private BannerRepository $bannerRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show banners', only: ['index']),
            new Middleware('can:create banner', only: ['store']),
            new Middleware('can:update banner', only: ['show', 'update', 'toggleStatus']),
            new Middleware('can:delete banner', only: ['destroy']),
        ];
    }

    /**
     * List paginated banners
     */
    public function index()
    {
        $banners = $this->bannerRepository->getPaginatedBanners();

        return success(new BaseCollection($banners, BannerResource::class));
    }

    /**
     * Create a new banner
     */
    public function store(BannerRequest $request)
    {
        $this->bannerService->create($request->validated());

        return success(true);
    }

    /**
     * Show a specific banner
     */
    public function show(string $id)
    {
        $banner = $this->bannerService->getBanner($id);

        return success(BannerResource::make($banner));
    }

    /**
     * Update a banner
     */
    public function update(BannerRequest $request, string $id)
    {
        $this->bannerService->update($id, $request->validated());

        return success(true);
    }

    /**
     * Delete a banner
     */
    public function destroy(string $id)
    {
        $this->bannerService->delete($id);

        return success(true);
    }

    public function toggleStatus(string $id)
    {
        $this->bannerService->toggleStatus($id);

        return success(true);
    }
}

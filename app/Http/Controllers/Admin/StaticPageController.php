<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaticPageRequest;
use App\Http\Resources\Admin\StaticPageResource;
use App\Repositories\StaticPageRepository;
use App\Services\Admin\StaticPageService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StaticPageController extends Controller implements HasMiddleware
{
    public function __construct(
        private StaticPageService $staticPageService,
        private StaticPageRepository $staticPageRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show static pages', only: ['index']),
            new Middleware('can:update static page', only: ['show', 'update']),
        ];
    }

    /**
     * List paginated static pages
     */
    public function index()
    {
        $pages = $this->staticPageRepository->getAll();

        return success(StaticPageResource::collection($pages));
    }

    /**
     * Show a specific static page
     */
    public function show(string $id)
    {
        $page = $this->staticPageService->getPage($id);

        return success(StaticPageResource::make($page));
    }

    /**
     * Update a static page
     */
    public function update(StaticPageRequest $request, string $id)
    {
        $this->staticPageService->update($id, $request->validated());

        return success(true);
    }
}

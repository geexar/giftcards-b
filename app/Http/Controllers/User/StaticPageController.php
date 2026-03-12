<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\StaticPageResource;
use App\Repositories\StaticPageRepository;

class StaticPageController extends Controller
{
    public function __construct(private StaticPageRepository $staticPageRepository) {}

    public function index()
    {
        $pages = $this->staticPageRepository->getAll();

        return success(StaticPageResource::collection($pages));
    }

    public function show(string $id)
    {
        $page = $this->staticPageRepository->getById($id);

        return success(new StaticPageResource($page));
    }
}

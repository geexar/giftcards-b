<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivityLogResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ActivityLogRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ActivityLogController extends Controller implements HasMiddleware
{
    public function __construct(
        private ActivityLogRepository $activityLogRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show activity logs', only: ['index']),
        ];
    }

    public function index()
    {
        $logs = $this->activityLogRepository->getPaginatedLogs();

        return success(new BaseCollection($logs, ActivityLogResource::class));
    }
}

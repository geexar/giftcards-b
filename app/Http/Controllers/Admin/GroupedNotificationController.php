<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupedNotificationRequest;
use App\Http\Resources\Admin\GroupedNotificationResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\GroupedNotificationRepository;
use App\Services\Admin\GroupedNotificationService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class GroupedNotificationController extends Controller implements HasMiddleware
{
    public function __construct(
        private GroupedNotificationService $groupedNotificationService,
        private GroupedNotificationRepository $groupedNotificationRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show grouped notifications', only: ['index']),
            new Middleware('can:create grouped notification', only: ['store']),
        ];
    }

    /**
     * Display a paginated list of Grouped Notifications
     */
    public function index()
    {
        $notifications = $this->groupedNotificationRepository->getPaginatedNotifications();

        return success(new BaseCollection($notifications, GroupedNotificationResource::class));
    }

    /**
     * Store a newly created Grouped Notification
     */
    public function store(GroupedNotificationRequest $request)
    {
        $this->groupedNotificationService->create($request->validated());

        return success(true);
    }
}

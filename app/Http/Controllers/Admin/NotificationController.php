<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\NotificationResource;
use App\Repositories\NotificationRepository;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private NotificationRepository $notificationRepository
    ) {}

    public function index()
    {
        $notifications = $this->notificationRepository->getUserNotifications(auth('admin')->user());
        $unreadCount = $this->notificationRepository->getUserUnreadNotificationsCount(auth('admin')->user());
        $extra = ['unread_count' => $unreadCount];

        return success(BaseCollection::make($notifications, NotificationResource::class, $extra));
    }

    public function markAsRead(Request $request)
    {
        $this->notificationService->markAsRead('admin', $request->notification_id);

        return success(true);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead('admin');

        return success(true);
    }
}

<?php

namespace App\Http\Controllers\User;

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
        $notifications = $this->notificationRepository->getUserNotifications(auth('user')->user());
        $unreadCount = $this->notificationRepository->getUserUnreadNotificationsCount(auth('user')->user());
        $extra = ['unread_count' => $unreadCount];

        return success(BaseCollection::make($notifications, NotificationResource::class, $extra));
    }

    public function markAsRead(Request $request)
    {
        $this->notificationService->markAsRead('user', $request->notification_id);

        return success(true);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead('user');

        return success(true);
    }

    public function delete(Request $request)
    {
        $this->notificationService->delete('user', $request->notification_id);

        return success(true);
    }

    public function deleteAll()
    {
        $this->notificationService->deleteAll('user');

        return success(true);
    }
}

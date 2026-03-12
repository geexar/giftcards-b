<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationService
{
    public function __construct(private NotificationRepository $notificationRepository) {}

    public function markAsRead(string $guard, string $id)
    {
        $notification = $this->notificationRepository->getById($id);

        if (!$notification) {
            throw new NotFoundHttpException('notification not found');
        }

        if ($notification->notifiable_id != auth($guard)->id()) {
            throw new BadRequestHttpException('notification not found');
        }

        $this->notificationRepository->markAsRead($notification);
    }

    public function markAllAsRead(string $guard)
    {
        DB::transaction(function () use ($guard) {
            $this->notificationRepository->markAllAsRead(auth($guard)->user());
        });
    }

    public function delete(string $guard, string $id)
    {
        $notification = $this->notificationRepository->getById($id);

        if (!$notification) {
            throw new NotFoundHttpException('notification not found');
        }

        if ($notification->notifiable_id != auth($guard)->id()) {
            throw new BadRequestHttpException('notification not found');
        }

        $notification->delete();
    }

    public function deleteAll(string $guard)
    {
        DB::transaction(function () use ($guard) {
            $this->notificationRepository->deleteAll(auth($guard)->user());
        });
    }
}

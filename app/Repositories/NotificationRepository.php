<?php

namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead($user)
    {
        $this->model
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUserNotifications($user)
    {
        return $this->model
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->id)
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getUserUnreadNotificationsCount($user)
    {
        return $this->model
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getLatestUserNotifications($user, int $limit = 5)
    {
        return $this->model
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function deleteAll($user)
    {
        $this->model
            ->where('notifiable_type', $user::class)
            ->where('notifiable_id', $user->id)
            ->delete();
    }
}

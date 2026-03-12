<?php

namespace App\Repositories;

use App\Models\GroupedNotification;

class GroupedNotificationRepository extends BaseRepository
{
    public function __construct(GroupedNotification $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedNotifications()
    {
        return $this->model
            ->with('users')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function notificationsCount()
    {
        return $this->model
            ->when(request('from_date'), fn($query) => $query->where('created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->where('created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('created_at', now()),
                    'last_7_days' => $query->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null
                };
            })
            ->count();
    }
}

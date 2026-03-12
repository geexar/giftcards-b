<?php

namespace App\Repositories;

use App\Models\ActivityLog;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedLogs()
    {
        return $this->model
            ->with('actor')
            ->when(request('from_date'), fn($query) => $query->whereDate('created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('created_at', now()),
                    'last_7_days' => $query->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null
                };
            })
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }
}

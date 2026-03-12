<?php

namespace App\Traits;

trait DateRangeFilter
{
    public function scopeDateRangeFilter($query)
    {
        return $query
            ->when(request('from_date'), fn($q) => $q->whereDate('created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($q) => $q->whereDate('created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($q, $period) {
                match ($period) {
                    'today'        => $q->whereDate('created_at', now()),
                    'last_7_days'  => $q->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $q->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month'   => $q->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default        => null,
                };
            });
    }
}

<?php

namespace App\Repositories;

use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Refund;

class RefundRepository extends BaseRepository
{
    public function __construct(Refund $model)
    {
        parent::__construct($model);
    }

    public function getByRefundNo(string $refundNo)
    {
        return $this->model->where('refund_no', $refundNo)->first();
    }

    public function getPaginatedRefunds()
    {
        return $this->getRefundsQuery()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getRefundsQuery()
    {
        return $this->model
            ->with('processor', 'order')
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->When(request('search'), function ($query, $search) {
                $query->where('refund_no', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q2) use ($search) {
                        $q2->where('order_no', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            });
                    });
            })
            ->when(request('created_from_date'), fn($query) => $query->whereDate('created_at', '>=', request('created_from_date')))
            ->when(request('created_to_date'), fn($query) => $query->whereDate('created_at', '<=', request('created_to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('created_at', now()),
                    'last_7_days' => $query->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null
                };
            })
            ->when(request('processed_from_date'), fn($query) => $query->whereDate('created_at', '>=', request('processed_from_date')))
            ->when(request('processed_to_date'), fn($query) => $query->whereDate('created_at', '<=', request('processed_to_date')))
            ->when(request('processed_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('created_at', now()),
                    'last_7_days' => $query->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null
                };
            })
            ->orderBy(request('sort_by') ?? 'created_at', request('sort_order') ?? 'desc');
    }

    public function totalRefunds()
    {
        return $this->model
            ->dateRangeFilter()
            ->count();
    }

    public function totalPendingRefunds()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', RefundStatus::PENDING->value)
            ->count();
    }

    public function totalProcessedRefunds()
    {
        return $this->model
            ->when(request('from_date'), fn($query) => $query->whereDate('processed_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('processed_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('processed_at', now()),
                    'last_7_days' => $query->whereDate('processed_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('processed_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('processed_at', now()->month)->whereYear('processed_at', now()->year),
                    default => null
                };
            })
            ->where('status', RefundStatus::PROCESSED->value)
            ->count();
    }

    public function totalProcessedRefundAmount()
    {
        return $this->model
            ->when(request('from_date'), fn($query) => $query->whereDate('processed_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('processed_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('processed_at', now()),
                    'last_7_days' => $query->whereDate('processed_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('processed_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('processed_at', now()->month)->whereYear('processed_at', now()->year),
                    default => null
                };
            })
            ->where('status', RefundStatus::PROCESSED->value)
            ->sum('amount');
    }
}

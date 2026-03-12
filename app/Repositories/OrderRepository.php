<?php

namespace App\Repositories;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getByOrderNo(string $orderNo)
    {
        return $this->model->where('order_no', $orderNo)->first();
    }

    public function getPaginatedOrders()
    {
        return $this->dashboardOrdersQuery()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function dashboardOrdersQuery()
    {
        $sortBy = request('sort_by', 'created_at');
        $sortOrder = request('sort_order', 'DESC');

        return $this->model
            ->excludeWaitingPayment()
            ->with('transaction', 'refund', 'paymentMethod', 'user', 'items')
            ->when(request('search'), function ($query, $search) {
                $query->where('order_no', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when(request('status'), fn($query, $status) => $query->where('status', $status))
            ->when(request('payment_method_id'), fn($query, $paymentMethodId) => $query->where('payment_method_id', $paymentMethodId))
            ->when(request('refund_status'), function ($query, $refundStatus) {
                $query->whereHas('refund', fn($q) => $q->where('status', $refundStatus));
            })
            ->when(request('needs_action'), function ($query) {
                $query->whereIn('status', [
                    OrderStatus::AWAITING_ACTION->value,
                    OrderStatus::PENDING_CONFIRMATION->value,
                    OrderStatus::PARTIALLY_COMPLETED->value,
                ]);
            })
            ->when(request('created_at_from_date'), fn($q) => $q->whereDate('created_at', '>=', request('created_at_from_date')))
            ->when(request('created_at_to_date'), fn($q) => $q->whereDate('created_at', '<=', request('created_at_to_date')))
            ->when(request('creation_range'), function ($q, $period) {
                match ($period) {
                    'today'        => $q->whereDate('created_at', now()),
                    'last_7_days'  => $q->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $q->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month'   => $q->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default        => null,
                };
            })
            ->when(request('updated_at_from_date'), fn($q) => $q->whereDate('created_at', '>=', request('updated_at_from_date')))
            ->when(request('updated_at_to_date'), fn($q) => $q->whereDate('created_at', '<=', request('updated_at_to_date')))
            ->when(request('update_range'), function ($q, $period) {
                match ($period) {
                    'today'        => $q->whereDate('created_at', now()),
                    'last_7_days'  => $q->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $q->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month'   => $q->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    default        => null,
                };
            })
            ->when($sortBy === 'rating', function ($query) use ($sortOrder) {
                $query->withAvg(
                    ['overallRating as overall_rating_avg'],
                    'rating'
                )
                    ->orderByRaw('overall_rating_avg IS NULL')
                    ->orderBy('overall_rating_avg', $sortOrder);
            })
            ->when($sortBy !== 'rating', fn($query) => $query->orderBy($sortBy, $sortOrder));
    }

    public function getPaginatedUserOrders(string $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->excludeWaitingPayment()
            ->with('transaction', 'refund', 'paymentMethod', 'user', 'items.product.media')
            ->when(request('order_no'), fn($query, $orderNo) => $query->where('order_no', 'like', "%{$orderNo}%"))
            ->when(request('state'), function ($query, $state) {
                if ($state == 'completed') {
                    $query->whereIn('status', [
                        OrderStatus::COMPLETED->value,
                        OrderStatus::PROCESSED->value
                    ]);
                }

                if ($state == 'in_progress') {
                    $query->whereIn('status', [
                        OrderStatus::PENDING_CONFIRMATION->value,
                        OrderStatus::PROCESSING->value,
                        OrderStatus::AWAITING_ACTION,
                        OrderStatus::PARTIALLY_COMPLETED->value
                    ]);
                }

                if ($state == 'incompleted') {
                    $query->whereIn('status', [
                        OrderStatus::FAILED->value,
                        OrderStatus::REJECTED->value,
                        OrderStatus::CANCELED->value
                    ]);
                }
            })
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getNeedActionOrdersCount()
    {
        return $this->model
            ->whereHas('items', function ($query) {
                $query->where('status', OrderItemStatus::PENDING_CONFIRMATION->value);
            })
            ->count();
    }

    public function totalOrdersCount()
    {
        return $this->model
            ->excludeWaitingPayment()
            ->dateRangeFilter()
            ->count();
    }

    public function totalPaidAmount()
    {
        return $this->model
            ->excludeWaitingPayment()
            ->dateRangeFilter()
            ->sum('total');
    }

    public function countCompleted()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::COMPLETED->value)
            ->count();
    }

    public function countProcessed()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::PROCESSED->value)
            ->count();
    }

    public function countProcessing()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::PROCESSING->value)
            ->count();
    }

    public function countPartiallyCompleted()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::PARTIALLY_COMPLETED->value)
            ->count();
    }

    public function countPendingConfirmation()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::PENDING_CONFIRMATION->value)
            ->count();
    }

    public function countAwaitingAction()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::AWAITING_ACTION->value)
            ->count();
    }

    public function countCanceled()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::CANCELED->value)
            ->count();
    }

    public function countRejected()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::REJECTED->value)
            ->count();
    }

    public function countFailed()
    {
        return $this->model
            ->dateRangeFilter()
            ->where('status', OrderStatus::FAILED->value)
            ->count();
    }

    public function guestOrdersCount()
    {
        return $this->model
            ->excludeWaitingPayment()
            ->dateRangeFilter()
            ->whereNull('user_id')
            ->count();
    }

    public function getGuestOrders()
    {
        return $this->model
            ->excludeWaitingPayment()
            ->dateRangeFilter()
            ->whereNull('user_id')
            ->get();
    }

    public function getLatestGuestOrder(string $email)
    {
        return $this->model
            ->excludeWaitingPayment()
            ->dateRangeFilter()
            ->whereNull('user_id')
            ->where('email', $email)
            ->latest()
            ->first();
    }

    public function deleteExpiredOrders(\DateTime|string $timestamp)
    {
        return $this->model
            ->where('status', OrderStatus::EXPIRED->value)
            ->where('created_at', '<=', $timestamp)
            ->delete();
    }

    public function getUnpaidOrders(\DateTime|string $timestamp)
    {
        return $this->model
            ->where('status', OrderStatus::WAITING_PAYMENT->value)
            ->where('created_at', '<=', $timestamp)
            ->get();
    }
}

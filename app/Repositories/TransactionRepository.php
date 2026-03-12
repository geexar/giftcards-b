<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\User;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function getByReferenceId(string $referenceId): ?Transaction
    {
        return $this->model->where('reference_id', $referenceId)->first();
    }

    public function getByTransactionNo(string $transactionNo): ?Transaction
    {
        return $this->model->where('transaction_no', $transactionNo)->first();
    }

    public function getUserBalance(string $userId): float
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::SUCCESS)
            ->where('affects_wallet', true)
            ->sum('amount');
    }

    public function getPaginatedTransactions()
    {
        return $this->getTransactionsQuery()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getTransactionsQuery()
    {
        return $this->model
            ->with('actor', 'order', 'refund', 'paymentMethod')
            ->When(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_no', 'like', "%{$search}%")
                        ->orWhere('reference_id', 'like', "%{$search}%")
                        ->orwhereHas('actor', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(request('type'), fn($query) => $query->where('type', request('type')))
            ->when(request('actor_type'), function ($query, $actorType) {
                if ($actorType == 'system') $query->whereNull('actor_type');
                elseif ($actorType == 'user') $query->where('actor_type', User::class);
                elseif ($actorType == 'admin') $query->where('actor_type', Admin::class);
            })
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
            ->orderBy(request('sort_by') ?? 'created_at', request('sort_order') ?? 'desc');
    }

    public function getPaginatedTransactionsForUser()
    {
        return $this->model
            ->where('user_id', auth('user')->id())
            ->where('type', '!=', TransactionType::REFUND->value)
            ->with('order', 'paymentMethod')
            ->when(request('type'), fn($query) => $query->where('type', request('type')))
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

    public function totalProfits()
    {
        return $this->model
            ->dateRangeFilter()
            ->sum('actual_profit');
    }
}

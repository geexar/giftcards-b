<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getByUuid(string $uuid): ?User
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    public function getByUuidForUpdate(string $uuid): ?User
    {
        return $this->model->where('uuid', $uuid)->lockForUpdate()->first();
    }

    public function getByIdForUpdate(string $id): ?User
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }

    public function getByProviderId(string $provider, string $providerId)
    {
        return $this->model->whereHas('socialProviders', function ($query) use ($provider, $providerId) {
            $query->where('provider', $provider)->where('provider_id', $providerId);
        })->first();
    }

    public function getByPhone(string $country_code, string $phone)
    {
        return $this->model->where('country_code', $country_code)->where('phone', $phone)->first();
    }

    public function getByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function getPaginatedUsers()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                $query->where(function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%");
                });
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function invalidateUserData(User $user)
    {
        $user->update([
            'email' => getInvalidatedValue($user->email)
        ]);
    }

    public function totalUsersCount()
    {
        return $this->model
            ->dateRangeFilter()
            ->count();
    }

    public function usersWithOrdersCount()
    {
        return $this->model
            ->whereHas('orders', fn($query) => $query->excludeWaitingPayment())
            ->dateRangeFilter()
            ->count();
    }

    public function getUsers()
    {
        return $this->model
            ->dateRangeFilter()
            ->with('latestOrder')
            ->get();
    }

    public function getUsersCount()
    {
        return $this->model
            ->dateRangeFilter()
            ->count();
    }

    public function getReturningUsers()
    {
        return $this->model
            ->whereHas('orders', function ($query) {
                $query->excludeWaitingPayment()->dateRangeFilter();
            }, '>=', 2)
            ->with('latestOrder')
            ->get();
    }

    public function returningUsersCount()
    {
        return $this->model
            ->whereHas('orders', function ($query) {
                $query->excludeWaitingPayment()->dateRangeFilter();
            }, '>=', 2)
            ->count();
    }

    public function getActiveUsers(array $ids)
    {
        return $this->model
            ->where('is_active', 1)
            ->whereIn('id', $ids)
            ->get();
    }

    public function getActiveUsersCount()
    {
        return $this->model
            ->where('is_active', 1)
            ->count();
    }
}

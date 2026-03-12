<?php

namespace App\Repositories;

use App\Models\UsdtAddress;

class UsdtAddressRepository extends BaseRepository
{
    public function __construct(UsdtAddress $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedAddresses()
    {
        return $this->model
            ->when(request('search'), function ($query, $search) {
                $query->where('address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when(request('network_id'), function ($query, $networkId) {
                $query->where('network_id', $networkId);
            })
            ->with('media', 'user', 'network')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));;
    }

    public function getAddress(string $userId, string $networkId): ?UsdtAddress
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('network_id', $networkId)
            ->first();
    }

    public function getByAddress(string $address): ?UsdtAddress
    {
        return $this->model
            ->where('address', $address)
            ->first();
    }
}

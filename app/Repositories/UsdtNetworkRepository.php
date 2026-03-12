<?php

namespace App\Repositories;

use App\Models\UsdtNetwork;

class UsdtNetworkRepository extends BaseRepository
{
    public function __construct(UsdtNetwork $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        return $this->model->get();
    }

    public function getPaginatedNetworks()
    {
        return $this->model
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getActiveNetworks()
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getByIdentifier(string $identifier)
    {
        return $this->model->where('identifier', $identifier)->first();
    }
}

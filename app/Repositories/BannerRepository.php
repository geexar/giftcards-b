<?php

namespace App\Repositories;

use App\Enums\BannerType;
use App\Models\Banner;

class BannerRepository extends BaseRepository
{
    public function __construct(Banner $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedBanners()
    {
        return $this->model
            ->when(request('search'), function ($query, $name) {
                return $query->where('name', 'like', "%{$name}%");
            })
            ->when(request()->has('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getActiveBanners(BannerType $type)
    {
        return $this->model
            ->where("type", $type->value)
            ->where('is_active', true)
            ->with('media')
            ->get();
    }
}

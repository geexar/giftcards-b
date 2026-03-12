<?php

namespace App\Repositories;

use App\Models\ProductAvailabilitySubscription;

class ProductAvailabilitySubscriptionRepostiory extends BaseRepository
{
    public function __construct(ProductAvailabilitySubscription $model)
    {
        parent::__construct($model);
    }

    public function exists(array $attributes): bool
    {
        return $this->model->where($attributes)->exists();
    }
}

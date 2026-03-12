<?php

namespace App\Repositories;

use App\Enums\ProductSyncStatus;
use App\Models\ProductSyncLog;

class ProductSyncLogRepository extends BaseRepository
{
    public function __construct(ProductSyncLog $model)
    {
        parent::__construct($model);
    }

    public function inProgressSync()
    {
        return $this->model->where('status', ProductSyncStatus::IN_PROGRESS->value)->first();
    }
}

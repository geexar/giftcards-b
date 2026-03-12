<?php

namespace App\Repositories;

use App\Models\ProductSyncLog;

class ProductSyncLogRepository extends BaseRepository
{
    public function __construct(ProductSyncLog $model)
    {
        parent::__construct($model);
    }
}

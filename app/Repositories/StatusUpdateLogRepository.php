<?php

namespace App\Repositories;

use App\Models\StatusUpdateLog;

class StatusUpdateLogRepository extends BaseRepository
{
    public function __construct(StatusUpdateLog $model)
    {
        parent::__construct($model);
    }
}

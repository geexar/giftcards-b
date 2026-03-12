<?php

namespace App\Repositories;

use App\Models\Refund;

class RefundRepository extends BaseRepository
{
    public function __construct(Refund $model)
    {
        parent::__construct($model);
    }
}
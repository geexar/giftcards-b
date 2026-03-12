<?php

namespace App\Repositories;

use App\Models\UsdtNetwork;

class UsdtNetworkRepository extends BaseRepository
{
    public function __construct(UsdtNetwork $model)
    {
        parent::__construct($model);
    }
}

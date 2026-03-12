<?php

namespace App\Repositories;

use App\Models\UsdtAddress;

class UsdtAddressRepository extends BaseRepository
{
    public function __construct(UsdtAddress $model)
    {
        parent::__construct($model);
    }
}
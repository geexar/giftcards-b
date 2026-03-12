<?php

namespace App\Repositories;

use App\Models\ProductVariantValue;

class ProductVariantValueRepository extends BaseRepository
{
    public function __construct(ProductVariantValue $model)
    {
        parent::__construct($model);
    }
}
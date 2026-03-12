<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getBySKU(string $sku)
    {
        return $this->model->where('sku', $sku)->first();
    }
}

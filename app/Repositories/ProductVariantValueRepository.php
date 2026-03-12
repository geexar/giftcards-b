<?php

namespace App\Repositories;

use App\Models\ProductVariantValue;

class ProductVariantValueRepository extends BaseRepository
{
    public function __construct(ProductVariantValue $model)
    {
        parent::__construct($model);
    }

    public function getValueInVariant(string $value, int $variantId)
    {
        return $this->model
            ->where('product_variant_id', $variantId)
            ->where('value', $value)
            ->first();
    }

    public function getByIdForUpdate(string $id)
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }
}

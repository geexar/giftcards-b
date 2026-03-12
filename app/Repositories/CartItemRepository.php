<?php

namespace App\Repositories;

use App\Models\CartItem;

class CartItemRepository extends BaseRepository
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function deleteItemsWithProductId(string $productId)
    {
        $this->model->where('product_id', $productId)->delete();
    }

    public function deleteItemsWithVariantValueId(string $variantValueId)
    {
        $this->model->where('product_variant_value_id', $variantValueId)->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\Rating;

class RatingRepository extends BaseRepository
{
    public function __construct(Rating $model)
    {
        parent::__construct($model);
    }

    public function getAvgRatingForProduct(string $productId): ?float
    {
        return $this->model
            ->join('order_items', 'ratings.order_item_id', '=', 'order_items.id')
            ->where('order_items.product_id', $productId)
            ->whereNotNull('ratings.rating')
            ->avg('ratings.rating');
    }

    public function getAvgRatingForVariantValue(string $variantValueId): ?float
    {
        return $this->model
            ->join('order_items', 'ratings.order_item_id', '=', 'order_items.id')
            ->where('order_items.product_variant_value_id', $variantValueId)
            ->whereNotNull('ratings.rating')
            ->avg('ratings.rating');
    }
}

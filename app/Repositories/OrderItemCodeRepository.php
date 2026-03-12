<?php

namespace App\Repositories;

use App\Models\OrderItemCode;

class OrderItemCodeRepository extends BaseRepository
{
    public function __construct(OrderItemCode $model)
    {
        parent::__construct($model);
    }

    public function delete(string $orderItemId, string $codeId)
    {
        return $this->model
            ->where('order_item_id', $orderItemId)
            ->where('code_id', $codeId)
            ->delete();
    }
}

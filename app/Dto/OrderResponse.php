<?php

namespace App\Dto;

use App\Models\Order;

class OrderResponse
{
    public function __construct(
        public Order $order,
        public ?string $message = null,
        public ?string $payment_url = null
    ) {}
}

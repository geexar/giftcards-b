<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Services\User\OrderFulfillmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FulfillApiOrderItemJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private OrderItem $orderItem)
    {
        $this->onQueue('api_product_purchase');
    }

    public function handle(): void
    {
        $orderFulfillmentService = app(OrderFulfillmentService::class);

        $orderFulfillmentService->fulfillApiItem($this->orderItem);
    }
}

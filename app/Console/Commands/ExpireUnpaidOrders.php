<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\ProductSource;
use App\Repositories\OrderRepository;
use App\Services\User\OrderStockReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'app:expire-unpaid-orders';

    protected $description = 'Expire unpaid orders after period';

    public function handle()
    {
        $orderRepository = app(OrderRepository::class);
        $orderStockReservationService = app(OrderStockReservationService::class);

        // Unpaid orders older than 5 minutes
        $minutes = 5;
        $cutoff = now()->subMinutes($minutes);

        $unpaidOrders = $orderRepository->getUnpaidOrders($cutoff);

        foreach ($unpaidOrders as $order) {
            DB::transaction(function () use ($order, $orderStockReservationService) {
                $order->update(['status' => OrderStatus::EXPIRED]);

                foreach ($order->items as $orderItem) {
                    if ($orderItem->product->source == ProductSource::LOCAL) {
                        $orderStockReservationService->releaseStock($orderItem);
                    }
                }
            });
            $this->info('Expired order id: ' . $order->id);
        }
    }
}

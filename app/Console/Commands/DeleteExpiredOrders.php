<?php

namespace App\Console\Commands;

use App\Repositories\OrderRepository;
use Illuminate\Console\Command;

class DeleteExpiredOrders extends Command
{
    protected $signature = 'app:delete-expired-orders';

    protected $description = 'Delete unpaid orders older than specified time';

    public function handle()
    {
        $orderRepository = app(OrderRepository::class);

        // Calculate timestamp: 48 hours
        $cutoff = now()->subHours(48);

        // Delete expired orders older than the cutoff
        $deletedCount = $orderRepository->deleteExpiredOrders($cutoff);

        $this->info("Deleted $deletedCount expired orders older than 48 hours.");
    }
}

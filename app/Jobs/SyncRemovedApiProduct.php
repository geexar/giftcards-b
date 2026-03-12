<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSyncLog;
use App\Services\Admin\ProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncRemovedApiProduct implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Product $product,
        private ProductSyncLog $syncLog
    ) {
        $this->onQueue('sync_products');
    }

    /**
     * Execute the job - handles removing a single product.
     */
    public function handle(): void
    {
        $productSyncService = app(ProductSyncService::class);

        try {
            $productSyncService->handleRemovedProduct(
                $this->product,
                $this->syncLog
            );
        } catch (\Throwable $e) {
            Log::error('Product removal job failed', [
                'sync_log_id' => $this->syncLog->id,
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Increment counter and check if sync is complete
            $productSyncService->incrementSyncJobCounter($this->syncLog);
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSyncLog;
use App\Services\Admin\ProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncApiProduct implements ShouldQueue
{
    use Queueable;

    private ProductSyncService $productSyncService;

    public function __construct(
        private array $apiData,
        private ?Product $existingProduct,
        private Category $category,
        private ProductSyncLog $syncLog
    ) {
        $this->onQueue('sync_products');
        $this->productSyncService = app(ProductSyncService::class);
    }

    /**
     * Execute the job - handles adding or updating a single product.
     */
    public function handle(): void
    {
        try {
            if ($this->existingProduct) {
                // Update existing product
                $this->productSyncService->handleUpdatedProduct(
                    $this->existingProduct,
                    $this->apiData,
                    $this->syncLog
                );
            } else {
                // Add new product
                $this->productSyncService->handleAddedProduct(
                    $this->apiData,
                    $this->category,
                    $this->syncLog
                );
            }
        } catch (\Throwable $e) {
            Log::error('Product sync job failed', [
                'sync_log_id' => $this->syncLog->id,
                'product_id' => $this->apiData['productId'] ?? null,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Increment counter and check if sync is complete
            $this->productSyncService->incrementSyncJobCounter($this->syncLog);
        }
    }
}

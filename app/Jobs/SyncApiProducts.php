<?php

namespace App\Jobs;

use App\Enums\ProductSyncStatus;
use App\Models\ProductSyncLog;
use App\Services\Admin\CategorySyncService;
use App\Services\Admin\ProductSyncService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncApiProducts implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(private ProductSyncLog $productSyncLog)
    {
        $this->onQueue('sync_products');
    }

    public function handle(): void
    {
        $categorySyncService = app(CategorySyncService::class);

        $logId = $this->productSyncLog->id;

        try {
            $categoryJobs = $categorySyncService->gatherCategoryJobs();

            Bus::batch($categoryJobs)
                ->onQueue('sync_products')
                ->catch(function ($batch, $e) {
                    Log::warning("Category sync job failed: {$e->getMessage()}");
                })
                ->finally(function () use ($logId) {
                    $service = app(ProductSyncService::class);
                    $log = ProductSyncLog::find($logId);

                    if ($log) {
                        $service->dispatchProductJobs($log);
                    }
                })
                ->dispatch();
        } catch (\Throwable $e) {
            $this->productSyncLog->update(['status' => ProductSyncStatus::FAILED]);
            Log::error("SyncApiProducts job failed: {$e->getMessage()}");
            throw $e;
        }
    }
}

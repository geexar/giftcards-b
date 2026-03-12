<?php

namespace App\Jobs;

use App\Enums\CategoryType;
use App\Services\Admin\CategorySyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncApiCategories implements ShouldQueue
{
    use Queueable;

    private CategorySyncService $categorySyncService;

    public function __construct()
    {
        $this->onQueue('sync_products');
        $this->categorySyncService = app(CategorySyncService::class);
    }

    /**
     * Execute the job - dispatches individual category sync jobs by level.
     * Jobs execute in FIFO order, so parents exist when children run.
     */
    public function handle(): void
    {
        try {
            $apiCategories = $this->categorySyncService->loadApiCategories();

            // 1. Dispatch main categories first
            foreach ($apiCategories as $mainData) {
                SyncApiCategory::dispatch($mainData, null, CategoryType::MAIN);
            }

            // 2. Dispatch sub-categories
            foreach ($apiCategories as $mainData) {
                if (!empty($mainData['childs'])) {
                    foreach ($mainData['childs'] as $subData) {
                        SyncApiCategory::dispatch($subData, $mainData['id'], CategoryType::SUB);
                    }
                }
            }

            // 3. Dispatch sub-sub-categories
            foreach ($apiCategories as $mainData) {
                if (!empty($mainData['childs'])) {
                    foreach ($mainData['childs'] as $subData) {
                        if (!empty($subData['childs'])) {
                            foreach ($subData['childs'] as $subSubData) {
                                SyncApiCategory::dispatch($subSubData, $subData['id'], CategoryType::SUB_SUB);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Category sync orchestration failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

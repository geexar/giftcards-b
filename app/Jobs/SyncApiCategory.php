<?php

namespace App\Jobs;

use App\Enums\CategoryType;
use App\Repositories\CategoryRepository;
use App\Services\Admin\CategorySyncService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncApiCategory implements ShouldQueue
{
    use Queueable, Batchable;

    public function __construct(
        private array $data,
        private ?string $parentExternalId,
        private CategoryType $type
    ) {
        $this->onQueue('sync_products');
    }

    public function handle(): void
    {
        $categoryRepository = app(CategoryRepository::class);
        $categorySyncService = app(CategorySyncService::class);

        $parent = null;
        if ($this->parentExternalId) {
            $parent = $categoryRepository->getByExternalId($this->parentExternalId);
        }

        try {
            $categorySyncService->syncCategory($this->data, $parent, $this->type);
        } catch (\Throwable $e) {
            Log::error('Category sync job failed', [
                'category_external_id' => $this->data['externalId'] ?? null,
                'parent_external_id' => $this->parentExternalId,
                'error' => $e->getMessage(),
            ]);
            throw $e; // optional for batch failure tracking
        }
    }
}

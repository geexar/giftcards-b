<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductSyncLogItemResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ProductRepository;
use App\Repositories\ProductSyncLogItemRepository;
use App\Repositories\ProductSyncLogRepository;
use App\Services\Admin\ProductSyncService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductSyncController extends Controller implements HasMiddleware
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductSyncService $productSyncService,
        private ProductSyncLogRepository $productSyncLogRepository,
        private ProductSyncLogItemRepository $productSyncLogItemRepository,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show sync logs', only: ['syncLogs']),
            new Middleware('can:sync products', only: ['sync', 'isSyncInProgress']),
        ];
    }

    public function syncLogs()
    {
        $logItems = $this->productSyncLogItemRepository->getPaginatedItems();

        return success(new BaseCollection($logItems, ProductSyncLogItemResource::class));
    }

    public function sync()
    {
        $this->productSyncService->startSync(auth('admin')->user());

        return success(__("Product syncing started. we will notify you when it's done"));
    }

    public function notViewedApiProductsCount()
    {
        $count = $this->productRepository->getNotViewdApiProductsCount();

        return success(['count' => $count]);
    }

    public function isSyncInProgress()
    {
        $inProgressSync = (bool) $this->productSyncLogRepository->inProgressSync();

        return success(['in_progress' => $inProgressSync]);
    }
}

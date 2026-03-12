<?php

namespace App\Repositories;

use App\Models\ProductSyncLogItem;

class ProductSyncLogItemRepository extends BaseRepository
{
    public function __construct(ProductSyncLogItem $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedItems()
    {
        return $this->model
            ->when(request('from_date'), fn($query) => $query->whereDate('created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('created_at', '<=', request('to_date')))
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->with('product', 'syncLog.admin', 'media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }
}

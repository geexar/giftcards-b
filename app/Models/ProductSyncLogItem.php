<?php

namespace App\Models;

use App\Enums\ProductSyncLogItemStatus;
use Illuminate\Database\Eloquent\Model;

class ProductSyncLogItem extends Model
{
    protected $fillable = [
        'product_sync_log_id',
        'product_id',
        'changes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductSyncLogItemStatus::class,
        ];
    }
}

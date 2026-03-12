<?php

namespace App\Models;

use App\Enums\ProductSyncStatus;
use App\Enums\ProductSyncType;
use Illuminate\Database\Eloquent\Model;

class ProductSyncLog extends Model
{
    protected $fillable = [
        'type',
        'admin_id',
        'status',
        'total_jobs',
        'completed_jobs',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductSyncType::class,
            'status' => ProductSyncStatus::class,
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function items()
    {
        return $this->hasMany(ProductSyncLogItem::class, 'product_sync_log_id');
    }
}

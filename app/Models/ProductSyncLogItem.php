<?php

namespace App\Models;

use App\Enums\ProductNewImageStatus;
use App\Enums\ProductSyncLogItemStatus;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductSyncLogItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_sync_log_id',
        'product_id',
        'changes',
        'status',
        'new_image_available',
        'new_image_status'
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductSyncLogItemStatus::class,
            'changes' => 'array',
            'new_image_available' => 'boolean',
            'new_image_status' => ProductNewImageStatus::class,
        ];
    }

    public function syncLog()
    {
        return $this->belongsTo(ProductSyncLog::class, 'product_sync_log_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }
}

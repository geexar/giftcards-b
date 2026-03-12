<?php

namespace App\Http\Resources\Admin;

use App\Enums\ProductSyncLogItemStatus;
use App\Enums\ProductSyncType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSyncLogItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sync_type' => $this->syncLog->type->value,
            'status' => $this->status->value,
            'changes' => $this->when($this->status == ProductSyncLogItemStatus::UPDATED, $this->changes),
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name
            ],
            'image' => $this->when($this->status == ProductSyncLogItemStatus::UPDATED, fn() => [
                'new_image_available' => $this->new_image_available,
                'new_image' => $this->when($this->new_image_available, fn() => $this->image?->getUrl()),
            ]),
            'admin' => $this->when($this->syncLog->type == ProductSyncType::MANUAL, fn() => [
                'id' => $this->syncLog->admin->id,
                'name' => $this->syncLog->admin->name
            ]),
            'created_at' => formatDateTime($this->created_at)
        ];
    }
}

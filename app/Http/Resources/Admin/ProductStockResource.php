<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\ProductInventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'variant' => $this->when($this->has_variants, fn() => [
                'name' => $this->variant->name,
                'values' => ProductVariantValueStockResource::collection($this->variant->values),
            ]),
            'delivery_type' => $this->when(!$this->has_variants, fn() => $this->delivery_type->value),
            'quantity' => $this->total_stock,
            'status' => app(ProductInventoryService::class)->getStockStatus($this->total_stock),
        ];
    }
}

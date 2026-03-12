<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->product_id,
            'name' => $this->product->name . ($this->variantValue ? ' - ' . $this->variantValue->value : ''),
            'source' => $this->product->source,
            'status' => $this->product->status,
            'quantity_sold' => (int) $this->quantity_sold,
            'revenue' => $this->revenue,
            'net_revenue' => $this->net_revenue,
            'total_profit' => $this->total_profit
        ];
    }
}

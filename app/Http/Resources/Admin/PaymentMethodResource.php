<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => __($this->name),
            'code' => $this->code,
            'is_active' => $this->is_active,
            'active_for_checkout' => $this->active_for_checkout,
            'active_for_top_up' => $this->active_for_top_up,
            'active_mode' => $this->active_mode,
        ];
    }
}

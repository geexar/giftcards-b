<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsdtNetworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'name' => $this->name,
            'fixed_fees' => $this->fixed_fees,
            'percentage_fees' => $this->percentage_fees,
            'is_active' => $this->is_active
        ];
    }
}

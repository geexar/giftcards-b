<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsdtAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'address' => $this->address,
            'network' => [
                'identifier' => $this->network->identifier,
                'name' => $this->network->name
            ],
            'created_at' => formatDateTime($this->created_at)
        ];
    }
}

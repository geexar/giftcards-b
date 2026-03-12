<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'pin_code' => $this->pin_code,
            'info_1' => $this->info_1,
            'info_2' => $this->info_2,
            'expiry_date' => $this->expiry_date
        ];
    }
}

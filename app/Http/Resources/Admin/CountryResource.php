<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.countries.show');

        return [
            'id' => $this->id,
            'name' => $showRoute ? $this->getTranslations('name') : $this->name,
            'code' => $this->code,
            'dial_code' => $this->dial_code,
            'flag' => $this->flag,
            'is_active' => $this->is_active,
        ];
    }
}

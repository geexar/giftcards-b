<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('user.static-pages.show');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'body' => $this->when($showRoute, $this->body),
            'updated_at' => $this->when($showRoute, formatDateTime($this->updated_at))
        ];
    }
}

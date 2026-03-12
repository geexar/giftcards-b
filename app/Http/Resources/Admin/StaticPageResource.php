<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.static-pages.show');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'body' => $this->when($showRoute, $this->getTranslations('body'))
        ];
    }
}

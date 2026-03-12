<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.articles.show');

        return [
            'id' => $this->id,
            'title' => $showRoute ? $this->getTranslations('title') : $this->title,
            'body' => $this->when($showRoute, $this->getTranslations('body')),
            'is_active' => $this->is_active,
            'image' => $this->image?->getUrl(),
        ];
    }
}

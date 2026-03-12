<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.categories.show');

        return [
            'id' => $this->id,
            'source' => $this->source->value,
            'name' => $showRoute ? $this->getTranslations('name') : $this->name,
            'type' => $this->type,
            'short_description' => $this->when($showRoute, $this->getTranslations('short_description')),
            'description' => $this->when($showRoute, $this->getTranslations('description')),
            'is_active' => $this->is_active,
            'is_promoted' => $this->when($showRoute, $this->is_promoted),
            'is_featured' => $this->when($showRoute, $this->is_featured),
            'is_trending' => $this->when($showRoute, $this->is_trending),
            'parent' => $this->when($showRoute, $this->parent ? app(CategoryService::class)->getCategoryHierarchy($this->parent) : null),
            'products_count' => $this->when($showRoute, fn() => $this->total_products_count),
            'image' => $this->when($showRoute, fn() => $this->image?->getUrl()),
        ];
    }
}

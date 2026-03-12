<?php

namespace App\Http\Resources\User;

use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image?->getUrl(),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'parent' => $this->parent ? app(CategoryService::class)->getCategoryHierarchy($this->parent) : null,
            'has_subcategories' => $this->childs()->exists(),
        ];
    }
}

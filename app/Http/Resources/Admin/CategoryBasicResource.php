<?php

namespace App\Http\Resources\Admin;

use App\Enums\CategoryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryBasicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image?->getUrl(),
            'has_subcategories' => $this->childs()->count() > 0,
            'childs' => $this->when($this->type == CategoryType::SUB, fn() => CategoryBasicResource::collection($this->childs)),
        ];
    }
}

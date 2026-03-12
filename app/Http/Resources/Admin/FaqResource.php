<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('admin.faqs.show');

        return [
            'id' => $this->id,
            'question' => $showRoute ? $this->getTranslations('question') : $this->question,
            'answer' => $showRoute ? $this->getTranslations('answer') : $this->answer,
            'is_active' => $this->is_active
        ];
    }
}

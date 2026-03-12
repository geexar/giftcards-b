<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showRoute = $request->routeIs('user.articles.show');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $showRoute ? $this->body : $this->getShortenedBody(),
            'image' => $this->image?->getUrl(),
            'updated_at' => formatDateTime($this->updated_at),
        ];
    }

    private function getShortenedBody(): string
    {
        $plainText = preg_replace('/\s+/', ' ', strip_tags($this->body));

        return Str::limit($plainText, 100);
    }
}

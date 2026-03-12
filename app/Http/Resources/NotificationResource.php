<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => formatDateTime($this->created_at),
            'is_read' => (bool) $this->read_at,
            'data' => $this->data['data'] ?? null
        ];
    }
}

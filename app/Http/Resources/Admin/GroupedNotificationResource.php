<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupedNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'sent_to_all' => (bool) $this->sent_to_all,
            'users' => $this->when(!$this->sent_to_all, fn() => UserBasicResource::collection($this->users)),
            'sent_count' => $this->sent_count,
            'created_at' => formatDateTime($this->created_at),
        ];
    }
}

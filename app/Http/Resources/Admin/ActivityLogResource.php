<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'actor_type' => $this->getActorType(),
            'actor' => $this->when($this->actor, fn() => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]),
            'description' => $this->description,
            'created_at' => formatDateTime($this->created_at)
        ];
    }

    public function getActorType()
    {
        if (is_null($this->actor_type)) {
            return 'system';
        }

        return strtolower(class_basename($this->actor_type));
    }
}

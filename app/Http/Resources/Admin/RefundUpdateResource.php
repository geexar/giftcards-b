<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundUpdateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'refund_update',
            'actor_type' => $this->getActorType(),
            'actor' => $this->when($this->actor, fn() => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]),
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'amount' => (string) $this->amount,
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

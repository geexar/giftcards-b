<?php

namespace App\Http\Resources\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusUpdateLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => get_class($this->model) == Order::class ? 'order_status_update' : 'order_item_status_update',
            'actor_type' => $this->getActorType(),
            'actor' => $this->when($this->actor, fn() => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ]),
            'item_name' => $this->when($this->model_type == OrderItem::class, $this->getItemName()),
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
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

    private function getItemName()
    {
        $name = $this->model->product?->name;

        if ($this->model?->variantValue) {
            $name .= ' - ' . $this->model->variantValue->value;
        }

        return $name;
    }
}

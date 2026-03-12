<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderNotesUpdateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'notes_update',
            'admin' => [
                'id' => $this->admin->id,
                'name' => $this->admin->name
            ],
            'content' => $this->content,
            'created_at' => formatDateTime($this->created_at)
        ];
    }
}

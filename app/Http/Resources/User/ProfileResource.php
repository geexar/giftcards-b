<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'image' => $this->image?->getUrl(),
            'has_password' => (bool) $this->password,
            'social_providers' => $this->socialProviders->pluck('provider')->toArray(),
            'apple_relay_email' => $this->when($this->appleProvider, fn() => $this->appleProvider->apple_relay_email)
        ];
    }
}

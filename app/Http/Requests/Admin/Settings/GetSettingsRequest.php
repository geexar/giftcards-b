<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class GetSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'group' => 'required|in:order_limits,inventory,markup_fee,contact_support,social_media,gift_cards',
        ];
    }
}

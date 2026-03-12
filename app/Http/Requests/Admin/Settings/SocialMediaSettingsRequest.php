<?php

namespace App\Http\Requests\Admin\Settings;

use App\Http\Requests\BaseFormRequest;

class SocialMediaSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'facebook' => ['required', 'string', 'url', 'max:255'],
            'x' => ['required', 'string', 'url', 'max:255'],
            'tiktok' => ['required', 'string', 'url', 'max:255'],
            'youtube' => ['required', 'string', 'url', 'max:255'],
            'snapchat' => ['required', 'string', 'url', 'max:255'],
            'linkedin' => ['required', 'string', 'url', 'max:255'],
        ];
    }
}
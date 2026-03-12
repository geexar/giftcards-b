<?php

namespace App\Http\Requests\Admin;

use App\Enums\BannerType;
use App\Http\Requests\BaseFormRequest;
use App\Rules\ValidMedia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class BannerRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('banner');

        return [
            'type' => ['required', new Enum(BannerType::class)],
            'name' => ['required', 'string', 'min:3', 'max:50', Rule::unique('banners', 'name')->ignore($id)],
            'link' => ['required', 'string', 'url', 'max:255'],
            'is_active' => [Rule::requiredIf((bool) $id), 'boolean'],
            'image' => [Rule::requiredIf((bool) !$id), new ValidMedia(['image'])]
        ];
    }
}

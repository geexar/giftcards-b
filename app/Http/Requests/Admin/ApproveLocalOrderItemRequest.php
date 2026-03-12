<?php

namespace App\Http\Requests\Admin;

use App\Repositories\OrderItemRepository;
use App\Rules\UniqueCodeHash;
use Illuminate\Foundation\Http\FormRequest;

class ApproveLocalOrderItemRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('order_item');
        $orderItemRepository = app(OrderItemRepository::class);
        $orderItem = $id ? $orderItemRepository->getById($id) : null;

        if (!$orderItem) {
            return [];
        }

        return [
            'codes' => ['required', 'array', 'min:1', 'max:' . $orderItem->quantity],
            'codes.*.code' => ['required', 'string', 'max:255', 'distinct', new UniqueCodeHash()],
            'codes.*.pin_code' => ['nullable', 'string', 'max:255'],
            'codes.*.info_1' => ['nullable', 'string', 'max:255'],
            'codes.*.info_2' => ['nullable', 'string', 'max:255'],
            'codes.*.expiry_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allCodes = [];

            foreach ($this->input('codes', []) as $i => $code) {
                $value = $code['code'] ?? null;
                if (!$value) continue;

                if (in_array($value, $allCodes, true)) {
                    $validator->errors()->add(
                        "codes.$i.code",
                        __("The code :code is duplicated.", ['code' => $value])
                    );
                } else {
                    $allCodes[] = $value;
                }
            }
        });
    }
}

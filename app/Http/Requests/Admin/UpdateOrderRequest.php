<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductSource;
use App\Http\Requests\BaseFormRequest;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Rules\UniqueCodeHash;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('order');
        $orderrepository = app(OrderRepository::class);
        $order = $id ? $orderrepository->getById($id) : null;

        if (!$order) {
            return [];
        }

        return [
            'items' => ['required', 'array'],

            'items.*.id' => ['required', 'exists:order_items,id'],
            'items.*.status' => ['required', 'in:approved,rejected'],

            // Rejection reason is required only when item is rejected
            'items.*.rejection_reason' => ['nullable', 'string', 'max:255'],

            // Codes are required only when item is approved and requires confirmation
            'items.*.codes' => ['nullable', 'array'],
            'items.*.codes.*.code' => ['required_with:items.*.codes', 'string', 'max:255'],
            'items.*.codes.*.pin_code' => ['nullable', 'string', 'max:255'],
            'items.*.codes.*.info_1' => ['nullable', 'string', 'max:255'],
            'items.*.codes.*.info_2' => ['nullable', 'string', 'max:255'],
            'items.*.codes.*.expiry_date' => ['nullable', 'date', 'after_or_equal:' . date('Y-m-d')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $orderItemRepository = app(OrderItemRepository::class);

            foreach ($this->input('items', []) as $itemIndex => $item) {
                $orderItem = $orderItemRepository->getById($item['id'] ?? null);

                if (!$orderItem) {
                    continue;
                }

                if (($item['status']) === 'rejected' && empty($item['rejection_reason'])) {
                    $validator->errors()->add(
                        "items.$itemIndex.rejection_reason",
                        __('Rejection reason is required when rejecting an item.')
                    );
                }

                // Approved items must have codes
                if ($item['status'] == 'approved' && $orderItem->product->source == ProductSource::LOCAL) {
                    if (empty($item['codes'])) {
                        $validator->errors()->add(
                            "items.$itemIndex.codes",
                            __('Codes are required when approving this item.')
                        );
                        continue;
                    }

                    // Max codes = quantity
                    if (count($item['codes']) > $orderItem->quantity) {
                        $validator->errors()->add(
                            "items.$itemIndex.codes",
                            __('Codes count exceeds item quantity.')
                        );
                    }

                    // Duplicate codes inside same item
                    $seen = [];
                    foreach ($item['codes'] as $codeIndex => $code) {
                        $value = $code['code'] ?? null;
                        if (!$value) {
                            continue;
                        }

                        if (in_array($value, $seen, true)) {
                            $validator->errors()->add(
                                "items.$itemIndex.codes.$codeIndex.code",
                                __('The code :code is duplicated.', ['code' => $value])
                            );
                        }

                        $seen[] = $value;
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'items.*.codes.*.expiry_date' => __('validation.attributes.expiry_date'),
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeliveryType;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use App\Rules\UniqueCodeHash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestockProductRequest extends FormRequest
{
    public function __construct(
        private ProductRepository $productRepo,
        private ProductVariantValueRepository $variantValueRepo
    ) {}

    public function rules(): array
    {
        $id = $this->route('inventory_product');

        $product = $this->productRepo->getById($id);
        $hasVariants = (bool) $product->has_variants;

        $rules = [];

        // ----------------------------------
        // Invariant Product (NO VARIANTS)
        // ----------------------------------
        if (!$hasVariants) {
            $rules['quantity'] = [
                'integer',
                'min:0',
                'max:1000000',
                Rule::requiredIf(fn() => $this->input('delivery_type') === DeliveryType::REQUIRES_CONFIRMATION->value)
            ];

            $rules['codes'] = ['array', Rule::requiredIf(fn() => $this->input('delivery_type') === DeliveryType::INSTANT->value)];

            foreach ($this->input('codes', []) as $i => $code) {
                $rules["codes.$i.code"] = ['required', 'string', 'max:255', 'distinct', new UniqueCodeHash($id)];
                $rules["codes.$i.pin_code"] = ['nullable', 'string', 'max:255'];
                $rules["codes.$i.info_1"] = ['nullable', 'string', 'max:255'];
                $rules["codes.$i.info_2"] = ['nullable', 'string', 'max:255'];
                $rules["codes.$i.expiry_date"] = ['nullable', 'date', 'after_or_equal:' . date('Y-m-d')];
            }
        }

        // ----------------------------------
        // Variant Product
        // ----------------------------------
        if ($hasVariants) {
            $rules['variant_values'] = ['required', 'array', 'min:1'];

            foreach ($this->input('variant_values', []) as $i => $variantInput) {
                if (!isset($variantInput['id'])) {
                    continue;
                }

                $variantValue = $this->variantValueRepo->getById($variantInput['id']);
                $deliveryType = $variantValue->delivery_type;

                $rules["variant_values.$i.id"] = ['required', 'string', Rule::exists('product_variant_values', 'id')->whereNull('deleted_at')];

                $rules["variant_values.$i.quantity"] = [
                    'integer',
                    'min:0',
                    'max:1000000',
                    Rule::requiredIf(fn() => $deliveryType === DeliveryType::REQUIRES_CONFIRMATION),
                ];

                $rules["variant_values.$i.codes"] = ['array', Rule::requiredIf(fn() => $deliveryType === DeliveryType::INSTANT)];

                foreach ($variantInput['codes'] ?? [] as $ci => $code) {
                    $rules["variant_values.$i.codes.$ci.code"] = ['required', 'string', 'max:255', 'distinct', new UniqueCodeHash($id)];
                    $rules["variant_values.$i.codes.$ci.pin_code"] = ['nullable', 'string', 'max:255'];
                    $rules["variant_values.$i.codes.$ci.info_1"] = ['nullable', 'string', 'max:255'];
                    $rules["variant_values.$i.codes.$ci.info_2"] = ['nullable', 'string', 'max:255'];
                    $rules["variant_values.$i.codes.$ci.expiry_date"] = ['nullable', 'date', 'after_or_equal:' . date('Y-m-d')];
                }
            }
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allCodes = [];

            // Invariant codes
            if ($this->has('codes')) {
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
            }

            // Variant codes
            foreach ($this->input('variant_values', []) as $vIndex => $variant) {
                foreach ($variant['codes'] ?? [] as $cIndex => $code) {
                    $value = $code['code'] ?? null;
                    if (!$value) continue;

                    if (in_array($value, $allCodes, true)) {
                        $validator->errors()->add(
                            "variant_values.$vIndex.codes.$cIndex.code",
                            __("The code :code is duplicated.", ['code' => $value])
                        );
                    } else {
                        $allCodes[] = $value;
                    }
                }
            }
        });
    }
}

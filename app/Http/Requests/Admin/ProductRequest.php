<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Http\Requests\BaseFormRequest;
use App\Repositories\ProductRepository;
use App\Rules\UniqueCodeHash;
use App\Rules\UniqueVariantValue;
use App\Rules\ValidAmount;
use App\Rules\ValidAmountByType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('product');
        $product = $id ? app(ProductRepository::class)->getById($id)  : null;
        $isDrafted = $this->input('status', 'active') == 'drafted';
        $hasVariants = (bool) $this->input('has_variants');

        $rules = [];

        foreach (config('app.locales') as $locale) {
            $rules["name.$locale"] = ['required', 'string', 'max:255', Rule::unique('products', "name->{$locale}")->ignore($id)->whereNull('deleted_at')];
            $rules["short_description.$locale"] = ['string', 'max:300'];
            $rules["description.$locale"] = ['string', 'max:3000'];
        }

        $rules['category_id'] = ['required', 'exists:categories,id'];
        $rules['status'] = [Rule::requiredIf((bool) $id), new Enum(ProductStatus::class)];

        $rules['image'] = ['image', 'max:2048'];
        if (!$isDrafted && !$id) $rules['image'][] = 'required';

        $rules['apply_new_api_image'] = 'boolean';
        $rules['cancel_new_api_image'] = 'boolean';

        $rules['is_best_seller'] = ['boolean'];
        $rules['is_popular'] = ['boolean'];
        $rules['is_featured'] = ['boolean'];
        $rules['is_trending'] = ['boolean'];

        $rules['is_global'] = ['boolean', 'required'];
        $rules['selected_countries'] = ['array', 'min:1'];
        $rules['selected_countries.*'] = ['exists:countries,id'];

        $rules['has_custom_markup_fee'] = ['boolean', 'required'];
        $rules['custom_markup_fee_type'] = ['in:fixed,percentage', Rule::requiredIf(fn() => (bool)$this->input('has_custom_markup_fee'))];
        $rules['custom_markup_fee_value'] = [
            'numeric',
            new ValidAmount(),
            new ValidAmountByType($this->input('custom_markup_fee_type'), 1000000),
            Rule::requiredIf(fn() => (bool)$this->input('has_custom_markup_fee')),
        ];

        $rules['has_variants'] = ['boolean', 'required'];

        // Base price
        $rules['base_price'] = [new ValidAmount(), 'numeric', 'min:0', 'max:10000000'];
        if (!$isDrafted && !$hasVariants) $rules['base_price'][] = 'required';

        // Discounts
        $rules['has_discount'] = ['boolean', 'required'];
        $rules['discount_type'] = ['in:fixed,percentage', Rule::requiredIf(fn() => (bool)$this->input('has_discount'))];
        $rules['discount_value'] = [
            'numeric',
            new ValidAmount(),
            new ValidAmountByType($this->input('discount_type'), $this->input('base_price')),
            Rule::requiredIf(fn() => (bool)$this->input('has_discount')),
        ];

        // Delivery Type
        $rules['delivery_type'] = ['in:instant,requires_confirmation'];
        if (!$isDrafted && !$hasVariants) $rules['delivery_type'][] = 'required';

        $rules['marked_as_out_of_stock'] = ['boolean', 'required'];

        // Quantity
        $rules['quantity'] = ['integer', 'min:0', 'max:1000000'];
        if (
            !$isDrafted &&
            !$hasVariants &&
            $product?->source != ProductSource::API &&
            $this->input('delivery_type') == 'requires_confirmation'
        ) {
            $rules['quantity'][] = 'required';
        }

        // -------------------------------
        // Codes 
        // -------------------------------
        $rules['codes'] = ['array'];

        if (
            ($product?->source ?? ProductSource::LOCAL) == ProductSource::LOCAL &&
            !$isDrafted &&
            !$hasVariants &&
            $this->input('delivery_type') == 'instant'
        ) {
            $rules['codes'][] = 'required';
        }

        $codes = $this->input('codes', []);
        foreach ($codes as $i => $code) {
            $ignoreId = $code['id'] ?? null;

            $rules["codes.$i.id"] = ['nullable', 'integer', Rule::exists('codes', 'id')];
            $rules["codes.$i.code"] = ['required', 'string', 'max:255', new UniqueCodeHash($id, $ignoreId)];

            $rules["codes.$i.pin_code"] = ['nullable', 'string', 'max:255'];
            $rules["codes.$i.info_1"] = ['nullable', 'string', 'max:255'];
            $rules["codes.$i.info_2"] = ['nullable', 'string', 'max:255'];
            $rules["codes.$i.expiry_date"] = ['nullable', 'date'];

            if (!(bool) $id) {
                $rules["codes.$i.expiry_date"][] = 'after_or_equal:' . date('Y-m-d');
            }
        }

        $rules['codes_ids_to_remove'] = ['array'];
        $rules['codes_ids_to_remove.*'] = ['integer', Rule::exists('codes', 'id')];

        // -------------------------------
        // Variant Values (REQUIRED ALWAYS if NOT draft)
        // -------------------------------
        $rules['variant_name'] = ['nullable', 'string', 'max:255'];
        if (!$isDrafted && $hasVariants) $rules['variant_name'][] = 'required';

        $rules['variant_values'] = ['array'];
        if (!$isDrafted && $hasVariants) $rules['variant_values'][] = 'required';

        $variantValues = $this->input('variant_values', []);
        $variantId = $product?->variant?->id;

        foreach ($variantValues as $i => $variantValue) {

            $ignoreId = $variantValue['id'] ?? null;

            // ID validation
            $rules["variant_values.$i.id"] = ['nullable', 'integer', Rule::exists('product_variant_values', 'id')->whereNull('deleted_at')];

            // Value — REQUIRED ALWAYS (Option 2)
            $rules["variant_values.$i.value"] = ['string', 'max:255'];
            if (!$isDrafted && $hasVariants) {
                $rules["variant_values.$i.value"][] = new UniqueVariantValue($variantId, $ignoreId);
                $rules["variant_values.$i.value"][] = 'required';
            }

            // Localized descriptions
            foreach (config('app.locales') as $locale) {
                $rules["variant_values.$i.description.$locale"] = ['string', 'max:300'];
            }

            // Base price — REQUIRED ALWAYS
            $rules["variant_values.$i.base_price"] = [new ValidAmount(), 'numeric', 'min:0', 'max:1000000'];
            if (!$isDrafted && $hasVariants) {
                $rules["variant_values.$i.base_price"][] = 'required';
            }

            // Delivery type — REQUIRED ALWAYS
            $rules["variant_values.$i.delivery_type"] = ['in:instant,requires_confirmation'];
            if (!$isDrafted && $hasVariants) {
                $rules["variant_values.$i.delivery_type"][] = 'required';
            }

            // Discount
            $rules["variant_values.$i.has_discount"] = ['boolean', Rule::requiredIf($hasVariants)];

            $rules["variant_values.$i.discount_type"] = ['in:fixed,percentage', Rule::requiredIf(fn() => (bool) ($variantValue['has_discount'] ?? false) && $hasVariants)];

            $rules["variant_values.$i.discount_value"] = [
                'numeric',
                new ValidAmount(),
                new ValidAmountByType($variantValue['discount_type'] ?? null, $variantValue['base_price'] ?? null),
                Rule::requiredIf(fn() => (bool) ($variantValue['has_discount'] ?? false) && $hasVariants)
            ];

            // Visibility + quantity
            $rules["variant_values.$i.is_visible"] = ['boolean', Rule::requiredIf($hasVariants)];

            $rules["variant_values.$i.quantity"] = ['integer', 'min:0', 'max:1000000'];
            if (!$isDrafted && $hasVariants) {
                $rules["variant_values.$i.quantity"][] =
                    Rule::requiredIf(fn() => $variantValue['delivery_type'] ?? null === 'requires_confirmation');
            }

            $rules["variant_values.$i.marked_as_out_of_stock"] = ['boolean', Rule::requiredIf($hasVariants)];

            $rules["variant_values.$i.codes"] = ['array'];

            if (
                !$isDrafted &&
                ($variantValue['delivery_type'] ?? null) == 'instant'
            ) {
                $rules["variant_values.$i.codes"][] = 'required';
            }

            // Codes inside variant
            foreach ($variantValue['codes'] ?? [] as $ci => $code) {
                $ignoreId = $code['id'] ?? null;

                $rules["variant_values.$i.codes.$ci.id"] = ['nullable', 'integer', Rule::exists('codes', 'id')];
                $rules["variant_values.$i.codes.$ci.code"] = ['required', 'string', 'max:255', new UniqueCodeHash($id, $ignoreId)];
                $rules["variant_values.$i.codes.$ci.pin_code"] = ['nullable', 'string', 'max:255'];
                $rules["variant_values.$i.codes.$ci.info_1"] = ['nullable', 'string', 'max:255'];
                $rules["variant_values.$i.codes.$ci.info_2"] = ['nullable', 'string', 'max:255'];
                $rules["variant_values.$i.codes.$ci.expiry_date"] = ['nullable', 'date'];

                if (!(bool) $id) {
                    $rules["variant_values.$i.codes.$ci.expiry_date"][] = 'after_or_equal:' . date('Y-m-d');
                }
            }

            $rules["variant_values.$i.codes_ids_to_remove"] = ['array'];
            $rules["variant_values.$i.codes_ids_to_remove.*"] = ['integer', Rule::exists('codes', 'id')];
        }

        $rules['variant_values_ids_to_remove'] = ['array'];
        $rules['variant_values_ids_to_remove.*'] = ['integer', Rule::exists('product_variant_values', 'id')->whereNull('deleted_at')];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_best_seller' => $this->boolean('is_best_seller'),
            'is_popular' => $this->boolean('is_popular'),
            'is_featured' => $this->boolean('is_featured'),
            'is_trending' => $this->boolean('is_trending'),
            'is_global' => $this->boolean('is_global'),
            'has_variants' => $this->boolean('has_variants'),
            'has_discount' => $this->boolean('has_discount'),
            'has_custom_markup_fee' => $this->boolean('has_custom_markup_fee'),
            'mark_as_out_of_stock' => $this->boolean('mark_as_out_of_stock'),
            'apply_new_api_image' => $this->boolean('apply_new_api_image'),
            'cancel_new_api_image' => $this->boolean('cancel_new_api_image'),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $allCodes = [];
            $allVariantValues = [];

            // -------------------------------
            // Top-level codes
            // -------------------------------
            foreach ($this->input('codes', []) as $index => $code) {
                $value = $code['code'] ?? null;
                if ($value === null) continue;

                if (in_array($value, $allCodes)) {
                    $validator->errors()->add(
                        "codes.$index.code",
                        __("The code :code is duplicated.", ['code' => $value])
                    );
                } else {
                    $allCodes[] = $value;
                }
            }

            // -------------------------------
            // Variant values (value uniqueness)
            // -------------------------------
            foreach ($this->input('variant_values', []) as $vIndex => $variant) {
                $value = $variant['value'] ?? null;
                if ($value !== null) {
                    if (in_array($value, $allVariantValues)) {
                        $validator->errors()->add(
                            "variant_values.$vIndex.value",
                            __("The variant value :value is duplicated.", ['value' => $value])
                        );
                    } else {
                        $allVariantValues[] = $value;
                    }
                }

                // -------------------------------
                // Variant codes
                // -------------------------------
                foreach ($variant['codes'] ?? [] as $cIndex => $code) {
                    $codeValue = $code['code'] ?? null;
                    if ($codeValue === null) continue;

                    if (in_array($codeValue, $allCodes)) {
                        $validator->errors()->add(
                            "variant_values.$vIndex.codes.$cIndex.code",
                            __("The code :code is duplicated.", ['code' => $codeValue])
                        );
                    } else {
                        $allCodes[] = $codeValue;
                    }
                }
            }
        });
    }




    public function attributes(): array
    {
        return [
            'variant_values.*.value' => __('validation.attributes.value'),
            'variant_values.*.description.en' => __('validation.attributes.description.en'),
            'variant_values.*.description.ar' => __('validation.attributes.description.ar'),
            'variant_values.*.delivery_type' => __('validation.attributes.delivery_type'),
            'variant_values.*.base_price' => __('validation.attributes.base_price'),
            'variant_values.*.discount_type' => __('validation.attributes.discount_type'),
            'variant_values.*.discount_value' => __('validation.attributes.discount_value'),
            'variant_values.*.quantity' => __('validation.attributes.quantity'),
            'variant_values.*.expiry_date' => __('validation.attributes.expiry_date'),
        ];
    }
}

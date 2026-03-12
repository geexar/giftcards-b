<?php

namespace App\Imports;

use App\Enums\DeliveryType;
use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Rules\ValidAmount;
use App\Rules\ValidAmountByType;
use App\Services\Admin\DiscountService;

class ProductsImport implements ToModel, WithValidation, SkipsOnFailure, SkipsEmptyRows, WithHeadingRow
{
    use Importable, SkipsFailures;

    private function transformBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        $value = strtolower(trim($value ?? ''));
        return in_array($value, ['yes', '1', 'true', 'y']);
    }

    private function transformDeliveryType($value): ?DeliveryType
    {
        if (!$value) return null;

        $value = strtolower(trim($value));

        return match ($value) {
            'instant' => DeliveryType::INSTANT,
            'requires confirmation' => DeliveryType::REQUIRES_CONFIRMATION,
            default => null,
        };
    }

    public function model(array $row)
    {
        $discountService = app(DiscountService::class);

        // Resolve category ID
        $category = Category::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$row['category'] ?? 0])->first();

        $basePrice = $row['base_price'] ?? null;
        $finalPrice = $basePrice;

        if ($this->transformBoolean($row['has_discount'] ?? false) && $finalPrice !== null) {
            $discount = $discountService->calculateDiscount($finalPrice, $row['discount_type'], $row['discount_value']);
            $finalPrice -= $discount;
        }

        return new Product([
            'source'                 => ProductSource::LOCAL,
            'status'                 => ProductStatus::DRAFTED,
            'name'                   => [
                'ar' => $row['name_arabic'] ?? null,
                'en' => $row['name_english'] ?? null
            ],
            'short_description'      => [
                'ar' => $row['short_description_arabic'] ?? null,
                'en' => $row['short_description_english'] ?? null
            ],
            'description'            => [
                'ar' => $row['description_arabic'] ?? null,
                'en' => $row['description_english'] ?? null
            ],
            'category_id'            => $category?->id,

            // Set to false by default as they are removed from Excel
            'is_best_seller'         => false,
            'is_popular'             => false,
            'is_featured'            => false,
            'is_trending'            => false,
            'is_global'              => true,

            'has_custom_markup_fee'  => $this->transformBoolean($row['has_custom_markup_fee'] ?? false),
            'custom_markup_fee_type' => $row['custom_markup_fee_type'] ?? null,
            'custom_markup_fee_value' => $row['custom_markup_fee_value'] ?? null,

            'base_price'             => $basePrice,
            'final_price'            => $finalPrice,

            'has_discount'           => $this->transformBoolean($row['has_discount'] ?? false),
            'discount_type'          => $row['discount_type'] ?? null,
            'discount_value'         => $row['discount_value'] ?? null,

            'delivery_type'          => $this->transformDeliveryType($row['delivery_type'] ?? null),
        ]);
    }

    public function rules(): array
    {
        return [
            'name_english'              => ['nullable', 'string', 'max:255', 'unique:products,name->en'],
            'name_arabic'               => ['nullable', 'string', 'max:255', 'unique:products,name->ar'],
            'short_description_english' => ['nullable', 'string', 'max:300'],
            'short_description_arabic'  => ['nullable', 'string', 'max:300'],
            'description_english'       => ['nullable', 'string', 'max:1000'],
            'description_arabic'        => ['nullable', 'string', 'max:1000'],
            'category'                  => ['nullable', 'exists:categories,name->en'],

            'has_custom_markup_fee'     => ['required', 'string', 'in:Yes,No,yes,no'],
            'custom_markup_fee_type'    => ['required_if:has_custom_markup_fee,Yes,yes', 'nullable', 'in:fixed,percentage'],
            'custom_markup_fee_value'   => ['required_if:has_custom_markup_fee,Yes,yes', 'nullable', new ValidAmount(), new ValidAmountByType(), 'min:0'],

            'base_price'                => ['nullable', new ValidAmount(), 'min:0'],
            'has_discount'              => ['required', 'string', 'in:Yes,No,yes,no'],
            'discount_type'             => ['required_if:has_discount,Yes,yes', 'nullable', 'in:fixed,percentage'],
            'discount_value'            => ['required_if:has_discount,Yes,yes', 'nullable', new ValidAmount(), new ValidAmountByType(), 'min:0'],

            'delivery_type'             => ['nullable', 'in:instant,requires confirmation'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'name_english'              => 'Name (English)',
            'name_arabic'               => 'Name (Arabic)',
            'short_description_english' => 'Short Description (English)',
            'short_description_arabic'  => 'Short Description (Arabic)',
            'description_english'       => 'Description (English)',
            'description_arabic'        => 'Description (Arabic)',
            'category'                  => 'Category',
            'has_custom_markup_fee'     => 'Has Custom Markup Fee',
            'custom_markup_fee_type'    => 'Custom Markup Fee Type',
            'custom_markup_fee_value'   => 'Custom Markup Fee Value',
            'base_price'                => 'Base Price',
            'has_discount'              => 'Has Discount',
            'discount_type'             => 'Discount Type',
            'discount_value'            => 'Discount Value',
            'delivery_type'             => 'Delivery Type',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'category.exists' => __('The selected category does not exist.'),
        ];
    }
}

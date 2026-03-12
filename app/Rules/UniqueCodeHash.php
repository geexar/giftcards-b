<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Repositories\CodeRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCodeHash implements ValidationRule
{
    public function __construct(
        private ?int $productId = null, // productId may be null
        private ?int $ignoreCodeId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If no product ID, skip uniqueness over product
        if (!$this->productId) {
            return;
        }

        $codeRepository = app(CodeRepository::class);

        $codeHash = hash('sha256', $value);

        $existingCode = $codeRepository->getByCodeHash($codeHash);

        if (!$existingCode) {
            return;
        }

        // Ignore the current code when updating
        if ($this->ignoreCodeId && $existingCode->id == $this->ignoreCodeId) {
            return;
        }

        // Determine the product of the existing code
        $existingProductId = null;

        if ($existingCode->codeable instanceof Product) {
            $existingProductId = $existingCode->codeable->id;
        } elseif ($existingCode->codeable instanceof ProductVariantValue) {
            $existingProductId = $existingCode->codeable->product?->id;
        }

        // Fail only if existing code belongs to the same product
        if ($existingProductId == $this->productId) {
            $fail(__('validation.unique', ['attribute' => __('code')]));
        }
    }
}

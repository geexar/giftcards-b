<?php

namespace App\Rules;

use App\Repositories\ProductVariantValueRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueVariantValue implements ValidationRule
{
    public function __construct(
        private ?string $variantId,
        private ?string $ignoreId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->variantId) {
            return;
        }

        $repository = app(ProductVariantValueRepository::class);

        // Check if same value exists in this variant
        $existing = $repository->getValueInVariant($value, $this->variantId);

        if ($existing && $existing->id != $this->ignoreId) {
            $fail(__('this value already exists in this variant'));
        }
    }
}

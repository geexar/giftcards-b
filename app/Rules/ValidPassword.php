<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen((string) $value) < 8) {
            $fail(__('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]));
        }

        if (strlen((string) $value) > 40) {
            $fail(__('validation.max.string', ['attribute' => __('validation.attributes.password'), 'max' => 40]));
        }

        if (
            in_array(preg_match('/[a-z]/', (string) $value), [0, false], true) ||
            in_array(preg_match('/[A-Z]/', (string) $value), [0, false], true) ||
            in_array(preg_match('/\d/', (string) $value), [0, false], true) ||
            in_array(preg_match('/[\W_]/', (string) $value), [0, false], true)
        ) {
            $fail(__("password must contain uppercase letters, lowercase letters, numbers, and special characters"));
        }
    }
}

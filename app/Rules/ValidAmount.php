<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAmount implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Must be digits, optionally with a decimal part (e.g. 10, 10.5, 10.25)
        if (!preg_match('/^\d+(\.\d+)?$/', $value)) {
            $fail(__("not a valid amount"));
        }
    }
}

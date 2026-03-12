<?php

namespace App\Rules;

use App\Repositories\CountryRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class ValidPhone implements ValidationRule
{
    public function __construct(private ?string $country_code = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->country_code) {
            return;
        }

        // Get country by dial code, e.g., '+20'
        $country = app(CountryRepository::class)->getByDialCode($this->country_code);

        if (! $country) {
            // Skip validation if country not found
            return;
        }

        // Use Validator::make to validate the phone number
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => "required|phone:{$country->code}"]
        );

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAmountByType implements ValidationRule
{
    /**
     * The type of amount being validated.
     * Can be 'fixed' or 'percentage'.
     */
    protected ?string $type;

    /**
     * Constructor to set the type.
     *
     * @param string|null $type
     */
    public function __construct(?string $type = null, private ?float $maxFixed = null)
    {
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute The field name being validated.
     * @param mixed $value The value of the field.
     * @param Closure $fail Callback to report a validation failure.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If no type is provided, skip validation
        if (! $this->type) {
            return;
        }

        // Ensure the value is numeric
        if (! is_numeric($value)) {
            $fail("The {$attribute} must be a numeric value.");
            return;
        }

        // Convert to float for numeric comparisons
        $value = (float) $value;

        // Determine the maximum allowed value based on the type
        $max = match ($this->type) {
            'fixed' => $this->maxFixed ?? 1000000,     // Fixed amounts can go up to 1,000,000
            'percentage' => 100,    // Percentages can go up to 100%
            default => null,         // If type is unknown, skip validation
        };

        // If type is not recognized, skip validation
        if ($max == null) {
            return;
        }

        // Check that the value is within the valid range
        if ($this->type == 'fixed' && ($value < 0 || $value > $max)) {
            $fail(__("The value must be between 0 and :max.", ['max' => $max]));
        }

        if ($this->type == 'percentage' && ($value < 0.01 || $value > $max)) {
            $fail(__("The value must be between 0.01 and :max.", ['max' => $max]));
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LocalizedAlpha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // This rule is designed to work with individual locale fields, not arrays
        // The validation should be applied to each locale field separately
        // For example: name.en, name.ar, etc.

        // Skip empty values (let required rule handle this)
        if (empty($value)) {
            return;
        }

        // Extract locale from attribute (e.g., "name.en" -> "en" or "name_en" -> "en")
        $parts = explode('.', $attribute);
        $lastPart = end($parts);

        // Handle both formats: name.en and name_en
        if (str_contains($lastPart, '_')) {
            // Format: fields.0.name_en -> extract "en" from "name_en"
            $locale = substr($lastPart, -2);
        } else {
            // Format: name.en -> extract "en"
            $locale = $lastPart;
        }

        // Get configured locales
        $configuredLocales = config('app.locales', ['en', 'ar']);

        // Skip if locale is not configured
        if (! in_array($locale, $configuredLocales)) {
            return;
        }

        // Validate based on locale
        $valid = match ($locale) {
            'ar', 'ur' => preg_match('/^[\p{Arabic}\p{P}\p{S}\d\s\-_]+$/u', (string) $value),
            'en' => preg_match('/^[A-Za-z\p{P}\p{S}\d\s\-_]+$/u', (string) $value),
            default => preg_match('/^[\p{L}\p{P}\p{S}\d\s\-_]+$/u', (string) $value),
        };

        if ($valid === 0 || $valid === false) {
            // Get the attribute name translation using direct array access
            $validationAttributes = trans('validation.attributes');
            $attributeName = $validationAttributes[$attribute] ?? null;

            // If no specific translation found, try to find a pattern match for dynamic fields
            if ($attributeName === null) {
                // Handle dynamic field patterns like fields.*.name.en or fields.*.name_en
                if (
                    preg_match('/^fields\.\d+\.name\.(en|ar)$/', $attribute) ||
                    preg_match('/^fields\.\d+\.name_(en|ar)$/', $attribute)
                ) {
                    $attributeName = $locale === 'en' ? __('Name (English)') : __('Name (Arabic)');
                } else {
                    // Extract base field name (e.g., "name" from "name.en" or "name_en")
                    $baseField = $parts[0] ?? $attribute;
                    if (str_contains($baseField, '_')) {
                        $baseField = substr($baseField, 0, strrpos($baseField, '_'));
                    }
                    $attributeName = $validationAttributes[$baseField] ?? null;

                    // If still no translation, use a fallback
                    if ($attributeName === null) {
                        $attributeName = ucfirst(str_replace('_', ' ', $baseField));
                    }
                }
            }

            $fail(__("validation.custom.localized_alpha.{$locale}", [
                'attribute' => $attributeName,
            ]));
        }
    }
}

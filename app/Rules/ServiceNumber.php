<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ServiceNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Service number must start with NCS (case insensitive)
        if (!preg_match('/^NCS/i', $value)) {
            $fail('The :attribute must start with NCS (e.g., NCS50001).');
        }
    }
}


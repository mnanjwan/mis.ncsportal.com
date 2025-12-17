<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RsaPin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // RSA PIN: Usually 12 digits with PEN prefix
        if (!preg_match('/^PEN\d{12}$/', $value)) {
            $fail('The :attribute must be 12 digits with PEN prefix (e.g., PEN123456789012).');
        }
    }
}


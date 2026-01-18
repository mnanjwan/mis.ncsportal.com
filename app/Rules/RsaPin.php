<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RsaPin implements ValidationRule
{
    private string $prefix;
    private int $digits;

    public function __construct(?string $prefix = 'PEN', ?int $digits = 12)
    {
        $this->prefix = strtoupper((string)($prefix ?? 'PEN'));
        $this->digits = (int)($digits ?? 12);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = max(1, $this->digits);
        $prefix = $this->prefix !== '' ? $this->prefix : 'PEN';

        $pattern = '/^' . preg_quote($prefix, '/') . '\d{' . $digits . '}$/';
        if (!preg_match($pattern, (string)$value)) {
            $example = $prefix . str_repeat('0', $digits);
            $fail("The :attribute must be {$prefix} followed by {$digits} digits (e.g., {$example}).");
        }
    }
}


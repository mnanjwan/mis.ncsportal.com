<?php

namespace App\Helpers;

class ServiceNumberHelper
{
    /**
     * Generate a unique service number
     * Format: 5 digits (e.g., 57616)
     */
    public static function generate(): string
    {
        do {
            $serviceNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $exists = \App\Models\Officer::where('service_number', $serviceNumber)->exists();
        } while ($exists);

        return $serviceNumber;
    }

    /**
     * Validate service number format
     */
    public static function validate(string $serviceNumber): bool
    {
        return preg_match('/^\d{5}$/', $serviceNumber) === 1;
    }
}


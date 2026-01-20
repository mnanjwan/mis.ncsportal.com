<?php

namespace App\Services;

use App\Models\Quarter;

class QuarterAddressFormatter
{
    /**
     * Format a quarter into the officer's "quartered address" string.
     * Requirement: quarter_number + quarter_type (if present).
     */
    public static function format(?Quarter $quarter): string
    {
        if (!$quarter) {
            return '';
        }

        $number = trim((string) ($quarter->quarter_number ?? ''));
        $type = trim((string) ($quarter->quarter_type ?? ''));

        if ($number === '') {
            return '';
        }

        return $type !== '' ? "{$number} - {$type}" : $number;
    }
}


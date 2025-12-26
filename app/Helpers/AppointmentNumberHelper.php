<?php

namespace App\Helpers;

class AppointmentNumberHelper
{
    /**
     * Determine appointment number prefix (CDT or RCT) based on rank and GL level
     * 
     * Rules:
     * - GL 08 and above → CDT
     * - GL 07 and below → RCT
     * 
     * Specific rank mappings:
     * - CGC (GL 18) → CDT
     * - DCG (GL 17) → CDT
     * - ACG (GL 16) → CDT
     * - CC (GL 15) → CDT
     * - DC (GL 14) → CDT
     * - AC (GL 13) → CDT
     * - CSC (GL 12) → CDT
     * - SC (GL 11) → CDT
     * - DSC (GL 10) → CDT
     * - ASC I (GL 09) → CDT
     * - ASC II (GL 08) → CDT
     * - ASC II (GL 07 and below) → RCT
     * - IC (GL 07 and below) → RCT
     * - IC (GL 08 and above) → CDT
     * - AIC (GL 06) → RCT
     * - CA I (GL 05) → RCT
     * - CA II (GL 04) → RCT
     * - CA III (GL 03) → RCT
     * 
     * @param string $rank The substantive rank (e.g., "ASC II", "IC", "AIC", "DSC", "CSC", "CA I", etc.)
     * @param string|null $glLevel The salary grade level (e.g., "GL 08", "GL 07")
     * @return string Either "CDT" or "RCT"
     */
    public static function getPrefix(string $rank, ?string $glLevel = null): string
    {
        $rank = strtoupper(trim($rank));
        $glLevel = $glLevel ? strtoupper(trim($glLevel)) : null;

        // Extract numeric GL level if provided (e.g., "GL 08" -> 8, "08" -> 8)
        $glNumber = null;
        if ($glLevel) {
            preg_match('/(\d+)/', $glLevel, $matches);
            if (!empty($matches[1])) {
                $glNumber = (int) $matches[1];
            }
        }

        // Highest ranks - Always CDT (GL 13+)
        if (str_contains($rank, 'CGC')) {
            return 'CDT'; // GL 18
        }
        if (str_contains($rank, 'DCG')) {
            return 'CDT'; // GL 17
        }
        if (str_contains($rank, 'ACG')) {
            return 'CDT'; // GL 16
        }
        if (str_contains($rank, 'CC') && !str_contains($rank, 'CGC') && !str_contains($rank, 'DCG') && !str_contains($rank, 'ACG')) {
            return 'CDT'; // GL 15
        }
        if (str_contains($rank, 'DC') && !str_contains($rank, 'DCG')) {
            return 'CDT'; // GL 14
        }
        if (str_contains($rank, 'AC') && !str_contains($rank, 'ACG') && !str_contains($rank, 'AIC') && !str_contains($rank, 'CA')) {
            return 'CDT'; // GL 13
        }

        // Superintendent ranks - Always CDT (GL 10-12)
        if (str_contains($rank, 'CSC')) {
            return 'CDT'; // GL 12
        }
        if (str_contains($rank, 'SC') && !str_contains($rank, 'CSC') && !str_contains($rank, 'DSC')) {
            return 'CDT'; // GL 11
        }
        if (str_contains($rank, 'DSC')) {
            return 'CDT'; // GL 10
        }

        // Assistant Superintendent ranks - GL dependent
        if (str_contains($rank, 'ASC I')) {
            return 'CDT'; // GL 09 (always CDT)
        }
        if (str_contains($rank, 'ASC II') || str_contains($rank, 'ASCII')) {
            if ($glNumber !== null) {
                return $glNumber >= 8 ? 'CDT' : 'RCT';
            }
            // Default: ASC II is typically GL 08, so CDT
            return 'CDT';
        }

        // Inspector ranks - GL dependent
        if (str_contains($rank, 'IC') && !str_contains($rank, 'AIC')) {
            if ($glNumber !== null) {
                return $glNumber <= 7 ? 'RCT' : 'CDT';
            }
            // Default: IC is typically GL 07, so RCT
            return 'RCT';
        }

        // Lower ranks - Always RCT (GL 06 and below)
        if (str_contains($rank, 'AIC')) {
            return 'RCT'; // GL 06
        }
        if (str_contains($rank, 'CA I')) {
            return 'RCT'; // GL 05
        }
        if (str_contains($rank, 'CA II')) {
            return 'RCT'; // GL 04
        }
        if (str_contains($rank, 'CA III')) {
            return 'RCT'; // GL 03
        }

        // Fallback: Use GL level if provided
        if ($glNumber !== null) {
            return $glNumber >= 8 ? 'CDT' : 'RCT';
        }

        // Final fallback: If rank contains "ASC" or "ASSISTANT" (without CA), use CDT
        // Otherwise default to RCT
        if (str_contains($rank, 'ASC') || (str_contains($rank, 'ASSISTANT') && !str_contains($rank, 'CA'))) {
            return 'CDT';
        }

        // Default fallback
        return 'RCT';
    }

    /**
     * Generate next appointment number for a given prefix
     * 
     * @param string $prefix The prefix (CDT or RCT)
     * @return string The next appointment number (e.g., CDT00001, RCT00002)
     */
    public static function generateNext(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));
        
        // Get last appointment number with this prefix
        $lastAppointment = \App\Models\Officer::where('appointment_number', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(appointment_number, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
            ->value('appointment_number');

        $counter = 1;
        if ($lastAppointment) {
            preg_match('/(\d+)$/', $lastAppointment, $matches);
            if (!empty($matches[1])) {
                $counter = (int) $matches[1] + 1;
            }
        }

        return $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get appointment number prefix for an officer based on their rank and GL level
     * 
     * @param \App\Models\Officer $officer
     * @return string Either "CDT" or "RCT"
     */
    public static function getPrefixForOfficer(\App\Models\Officer $officer): string
    {
        return self::getPrefix(
            $officer->substantive_rank ?? '',
            $officer->salary_grade_level ?? null
        );
    }
}


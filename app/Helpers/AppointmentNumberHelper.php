<?php

namespace App\Helpers;

class AppointmentNumberHelper
{
    /**
     * Determine appointment number prefix (CDT or RCT) based on rank and GL level
     * 
     * Rules:
     * - ASC II GL 08 and above → CDT
     * - IC GL 07 and below → RCT
     * - AIC → RCT
     * - DSC → CDT
     * 
     * @param string $rank The substantive rank (e.g., "ASC II", "IC", "AIC", "DSC")
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

        // ASC II GL 08 and above → CDT
        if (str_contains($rank, 'ASC II') || str_contains($rank, 'ASCII')) {
            if ($glNumber !== null && $glNumber >= 8) {
                return 'CDT';
            }
            // If GL level not provided but rank is ASC II, default to CDT
            if ($glNumber === null && (str_contains($rank, 'ASC II') || str_contains($rank, 'ASCII'))) {
                return 'CDT';
            }
        }

        // IC GL 07 and below → RCT
        if (str_contains($rank, 'IC') && !str_contains($rank, 'AIC')) {
            if ($glNumber !== null && $glNumber <= 7) {
                return 'RCT';
            }
            // If GL level not provided but rank is IC, default to RCT
            if ($glNumber === null) {
                return 'RCT';
            }
        }

        // AIC → RCT
        if (str_contains($rank, 'AIC')) {
            return 'RCT';
        }

        // DSC → CDT
        if (str_contains($rank, 'DSC')) {
            return 'CDT';
        }

        // Default: If rank contains "ASC" or similar high ranks, use CDT
        // Otherwise default to RCT
        if (str_contains($rank, 'ASC') || str_contains($rank, 'ASSISTANT')) {
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


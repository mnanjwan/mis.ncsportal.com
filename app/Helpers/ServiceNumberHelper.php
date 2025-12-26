<?php

namespace App\Helpers;

class ServiceNumberHelper
{
    /**
     * Get the last service number for a specific rank
     * 
     * @param string $rank The rank name
     * @return string|null The last service number (e.g., NCS65000) or null if none exists
     */
    public static function getLastServiceNumberForRank(string $rank): ?string
    {
        // Get officers with this rank who have service numbers
        $lastServiceNumber = \App\Models\Officer::where('substantive_rank', $rank)
            ->whereNotNull('service_number')
            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
            ->value('service_number');

        return $lastServiceNumber;
    }

    /**
     * Generate next service number for a specific rank
     * 
     * @param string $rank The rank name
     * @param int|null $startFrom Optional starting number (if not provided, uses last + 1)
     * @return string The next service number (e.g., NCS65001)
     */
    public static function generateNextForRank(string $rank, ?int $startFrom = null): string
    {
        if ($startFrom !== null) {
            $nextNumber = $startFrom;
        } else {
            $lastServiceNumber = self::getLastServiceNumberForRank($rank);
            
            if ($lastServiceNumber) {
                // Extract numeric part (e.g., NCS65000 -> 65000)
                preg_match('/(\d+)$/', $lastServiceNumber, $matches);
                if (!empty($matches[1])) {
                    $nextNumber = (int) $matches[1] + 1;
                } else {
                    $nextNumber = 1;
                }
            } else {
                // No existing service numbers for this rank, start from 1
                // But we need to check global last number to avoid conflicts
                $globalLast = \App\Models\Officer::whereNotNull('service_number')
                    ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
                    ->value('service_number');
                
                if ($globalLast) {
                    preg_match('/(\d+)$/', $globalLast, $matches);
                    $nextNumber = !empty($matches[1]) ? (int) $matches[1] + 1 : 1;
                } else {
                    $nextNumber = 1;
                }
            }
        }

        // Check if service number already exists, increment if needed
        do {
            $serviceNumber = 'NCS' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $exists = \App\Models\Officer::where('service_number', $serviceNumber)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $serviceNumber;
    }

    /**
     * Validate service number format
     */
    public static function validate(string $serviceNumber): bool
    {
        return preg_match('/^NCS\d{5}$/i', $serviceNumber) === 1;
    }

    /**
     * Group training results by rank
     * 
     * @param \Illuminate\Support\Collection $results
     * @return array Array grouped by rank
     */
    public static function groupByRank($results): array
    {
        return $results->groupBy('rank')->toArray();
    }
}

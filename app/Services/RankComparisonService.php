<?php

namespace App\Services;

use App\Models\Officer;

class RankComparisonService
{
    /**
     * Rank hierarchy mapping (higher number = higher rank)
     * Based on NCS rank structure from Manning Level Request
     * Order: Highest (CGC) to Lowest (CA III)
     */
    private static $rankHierarchy = [
        'CGC' => 18,           // Comptroller General of Customs GL18
        'DCG' => 17,           // Deputy Comptroller General of Customs GL17
        'ACG' => 16,           // Assistant Comptroller General of Customs GL16
        'CC' => 15,            // Comptroller of Customs GL15
        'DC' => 14,            // Deputy Comptroller of Customs GL14
        'AC' => 13,            // Assistant Comptroller of Customs GL13
        'CSC' => 12,           // Chief Superintendent of Customs GL12
        'SC' => 11,            // Superintendent of Customs GL11
        'DSC' => 10,           // Deputy Superintendent of Customs GL10
        'ASC I' => 9,          // Assistant Superintendent of Customs Grade I GL09
        'ASC II' => 8,         // Assistant Superintendent of Customs Grade II GL08
        'IC' => 7,             // Inspector of Customs GL07
        'AIC' => 6,            // Assistant Inspector of Customs GL06
        'CA I' => 5,           // Customs Assistant I GL05
        'CA II' => 4,          // Customs Assistant II GL04
        'CA III' => 3,         // Customs Assistant III GL03
    ];

    /**
     * Compare ranks of two officers
     * 
     * @param int $officer1Id First officer ID
     * @param int $officer2Id Second officer ID
     * @return int Returns:
     *   -1 if officer1 rank is lower than officer2
     *    0 if ranks are equal
     *    1 if officer1 rank is higher than officer2
     */
    public function compareRanks($officer1Id, $officer2Id): int
    {
        $officer1 = Officer::find($officer1Id);
        $officer2 = Officer::find($officer2Id);

        if (!$officer1 || !$officer2) {
            throw new \InvalidArgumentException('One or both officers not found');
        }

        $rank1 = $this->getRankLevel($officer1->substantive_rank);
        $rank2 = $this->getRankLevel($officer2->substantive_rank);

        if ($rank1 < $rank2) {
            return -1;
        } elseif ($rank1 > $rank2) {
            return 1;
        }

        return 0;
    }

    /**
     * Check if officer1 rank is higher or equal to officer2 rank
     * 
     * @param int $officer1Id First officer ID (the one being checked)
     * @param int $officer2Id Second officer ID (the reference)
     * @return bool
     */
    public function isRankHigherOrEqual($officer1Id, $officer2Id): bool
    {
        $comparison = $this->compareRanks($officer1Id, $officer2Id);
        return $comparison >= 0;
    }

    /**
     * Check if officer1 rank is higher than officer2 rank
     * 
     * @param int $officer1Id First officer ID (the one being checked)
     * @param int $officer2Id Second officer ID (the reference)
     * @return bool
     */
    public function isRankHigher($officer1Id, $officer2Id): bool
    {
        $comparison = $this->compareRanks($officer1Id, $officer2Id);
        return $comparison > 0;
    }

    /**
     * Mapping from full rank names to abbreviations
     * Based on Manning Level Request rank mapping
     */
    private static $rankMappingToAbbr = [
        'Comptroller General of Customs (CGC) GL18' => 'CGC',
        'Comptroller General' => 'CGC',
        'Deputy Comptroller General of Customs (DCG) GL17' => 'DCG',
        'Deputy Comptroller General' => 'DCG',
        'Assistant Comptroller General (ACG) of Customs GL 16' => 'ACG',
        'Assistant Comptroller General' => 'ACG',
        'Comptroller of Customs (CC) GL15' => 'CC',
        'Comptroller' => 'CC',
        'Deputy Comptroller of Customs (DC) GL14' => 'DC',
        'Deputy Comptroller' => 'DC',
        'Assistant Comptroller of Customs (AC) GL13' => 'AC',
        'Assistant Comptroller' => 'AC',
        'Chief Superintendent of Customs (CSC) GL12' => 'CSC',
        'Chief Superintendent' => 'CSC',
        'Superintendent of Customs (SC) GL11' => 'SC',
        'Superintendent' => 'SC',
        'Deputy Superintendent of Customs (DSC) GL10' => 'DSC',
        'Deputy Superintendent' => 'DSC',
        'Assistant Superintendent of Customs Grade I (ASC I) GL 09' => 'ASC I',
        'Assistant Superintendent Grade I' => 'ASC I',
        'Assistant Superintendent of Customs Grade II (ASC II) GL 08' => 'ASC II',
        'Assistant Superintendent Grade II' => 'ASC II',
        'Assistant Superintendent' => 'ASC I', // Default to ASC I if ambiguous
        'Inspector of Customs (IC) GL07' => 'IC',
        'Inspector' => 'IC',
        'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
        'Assistant Inspector' => 'AIC',
        'Customs Assistant I (CA I) GL05' => 'CA I',
        'Customs Assistant I' => 'CA I',
        'Customs Assistant II (CA II) GL04' => 'CA II',
        'Customs Assistant II' => 'CA II',
        'Customs Assistant III (CA III) GL03' => 'CA III',
        'Customs Assistant III' => 'CA III',
        'Customs Assistant' => 'CA I', // Default to CA I if ambiguous
    ];

    /**
     * Get rank level from rank name
     * 
     * @param string|null $rankName The rank name
     * @return int Rank level (0 if rank not found)
     */
    private function getRankLevel(?string $rankName): int
    {
        if (!$rankName) {
            return 0;
        }

        // Normalize rank name (remove extra spaces, convert to uppercase for comparison)
        $normalizedRank = trim($rankName);
        $upperRank = strtoupper($normalizedRank);

        // First, check exact match in hierarchy (uppercase) - this is fastest and most accurate
        if (isset(self::$rankHierarchy[$upperRank])) {
            return self::$rankHierarchy[$upperRank];
        }

        // Second, try to map full rank name to abbreviation
        if (isset(self::$rankMappingToAbbr[$normalizedRank])) {
            $abbr = self::$rankMappingToAbbr[$normalizedRank];
            if (isset(self::$rankHierarchy[$abbr])) {
                return self::$rankHierarchy[$abbr];
            }
        }

        // Try partial matching in mapping (case-insensitive)
        foreach (self::$rankMappingToAbbr as $fullName => $abbr) {
            if (stripos($normalizedRank, $fullName) !== false || stripos($fullName, $normalizedRank) !== false) {
                if (isset(self::$rankHierarchy[$abbr])) {
                    return self::$rankHierarchy[$abbr];
                }
            }
        }

        // Check partial matches in hierarchy (for variations)
        // Only match if the rank abbreviation appears as a complete word/substring
        foreach (self::$rankHierarchy as $rank => $level) {
            $upperRankKey = strtoupper($rank);
            // Check if rank key is contained in the rank name (e.g., "CGC" in "Comptroller General of Customs")
            // Or if rank name is contained in a mapped full name
            if (str_contains($upperRank, $upperRankKey)) {
                // Make sure it's not a false positive (e.g., "CC" matching "DC")
                // Check if the rank key length is at least 2 characters and appears as a word boundary
                if (strlen($upperRankKey) >= 2) {
                    // Check if it's a complete match or appears with word boundaries
                    if ($upperRank === $upperRankKey || 
                        preg_match('/\b' . preg_quote($upperRankKey, '/') . '\b/i', $normalizedRank)) {
                        return $level;
                    }
                } else {
                    // For single character matches, only allow exact match
                    if ($upperRank === $upperRankKey) {
                        return $level;
                    }
                }
            }
        }

        // If rank not found, return 0 (lowest)
        return 0;
    }

    /**
     * Get rank hierarchy mapping (for reference)
     * 
     * @return array
     */
    public static function getRankHierarchy(): array
    {
        return self::$rankHierarchy;
    }
}


<?php

namespace App\Services;

class PromotionService
{
    /**
     * Highest rank has the lowest order value.
     * This ordering is consistent with the sorting used elsewhere in the codebase.
     */
    private const RANK_ORDER = [
        'CGC' => 1,
        'DCG' => 2,
        'ACG' => 3,
        'CC' => 4,
        'DC' => 5,
        'AC' => 6,
        'CSC' => 7,
        'SC' => 8,
        'DSC' => 9,
        'ASC I' => 10,
        'ASC II' => 11,
        'IC' => 12,
        'AIC' => 13,
        'CA I' => 14,
        'CA II' => 15,
        'CA III' => 16,
    ];

    /**
     * Normalize rank string to a standard abbreviation where possible.
     */
    public function normalizeRankToAbbreviation(?string $rank): ?string
    {
        if (empty($rank)) {
            return $rank;
        }

        $rank = trim($rank);

        if (isset(self::RANK_ORDER[$rank])) {
            return $rank;
        }

        // Extract abbreviation from parentheses, e.g. "Inspector of Customs (IC) GL07"
        if (preg_match('/\(([A-Z\s]+)\)/', $rank, $matches)) {
            $abbr = trim($matches[1]);
            if (isset(self::RANK_ORDER[$abbr])) {
                return $abbr;
            }
        }

        // Fallback: case-insensitive partial matches
        foreach (array_keys(self::RANK_ORDER) as $abbr) {
            if (stripos($rank, $abbr) !== false) {
                return $abbr;
            }
        }

        return $rank;
    }

    /**
     * Rank to Grade Level mapping.
     */
    private const RANK_TO_GRADE_LEVEL = [
        'CGC' => 'GL 18',
        'DCG' => 'GL 17',
        'ACG' => 'GL 16',
        'CC' => 'GL 15',
        'DC' => 'GL 14',
        'AC' => 'GL 13',
        'CSC' => 'GL 12',
        'SC' => 'GL 11',
        'DSC' => 'GL 10',
        'ASC I' => 'GL 09',
        'ASC II' => 'GL 08',
        'IC' => 'GL 07',
        'AIC' => 'GL 06',
        'CA I' => 'GL 05',
        'CA II' => 'GL 04',
        'CA III' => 'GL 03',
    ];

    /**
     * Get the next rank (promotion) for the provided rank.
     * Returns null if rank is unknown or already at the top.
     */
    public function getNextRank(?string $currentRank): ?string
    {
        $abbr = $this->normalizeRankToAbbreviation($currentRank);
        if (empty($abbr) || !isset(self::RANK_ORDER[$abbr])) {
            return null;
        }

        $currentOrder = self::RANK_ORDER[$abbr];
        if ($currentOrder <= 1) {
            return null; // Already at top rank
        }

        $targetOrder = $currentOrder - 1;
        $next = array_search($targetOrder, self::RANK_ORDER, true);

        return $next === false ? null : (string) $next;
    }

    /**
     * Get the grade level for a given rank.
     * Returns null if rank is unknown.
     */
    public function getGradeLevelForRank(?string $rank): ?string
    {
        if (empty($rank)) {
            return null;
        }

        $abbr = $this->normalizeRankToAbbreviation($rank);
        
        return self::RANK_TO_GRADE_LEVEL[$abbr] ?? null;
    }
}


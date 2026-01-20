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
}


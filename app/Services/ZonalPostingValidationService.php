<?php

namespace App\Services;

use App\Models\Officer;
use App\Models\Command;
use App\Models\OfficerPosting;
use Carbon\Carbon;

class ZonalPostingValidationService
{
    /**
     * Get Zone Coordinator's zone
     */
    public function getZoneCoordinatorZone($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }
        
        $zoneCoordinatorRole = $user->roles()
            ->where('name', 'Zone Coordinator')
            ->wherePivot('is_active', true)
            ->first();
        
        if (!$zoneCoordinatorRole || !$zoneCoordinatorRole->pivot->command_id) {
            return null;
        }
        
        $command = Command::find($zoneCoordinatorRole->pivot->command_id);
        return $command ? $command->zone : null;
    }

    /**
     * Get all command IDs in Zone Coordinator's zone
     */
    public function getZoneCommandIds($user = null)
    {
        $zone = $this->getZoneCoordinatorZone($user);
        if (!$zone) {
            return [];
        }
        
        return Command::where('zone_id', $zone->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Check if command is in Zone Coordinator's zone
     */
    public function isCommandInZone($commandId, $user = null)
    {
        $zoneCommandIds = $this->getZoneCommandIds($user);
        return in_array($commandId, $zoneCommandIds);
    }

    /**
     * Check if officer is GL 07 or below
     */
    public function isOfficerGL07OrBelow($officerId)
    {
        $officer = Officer::find($officerId);
        if (!$officer) {
            return false;
        }
        
        $gradeLevel = $officer->salary_grade_level ?? '';
        
        // If no grade level, exclude
        if (empty($gradeLevel) || trim($gradeLevel) === '' || $gradeLevel === null) {
            return false;
        }
        
        $gradeLevel = trim($gradeLevel);
        
        // Extract all numeric digits from the grade level
        preg_match_all('/\d+/', $gradeLevel, $matches);
        if (empty($matches[0])) {
            // No numbers found - could be text only, exclude to be safe
            return false;
        }
        
        // Get the first/largest number (in case of formats like "GL07" or just "7")
        $numericValue = 0;
        foreach ($matches[0] as $match) {
            $num = (int)$match;
            if ($num > $numericValue) {
                $numericValue = $num;
            }
        }
        
        // CRITICAL: Must be exactly between 1 and 7 (GL 01 to GL 07)
        // Anything above 7 is excluded
        if ($numericValue < 1 || $numericValue > 7) {
            return false; // Above GL 07 or invalid
        }
        
        // If we extracted a number and it's between 1 and 7, allow it
        return true;
    }

    /**
     * Get minimum command duration in months for a rank/GL
     */
    public function getMinimumDurationForRank($gradeLevel)
    {
        $gradeLevel = $gradeLevel ?? '';
        
        // GL 07 (IC): 24 months
        if (preg_match('/GL?\s?0?7/i', $gradeLevel) || preg_match('/^7$/', $gradeLevel)) {
            return 24;
        }
        
        // GL 06 (AIC): 18 months
        if (preg_match('/GL?\s?0?6/i', $gradeLevel) || preg_match('/^6$/', $gradeLevel)) {
            return 18;
        }
        
        // GL 05 and below: 12 months
        return 12;
    }

    /**
     * Check if officer has completed minimum command duration
     */
    public function checkCommandDuration($officerId)
    {
        $officer = Officer::find($officerId);
        if (!$officer) {
            return false;
        }
        
        $gradeLevel = $officer->salary_grade_level ?? '';
        
        // Get minimum duration for this rank/GL
        $minMonths = $this->getMinimumDurationForRank($gradeLevel);
        
        // Get current posting
        $currentPosting = OfficerPosting::where('officer_id', $officerId)
            ->where('is_current', true)
            ->first();
        
        // If no current posting, allow the posting (no duration to check)
        if (!$currentPosting || !$currentPosting->posting_date) {
            return true; // No current posting found - allow posting
        }
        
        // Calculate duration
        $postingDate = Carbon::parse($currentPosting->posting_date);
        $monthsAtStation = $postingDate->diffInMonths(now());
        
        return $monthsAtStation >= $minMonths;
    }

    /**
     * Check manning level requirements
     * Note: This is a simplified check. If ManningLevel model exists, use it.
     * For now, we'll use a basic check based on current officer counts.
     */
    public function checkManningLevel($fromCommandId, $toCommandId, $officerId)
    {
        $officer = Officer::find($officerId);
        if (!$officer) {
            return false;
        }
        
        $rank = $officer->substantive_rank;
        $gradeLevel = $officer->salary_grade_level ?? '';
        
        // Check destination command - ensure it won't exceed capacity
        // Get current count of officers with same rank in destination command
        $destCount = Officer::where('present_station', $toCommandId)
            ->where('substantive_rank', $rank)
            ->where('is_active', true)
            ->count();
        
        // Basic check: destination command shouldn't exceed reasonable capacity
        // This is a simplified rule - can be enhanced with actual ManningLevel model
        // For now, we'll allow up to 20 officers of same rank (reasonable limit)
        if ($destCount >= 20) {
            return false; // Destination command is at capacity
        }
        
        // TODO: If ManningLevel model/table exists, use it for more accurate checks:
        // - Check minimum_required for source command
        // - Check maximum_allowed for destination command
        // - Check rank-specific manning levels
        
        return true; // All checks passed
    }

    /**
     * Get validation message for command duration
     */
    public function getCommandDurationMessage($officerId)
    {
        $officer = Officer::find($officerId);
        if (!$officer) {
            return 'Officer not found';
        }
        
        $gradeLevel = $officer->salary_grade_level ?? '';
        $minMonths = $this->getMinimumDurationForRank($gradeLevel);
        
        $currentPosting = OfficerPosting::where('officer_id', $officerId)
            ->where('is_current', true)
            ->first();
        
        // If no current posting, return null (skip duration check - this is allowed)
        if (!$currentPosting || !$currentPosting->posting_date) {
            return null;
        }
        
        $postingDate = Carbon::parse($currentPosting->posting_date);
        $monthsAtStation = $postingDate->diffInMonths(now());
        $yearsAtStation = floor($monthsAtStation / 12);
        $remainingMonths = $monthsAtStation % 12;
        
        if ($monthsAtStation < $minMonths) {
            $remaining = $minMonths - $monthsAtStation;
            return "Officer has served {$yearsAtStation} year(s) {$remainingMonths} month(s) at current command. Minimum required: {$minMonths} months. Remaining: {$remaining} month(s).";
        }
        
        return null; // Valid
    }

    /**
     * Get validation message for manning level
     */
    public function getManningLevelMessage($fromCommandId, $toCommandId, $officerId)
    {
        $officer = Officer::find($officerId);
        if (!$officer) {
            return 'Officer not found';
        }
        
        $rank = $officer->substantive_rank;
        
        $destCount = Officer::where('present_station', $toCommandId)
            ->where('substantive_rank', $rank)
            ->where('is_active', true)
            ->count();
        
        if ($destCount >= 20) {
            return "Destination command already has {$destCount} officer(s) of rank {$rank}. Maximum capacity reached.";
        }
        
        return null; // Valid
    }
}


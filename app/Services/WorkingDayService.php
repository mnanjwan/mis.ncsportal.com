<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class WorkingDayService
{
    /**
     * Check if a date is a working day (excluding weekends and holidays).
     */
    public function isWorkingDay(Carbon $date): bool
    {
        // Check if weekend (Saturday or Sunday)
        if ($date->isWeekend()) {
            return false;
        }

        // Check holidays configured by HRD in Holiday Settings
        $isHoliday = Holiday::where('date', $date->toDateString())->exists();
        if ($isHoliday) {
            return false;
        }

        return true;
    }

    /**
     * Count working days between two dates, inclusive.
     */
    public function workingDaysBetween($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $count = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($this->isWorkingDay($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Calculate end date based on duration and start date, skipping non-working days.
     */
    public function calculateEndDate($startDate, int $workingDays): Carbon
    {
        $current = Carbon::parse($startDate)->startOfDay();
        $count = 0;

        while ($count < $workingDays) {
            if ($this->isWorkingDay($current)) {
                $count++;
            }
            
            if ($count < $workingDays) {
                $current->addDay();
            }
        }

        return $current;
    }

    /**
     * Calculate the resume duty date (the first working day after leave ends).
     */
    public function calculateResumeDate($endDate): Carbon
    {
        $current = Carbon::parse($endDate)->addDay()->startOfDay();
        
        while (!$this->isWorkingDay($current)) {
            $current->addDay();
        }

        return $current;
    }
}

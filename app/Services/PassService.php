<?php

namespace App\Services;

use App\Models\LeavePassCriterion;
use App\Models\SystemSetting;
use Carbon\Carbon;

class PassService
{
    /**
     * Count working days (Monday–Friday) between two dates, inclusive.
     * Saturdays, Sundays and Holidays are excluded.
     */
    public function workingDaysBetween($startDate, $endDate): int
    {
        return app(WorkingDayService::class)->workingDaysBetween($startDate, $endDate);
    }

    /**
     * Parse numeric grade level from salary_grade_level string (e.g. "GL 07", "GL05").
     * Returns the single numeric value or 0 if none found.
     */
    public function parseGradeLevelNumeric(?string $gradeLevel): int
    {
        if ($gradeLevel === null || trim($gradeLevel) === '') {
            return 0;
        }

        preg_match_all('/\d+/', trim($gradeLevel), $matches);
        if (empty($matches[0])) {
            return 0;
        }

        $numericValue = 0;
        foreach ($matches[0] as $match) {
            $num = (int) $match;
            if ($num > $numericValue) {
                $numericValue = $num;
            }
        }

        return $numericValue;
    }

    /**
     * Get maximum pass working days allowed for a grade level.
     * GL 07 and above: 30; GL 04–06: 21; GL 03 and below (or unknown): 14.
     * Values can be overridden by system settings.
     */
    public function getPassMaxWorkingDaysForGradeLevel(?string $gradeLevel, ?string $rank = null): int
    {
        $criteria = app(LeavePassCriteriaService::class)
            ->getCriteriaForOfficer(LeavePassCriterion::TYPE_PASS, $gradeLevel, $rank);
        if ($criteria) {
            return (int) $criteria->max_duration_days;
        }

        $gl = $this->parseGradeLevelNumeric($gradeLevel);

        if ($gl >= 7) {
            return (int) $this->getSetting('pass_max_days_gl07_above', 30);
        }
        if ($gl >= 4 && $gl <= 6) {
            return (int) $this->getSetting('pass_max_days_gl04_06', 21);
        }

        return (int) $this->getSetting('pass_max_days_gl03_below', 14);
    }

    public function getPassCriteriaForGradeLevel(?string $gradeLevel, ?string $rank = null): ?LeavePassCriterion
    {
        return app(LeavePassCriteriaService::class)
            ->getCriteriaForOfficer(LeavePassCriterion::TYPE_PASS, $gradeLevel, $rank);
    }

    public function getPassMaxApplicationsForGradeLevel(?string $gradeLevel, ?string $rank = null): int
    {
        $criteria = $this->getPassCriteriaForGradeLevel($gradeLevel, $rank);
        if ($criteria && $criteria->max_times_per_year) {
            return (int) $criteria->max_times_per_year;
        }

        return 2;
    }

    public function getRequiredAnnualLeaveCountBeforePass(?string $gradeLevel, ?string $rank = null): int
    {
        $criteria = app(LeavePassCriteriaService::class)
            ->getCriteriaForOfficer(LeavePassCriterion::TYPE_ANNUAL_LEAVE, $gradeLevel, $rank);
        if ($criteria && $criteria->max_times_per_year) {
            return (int) $criteria->max_times_per_year;
        }

        return (int) $this->getSetting('annual_leave_max_applications', 2);
    }

    /**
     * Get system setting value or default.
     */
    protected function getSetting(string $key, $default): string
    {
        $setting = SystemSetting::where('setting_key', $key)->first();

        return $setting && $setting->setting_value !== null && $setting->setting_value !== ''
            ? $setting->setting_value
            : (string) $default;
    }
}

<?php

namespace App\Services;

use App\Models\LeavePassCriterion;
use App\Models\Officer;
use Carbon\Carbon;

class LeavePassCriteriaService
{
    public function resolveGradeBand(?string $salaryGradeLevel): string
    {
        $gl = app(PassService::class)->parseGradeLevelNumeric($salaryGradeLevel);

        if ($gl >= 7) {
            return LeavePassCriterion::BAND_GL07_ABOVE;
        }

        if ($gl >= 4) {
            return LeavePassCriterion::BAND_GL04_06;
        }

        return LeavePassCriterion::BAND_GL03_BELOW;
    }

    public function getCriteriaForOfficer(string $type, ?string $salaryGradeLevel, ?string $rank = null): ?LeavePassCriterion
    {
        if ($rank) {
            $byRank = LeavePassCriterion::where('type', $type)
                ->where('rank', $rank)
                ->first();
            if ($byRank) {
                return $byRank;
            }
        }

        $band = $this->resolveGradeBand($salaryGradeLevel);

        return LeavePassCriterion::where('type', $type)
            ->whereNull('rank')
            ->where('grade_band', $band)
            ->first();
    }

    public function requestedDaysForCriteria(string $durationType, int $workingDays, int $calendarDays): int
    {
        if ($durationType === LeavePassCriterion::DURATION_CALENDAR_DAYS) {
            return $calendarDays;
        }

        return $workingDays;
    }

    public function hasQualifiedServiceMonths(Officer $officer, int $qualificationMonths, string $asOfDate): bool
    {
        if ($qualificationMonths <= 0) {
            return true;
        }

        if (!$officer->date_of_first_appointment) {
            return false;
        }

        $firstAppointmentDate = Carbon::parse($officer->date_of_first_appointment)->startOfDay();
        $applicationDate = Carbon::parse($asOfDate)->startOfDay();

        return $firstAppointmentDate->diffInMonths($applicationDate, false) >= $qualificationMonths;
    }
}

<?php

namespace App\Services;

use App\Models\DutyRoster;
use App\Models\Officer;
use Illuminate\Support\Facades\DB;

class DutyRosterService
{
    /**
     * Check if an officer is OIC or 2IC in any approved duty roster for a given command and year
     * 
     * @param int $officerId The officer ID to check
     * @param int $commandId The command ID
     * @param int|null $year The year to check (null for current year)
     * @return bool
     */
    public function isOfficerOICOr2IC($officerId, $commandId, $year = null): bool
    {
        if ($year === null) {
            $year = date('Y');
        }

        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        return DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->where(function($query) use ($officerId, $startDate, $endDate) {
                $query->where(function($q) use ($officerId, $startDate, $endDate) {
                    // Check if officer is OIC and roster period overlaps with the year
                    $q->where('oic_officer_id', $officerId)
                      ->where(function($periodQuery) use ($startDate, $endDate) {
                          $periodQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                                     ->where('roster_period_end', '>=', $endDate);
                                     });
                      });
                })
                ->orWhere(function($q) use ($officerId, $startDate, $endDate) {
                    // Check if officer is 2IC and roster period overlaps with the year
                    $q->where('second_in_command_officer_id', $officerId)
                      ->where(function($periodQuery) use ($startDate, $endDate) {
                          $periodQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                                     ->where('roster_period_end', '>=', $endDate);
                                     });
                      });
                });
            })
            ->exists();
    }

    /**
     * Get list of officers who are OIC or 2IC in approved rosters for a command and year
     * 
     * @param int $commandId The command ID
     * @param int|null $year The year to check (null for current year)
     * @return \Illuminate\Support\Collection Collection of Officer models
     */
    public function getOICAnd2ICForCommand($commandId, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        // Get OIC officers
        $oicOfficerIds = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->whereNotNull('oic_officer_id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('roster_period_start', [$startDate, $endDate])
                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                     ->where('roster_period_end', '>=', $endDate);
                     });
            })
            ->pluck('oic_officer_id')
            ->unique();

        // Get 2IC officers
        $secondInCommandOfficerIds = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->whereNotNull('second_in_command_officer_id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('roster_period_start', [$startDate, $endDate])
                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                     ->where('roster_period_end', '>=', $endDate);
                     });
            })
            ->pluck('second_in_command_officer_id')
            ->unique();

        // Combine and get unique officer IDs
        $allOfficerIds = $oicOfficerIds->merge($secondInCommandOfficerIds)->unique();

        return Officer::whereIn('id', $allOfficerIds)->get();
    }

    /**
     * Get the role (OIC or 2IC) of an officer in a command for a given year
     * 
     * @param int $officerId The officer ID
     * @param int $commandId The command ID
     * @param int|null $year The year to check (null for current year)
     * @return string|null Returns 'OIC', '2IC', or null
     */
    public function getOfficerRoleInRoster($officerId, $commandId, $year = null): ?string
    {
        if ($year === null) {
            $year = date('Y');
        }

        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        // Check if OIC
        $isOIC = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->where('oic_officer_id', $officerId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('roster_period_start', [$startDate, $endDate])
                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                     ->where('roster_period_end', '>=', $endDate);
                     });
            })
            ->exists();

        if ($isOIC) {
            return 'OIC';
        }

        // Check if 2IC
        $is2IC = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->where('second_in_command_officer_id', $officerId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('roster_period_start', [$startDate, $endDate])
                     ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                     ->orWhere(function($overlapQuery) use ($startDate, $endDate) {
                         $overlapQuery->where('roster_period_start', '<=', $startDate)
                                     ->where('roster_period_end', '>=', $endDate);
                     });
            })
            ->exists();

        if ($is2IC) {
            return '2IC';
        }

        return null;
    }
}


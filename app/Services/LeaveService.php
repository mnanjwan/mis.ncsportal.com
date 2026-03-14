<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\Officer;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Check if officer can apply for leave
     */
    public function canApplyLeave(int $officerId, int $leaveTypeId, string $startDate, string $endDate): array
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $officer = Officer::findOrFail($officerId);

        $workingDayService = app(WorkingDayService::class);
        $numberOfDays = $workingDayService->workingDaysBetween($startDate, $endDate);

        // GL-based Annual Leave duration
        if ($leaveType->code === 'ANNUAL_LEAVE') {
            $glNumeric = app(PassService::class)->parseGradeLevelNumeric($officer->salary_grade_level);
            $maxDays = 14;
            if ($glNumeric >= 7) {
                $maxDays = 30;
            } elseif ($glNumeric >= 4) {
                $maxDays = 21;
            }

            if ($numberOfDays > $maxDays) {
                return [
                    'can_apply' => false,
                    'reason' => "For your Grade Level, maximum Annual Leave duration is {$maxDays} working days.",
                ];
            }

            // 6-Month Cooling Period
            $lastLeave = LeaveApplication::where('officer_id', $officerId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('status', 'APPROVED')
                ->whereYear('start_date', Carbon::parse($startDate)->year)
                ->latest('start_date')
                ->first();

            if ($lastLeave) {
                $lastEndDate = Carbon::parse($lastLeave->end_date);
                $newStartDate = Carbon::parse($startDate);
                if ($lastEndDate->diffInMonths($newStartDate) < 6) {
                    return [
                        'can_apply' => false,
                        'reason' => "You must wait at least 6 months after your previous leave within the same service year. Your last leave ended on {$lastEndDate->toDateString()}.",
                    ];
                }
            }

            // Check annual leave limits (max occurrences)
            $annualLeaveCount = LeaveApplication::where('officer_id', $officerId)
                ->where('leave_type_id', $leaveTypeId)
                ->whereYear('start_date', now()->year)
                ->where('status', 'APPROVED')
                ->count();

            if ($annualLeaveCount >= ($leaveType->max_occurrences_per_year ?? 2)) {
                return [
                    'can_apply' => false,
                    'reason' => 'Maximum annual leave applications reached for this year',
                ];
            }
        }

        // Generic max duration check for other leave types
        if ($leaveType->code !== 'ANNUAL_LEAVE' && $leaveType->max_duration_days && $numberOfDays > $leaveType->max_duration_days) {
            return [
                'can_apply' => false,
                'reason' => "Maximum duration for this leave type is {$leaveType->max_duration_days} days",
            ];
        }

        return ['can_apply' => true];
    }

    /**
     * Apply for leave
     */
    public function applyLeave(int $officerId, array $data): LeaveApplication
    {
        $numberOfDays = app(WorkingDayService::class)->workingDaysBetween($data['start_date'], $data['end_date']);

        $application = LeaveApplication::create([
            'officer_id' => $officerId,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'number_of_days' => $numberOfDays,
            'reason' => $data['reason'] ?? null,
            'expected_date_of_delivery' => $data['expected_date_of_delivery'] ?? null,
            'status' => 'PENDING',
        ]);

        // Notify staff officer
        $this->notifyStaffOfficer($application);

        return $application;
    }

    /**
     * Minute leave application
     */
    public function minuteLeave(LeaveApplication $application, int $staffOfficerId): void
    {
        $application->update([
            'status' => 'MINUTED',
            'minuted_at' => now(),
        ]);

        $application->approval()->create([
            'staff_officer_id' => $staffOfficerId,
            'approval_status' => 'MINUTED',
        ]);

        // Notify 2iC Unit Head
        $this->notifyDcAdmin($application);
    }

    /**
     * Approve leave application
     */
    public function approveLeave(LeaveApplication $application, int $dcAdminId, bool $approved, ?string $comments = null): void
    {
        if ($approved) {
            $application->update([
                'status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            $application->approval->update([
                'dc_admin_id' => $dcAdminId,
                'approval_status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            // Get Area Controller
            $areaController = $application->officer->presentStation?->areaController;
            if ($areaController) {
                $application->approval->update([
                    'area_controller_id' => $areaController->id,
                ]);
            }

            // Notify officer
            $this->notifyOfficer($application, 'Your leave application has been approved');
        } else {
            $application->update([
                'status' => 'REJECTED',
                'rejected_at' => now(),
                'rejection_reason' => $comments,
            ]);

            $application->approval->update([
                'dc_admin_id' => $dcAdminId,
                'approval_status' => 'REJECTED',
            ]);

            // Notify officer
            $this->notifyOfficer($application, 'Your leave application has been rejected');
        }
    }

    /**
     * Notify staff officer
     */
    private function notifyStaffOfficer(LeaveApplication $application): void
    {
        $staffOfficers = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'Staff Officer');
        })->whereHas('officer', function ($query) use ($application) {
            $query->where('present_station', $application->officer->present_station);
        })->get();

        foreach ($staffOfficers as $staffOfficer) {
            Notification::create([
                'user_id' => $staffOfficer->id,
                'notification_type' => 'LEAVE_APPLICATION',
                'title' => 'New Leave Application',
                'message' => "Officer {$application->officer->service_number} has applied for leave",
                'data' => ['leave_application_id' => $application->id],
            ]);
        }
    }

    /**
     * Notify 2iC Unit Head
     */
    private function notifyDcAdmin(LeaveApplication $application): void
    {
        $dcAdmins = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', '2iC Unit Head');
        })->whereHas('officer', function ($query) use ($application) {
            $query->where('present_station', $application->officer->present_station);
        })->get();

        foreach ($dcAdmins as $dcAdmin) {
            Notification::create([
                'user_id' => $dcAdmin->id,
                'notification_type' => 'LEAVE_MINUTED',
                'title' => 'Leave Application Minuted',
                'message' => "Leave application for {$application->officer->service_number} has been minuted and requires your approval",
                'data' => ['leave_application_id' => $application->id],
            ]);
        }
    }

    /**
     * Notify officer
     */
    private function notifyOfficer(LeaveApplication $application, string $message): void
    {
        if ($application->officer->user_id) {
            Notification::create([
                'user_id' => $application->officer->user_id,
                'notification_type' => 'LEAVE_STATUS',
                'title' => 'Leave Application Status',
                'message' => $message,
                'data' => ['leave_application_id' => $application->id],
            ]);
        }
    }
}


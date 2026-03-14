<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Services\PassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassApplicationController extends BaseController
{
    /**
     * List pass applications
     */
    public function index(Request $request): JsonResponse
    {
        $query = PassApplication::with('officer');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('command_id')) {
            $query->whereHas('officer', function ($q) use ($request) {
                $q->where('present_station', $request->command_id);
            });
        }

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        $perPage = $request->get('per_page', 20);
        $applications = $query->paginate($perPage);

        return $this->paginatedResponse(
            $applications->items(),
            [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'last_page' => $applications->lastPage(),
            ]
        );
    }

    /**
     * Get pass application detail (for mobile detail screen)
     */
    public function show(Request $request, $id): JsonResponse
    {
        $application = PassApplication::with(['officer.presentStation', 'approval'])
            ->findOrFail($id);

        $user = $request->user();
        $officer = $user->officer;

        // Officers see only their own; Staff Officer / 2iC Unit Head see by command
        if ($officer && $application->officer_id !== $officer->id) {
            $canSee = $user->hasRole('Staff Officer') || $user->hasRole('2iC Unit Head');
            if ($canSee && $officer->present_station && $application->officer->present_station !== $officer->present_station) {
                return $this->errorResponse('Pass application not found', null, 404);
            }
            if (!$canSee) {
                return $this->errorResponse('Pass application not found', null, 404);
            }
        }

        $data = [
            'id' => $application->id,
            'officer_id' => $application->officer_id,
            'start_date' => $application->start_date,
            'end_date' => $application->end_date,
            'number_of_days' => $application->number_of_days,
            'reason' => $application->reason,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at?->toIso8601String(),
            'minuted_at' => $application->minuted_at?->toIso8601String(),
            'approved_at' => $application->approved_at?->toIso8601String(),
            'rejected_at' => $application->rejected_at?->toIso8601String(),
            'rejection_reason' => $application->rejection_reason,
            'officer' => $application->officer ? [
                'id' => $application->officer->id,
                'service_number' => $application->officer->service_number,
                'full_name' => $application->officer->full_name,
                'present_station' => $application->officer->presentStation ? [
                    'id' => $application->officer->presentStation->id,
                    'name' => $application->officer->presentStation->name,
                ] : null,
            ] : null,
        ];

        return $this->successResponse($data);
    }

    /**
     * Apply for pass (Officer)
     */
    public function store(Request $request, PassService $passService): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
        ]);

        $workingDays = $passService->workingDaysBetween($request->start_date, $request->end_date);
        $passMax = $passService->getPassMaxWorkingDaysForGradeLevel($officer->salary_grade_level);

        if ($workingDays > $passMax) {
            $gl = $officer->salary_grade_level ?? 'N/A';
            return $this->errorResponse(
                "Pass cannot exceed {$passMax} working days for your grade level ({$gl}). Saturdays and Sundays are not counted.",
                null,
                422,
                'VALIDATION_ERROR'
            );
        }

        // Check if annual leave is exhausted
        $annualLeaveType = \App\Models\LeaveType::where('code', 'ANNUAL_LEAVE')->first();
        if ($annualLeaveType) {
            $annualLeaveCount = LeaveApplication::where('officer_id', $officer->id)
                ->where('leave_type_id', $annualLeaveType->id)
                ->whereYear('start_date', now()->year)
                ->where('status', 'APPROVED')
                ->count();

            if ($annualLeaveCount < 2) {
                return $this->errorResponse(
                    'Annual leave must be exhausted before applying for pass',
                    null,
                    422,
                    'VALIDATION_ERROR'
                );
            }
        }

        // Check maximum 2 passes per year
        $passCount = PassApplication::where('officer_id', $officer->id)
            ->whereYear('start_date', now()->year)
            ->where('status', 'APPROVED')
            ->count();

        if ($passCount >= 2) {
            return $this->errorResponse(
                'Maximum 2 passes per year allowed',
                null,
                422,
                'VALIDATION_ERROR'
            );
        }

        $application = PassApplication::create([
            'officer_id' => $officer->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'number_of_days' => $workingDays,
            'reason' => $request->reason,
            'status' => 'PENDING',
        ]);

        return $this->successResponse([
            'id' => $application->id,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at,
        ], 'Pass application submitted successfully', 201);
    }

    /**
     * Approve/reject pass (2iC Unit Head)
     */
    public function approve(Request $request, $id, PassService $passService): JsonResponse
    {
        $application = PassApplication::with(['approval', 'officer'])->findOrFail($id);

        if ($application->status !== 'MINUTED') {
            return $this->errorResponse(
                'Application must be minuted before approval',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'required_if:action,reject|string',
        ]);

        if ($request->action === 'approve') {
            $passMax = $passService->getPassMaxWorkingDaysForGradeLevel($application->officer->salary_grade_level);
            if ($application->number_of_days > $passMax) {
                $gl = $application->officer->salary_grade_level ?? 'N/A';
                return $this->errorResponse(
                    "Pass cannot exceed {$passMax} working days for this officer's grade level ({$gl}).",
                    null,
                    422,
                    'VALIDATION_ERROR'
                );
            }

            $application->update([
                'status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            $application->approval->update([
                'dc_admin_id' => $request->user()->id,
                'approval_status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            $areaController = $application->officer->presentStation?->areaController;
            if ($areaController) {
                $application->approval->update([
                    'area_controller_id' => $areaController->id,
                ]);
            }

            return $this->successResponse([
                'id' => $application->id,
                'status' => $application->status,
                'approved_at' => $application->approved_at,
            ], 'Pass application approved');
        } else {
            $application->update([
                'status' => 'REJECTED',
                'rejected_at' => now(),
            ]);

            $application->approval->update([
                'dc_admin_id' => $request->user()->id,
                'approval_status' => 'REJECTED',
            ]);

            return $this->successResponse([
                'id' => $application->id,
                'status' => $application->status,
            ], 'Pass application rejected');
        }
    }
}


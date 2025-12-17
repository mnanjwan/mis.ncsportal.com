<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LeaveApplication;
use App\Models\PassApplication;
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
     * Apply for pass (Officer)
     */
    public function store(Request $request): JsonResponse
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

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $numberOfDays = $startDate->diffInDays($endDate) + 1;

        // Validate maximum 5 days
        if ($numberOfDays > 5) {
            return $this->errorResponse(
                'Pass cannot exceed 5 days',
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
            'number_of_days' => $numberOfDays,
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
     * Approve/reject pass (DC Admin)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $application = PassApplication::with('approval')->findOrFail($id);

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
            // Validate 5-day limit
            if ($application->number_of_days > 5) {
                return $this->errorResponse(
                    'Pass cannot exceed 5 days',
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


<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApplicationController extends BaseController
{
    /**
     * List leave applications
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveApplication::with(['officer', 'leaveType']);

        // Apply filters
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
     * Get leave application details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $application = LeaveApplication::with([
            'officer',
            'leaveType',
            'approval.staffOfficer',
            'approval.dcAdmin',
            'approval.areaController'
        ])->findOrFail($id);

        return $this->successResponse($application);
    }

    /**
     * Apply for leave (Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
            'expected_date_of_delivery' => 'required_if:leave_type_id,6|date', // Maternity leave
            'medical_certificate' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Calculate number of days
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $numberOfDays = $startDate->diffInDays($endDate) + 1;

        // Validate leave type specific rules
        if ($leaveType->max_duration_days && $numberOfDays > $leaveType->max_duration_days) {
            return $this->errorResponse(
                "Maximum duration for this leave type is {$leaveType->max_duration_days} days",
                null,
                422,
                'VALIDATION_ERROR'
            );
        }

        // Check annual leave limits
        if ($leaveType->code === 'ANNUAL_LEAVE') {
            $annualLeaveCount = LeaveApplication::where('officer_id', $officer->id)
                ->where('leave_type_id', $leaveType->id)
                ->whereYear('start_date', now()->year)
                ->count();

            if ($annualLeaveCount >= ($leaveType->max_occurrences_per_year ?? 2)) {
                return $this->errorResponse(
                    'Maximum annual leave applications reached for this year',
                    null,
                    422,
                    'VALIDATION_ERROR'
                );
            }
        }

        $application = LeaveApplication::create([
            'officer_id' => $officer->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'number_of_days' => $numberOfDays,
            'reason' => $request->reason,
            'expected_date_of_delivery' => $request->expected_date_of_delivery,
            'status' => 'PENDING',
        ]);

        // Handle medical certificate upload if provided
        if ($request->hasFile('medical_certificate')) {
            $path = $request->file('medical_certificate')->store('certificates', 'public');
            $application->medical_certificate_url = $path;
            $application->save();
        }

        return $this->successResponse([
            'id' => $application->id,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at,
        ], 'Leave application submitted successfully', 201);
    }

    /**
     * Minute leave application (Staff Officer)
     */
    public function minute(Request $request, $id): JsonResponse
    {
        $application = LeaveApplication::findOrFail($id);

        if ($application->status !== 'PENDING') {
            return $this->errorResponse(
                'Application must be in PENDING status',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $application->update([
            'status' => 'MINUTED',
            'minuted_at' => now(),
        ]);

        $approval = $application->approval()->create([
            'staff_officer_id' => $request->user()->id,
            'approval_status' => 'MINUTED',
        ]);

        return $this->successResponse([
            'id' => $application->id,
            'status' => $application->status,
            'minuted_at' => $application->minuted_at,
        ], 'Leave application minuted successfully');
    }

    /**
     * Approve/reject leave application (DC Admin)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $application = LeaveApplication::with('approval')->findOrFail($id);

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
            $application->update([
                'status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            $application->approval->update([
                'dc_admin_id' => $request->user()->id,
                'approval_status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            // Get Area Controller for the officer's command
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
                'area_controller' => $areaController ? [
                    'id' => $areaController->id,
                    'name' => $areaController->full_name,
                ] : null,
            ], 'Leave application approved');
        } else {
            $application->update([
                'status' => 'REJECTED',
                'rejected_at' => now(),
                'rejection_reason' => $request->comments,
            ]);

            $application->approval->update([
                'dc_admin_id' => $request->user()->id,
                'approval_status' => 'REJECTED',
            ]);

            return $this->successResponse([
                'id' => $application->id,
                'status' => $application->status,
            ], 'Leave application rejected');
        }
    }

    /**
     * Mark leave document as printed (Staff Officer)
     */
    public function print(Request $request, $id): JsonResponse
    {
        $application = LeaveApplication::with('approval')->findOrFail($id);

        if ($application->status !== 'APPROVED') {
            return $this->errorResponse(
                'Application must be approved before printing',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $application->approval->update([
            'printed_at' => now(),
            'printed_by' => $request->user()->id,
        ]);

        return $this->successResponse([
            'id' => $application->id,
            'printed_at' => $application->approval->printed_at,
        ], 'Leave document marked as printed');
    }
}


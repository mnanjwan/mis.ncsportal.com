<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends BaseController
{
    /**
     * List all leave types
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveType::where('is_active', true);

        $leaveTypes = $query->get();

        return $this->successResponse($leaveTypes);
    }

    /**
     * Create custom leave type (HRD only)
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can create leave types',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'name' => 'required|string|unique:leave_types,name',
            'code' => 'required|string|unique:leave_types,code',
            'max_duration_days' => 'nullable|integer|min:1',
            'max_duration_months' => 'nullable|integer|min:1',
            'max_occurrences_per_year' => 'nullable|integer|min:1',
            'requires_medical_certificate' => 'boolean',
            'requires_approval_level' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $leaveType = LeaveType::create([
            'name' => $request->name,
            'code' => $request->code,
            'max_duration_days' => $request->max_duration_days,
            'max_duration_months' => $request->max_duration_months,
            'max_occurrences_per_year' => $request->max_occurrences_per_year,
            'requires_medical_certificate' => $request->boolean('requires_medical_certificate', false),
            'requires_approval_level' => $request->requires_approval_level,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        return $this->successResponse($leaveType, 'Leave type created successfully', 201);
    }
}


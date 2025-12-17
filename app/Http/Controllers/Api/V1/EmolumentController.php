<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmolumentController extends BaseController
{
    /**
     * List emoluments (Assessor/Validator/HRD)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Emolument::with(['officer', 'timeline']);

        // Role-based filtering
        if ($user->hasRole('Assessor')) {
            // Assessors see only subordinate officers' emoluments
            // This would need to be implemented based on command hierarchy
            $query->whereHas('officer', function ($q) use ($user) {
                // Filter by command/subordinates
                if ($user->officer?->present_station) {
                    $q->where('present_station', $user->officer->present_station);
                }
            });
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('command_id')) {
            $query->whereHas('officer', function ($q) use ($request) {
                $q->where('present_station', $request->command_id);
            });
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        $perPage = $request->get('per_page', 20);
        $emoluments = $query->paginate($perPage);

        return $this->paginatedResponse(
            $emoluments->items(),
            [
                'current_page' => $emoluments->currentPage(),
                'per_page' => $emoluments->perPage(),
                'total' => $emoluments->total(),
                'last_page' => $emoluments->lastPage(),
            ]
        );
    }

    /**
     * Get current officer's emoluments
     */
    public function myEmoluments(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $emoluments = Emolument::where('officer_id', $officer->id)
            ->with('timeline')
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($emolument) {
                return [
                    'id' => $emolument->id,
                    'year' => $emolument->year,
                    'timeline' => $emolument->timeline ? [
                        'id' => $emolument->timeline->id,
                        'year' => $emolument->timeline->year,
                    ] : null,
                    'bank_name' => $emolument->bank_name,
                    'bank_account_number' => $emolument->bank_account_number,
                    'pfa_name' => $emolument->pfa_name,
                    'rsa_pin' => $emolument->rsa_pin,
                    'status' => $emolument->status,
                    'submitted_at' => $emolument->submitted_at?->format('Y-m-d H:i:s'),
                    'assessed_at' => $emolument->assessed_at?->format('Y-m-d H:i:s'),
                    'validated_at' => $emolument->validated_at?->format('Y-m-d H:i:s'),
                    'processed_at' => $emolument->processed_at?->format('Y-m-d H:i:s'),
                ];
            });

        return $this->successResponse($emoluments);
    }

    /**
     * Get emolument details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $emolument = Emolument::with([
            'officer.presentStation',
            'timeline',
            'assessment.assessor',
            'validation.validator'
        ])->findOrFail($id);

        return $this->successResponse($emolument);
    }

    /**
     * Raise emolument (Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        // Check if active timeline exists
        $timeline = EmolumentTimeline::where('is_active', true)
            ->where('year', now()->year)
            ->first();

        if (!$timeline || !$timeline->can_submit) {
            return $this->errorResponse(
                'No active emolument timeline or timeline has expired',
                null,
                400,
                'TIMELINE_INACTIVE'
            );
        }

        // Check if emolument already exists for this year
        $existing = Emolument::where('officer_id', $officer->id)
            ->where('year', now()->year)
            ->first();

        if ($existing) {
            return $this->errorResponse(
                'Emolument already raised for this year',
                null,
                400,
                'DUPLICATE_ENTRY'
            );
        }

        $request->validate([
            'timeline_id' => 'required|exists:emolument_timelines,id',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'pfa_name' => 'required|string',
            'rsa_pin' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $emolument = Emolument::create([
            'officer_id' => $officer->id,
            'timeline_id' => $request->timeline_id,
            'year' => now()->year,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'pfa_name' => $request->pfa_name,
            'rsa_pin' => $request->rsa_pin,
            'notes' => $request->notes,
            'status' => 'RAISED',
        ]);

        // Update next of kin (this would be handled separately)
        // For now, we'll just create the emolument

        return $this->successResponse([
            'id' => $emolument->id,
            'status' => $emolument->status,
            'submitted_at' => $emolument->submitted_at,
        ], 'Emolument raised successfully', 201);
    }

    /**
     * Assess emolument (Assessor)
     */
    public function assess(Request $request, $id): JsonResponse
    {
        $emolument = Emolument::findOrFail($id);

        if ($emolument->status !== 'RAISED') {
            return $this->errorResponse(
                'Emolument must be in RAISED status to be assessed',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $request->validate([
            'assessment_status' => 'required|in:APPROVED,REJECTED',
            'comments' => 'nullable|string',
        ]);

        $assessment = $emolument->assessment()->create([
            'assessor_id' => $request->user()->id,
            'assessment_status' => $request->assessment_status,
            'comments' => $request->comments,
        ]);

        $emolument->update([
            'status' => 'ASSESSED',
            'assessed_at' => now(),
        ]);

        return $this->successResponse([
            'id' => $emolument->id,
            'status' => $emolument->status,
            'assessed_at' => $emolument->assessed_at,
        ], 'Emolument assessed successfully');
    }

    /**
     * Validate emolument (Validator/Area Controller)
     */
    public function validate(Request $request, $id): JsonResponse
    {
        $emolument = Emolument::with('assessment')->findOrFail($id);

        if ($emolument->status !== 'ASSESSED') {
            return $this->errorResponse(
                'Emolument must be assessed before validation',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        if (!$emolument->assessment) {
            return $this->errorResponse(
                'Assessment not found',
                null,
                404
            );
        }

        $request->validate([
            'validation_status' => 'required|in:APPROVED,REJECTED',
            'comments' => 'nullable|string',
        ]);

        $validation = $emolument->validation()->create([
            'assessment_id' => $emolument->assessment->id,
            'validator_id' => $request->user()->id,
            'validation_status' => $request->validation_status,
            'comments' => $request->comments,
        ]);

        $emolument->update([
            'status' => 'VALIDATED',
            'validated_at' => now(),
        ]);

        return $this->successResponse([
            'id' => $emolument->id,
            'status' => $emolument->status,
            'validated_at' => $emolument->validated_at,
        ], 'Emolument validated successfully');
    }

    /**
     * Get validated emoluments for payment (Accounts)
     */
    public function validated(Request $request): JsonResponse
    {
        $query = Emolument::where('status', 'VALIDATED')
            ->with('officer');

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('command_id')) {
            $query->whereHas('officer', function ($q) use ($request) {
                $q->where('present_station', $request->command_id);
            });
        }

        $emoluments = $query->get()->map(function ($emolument) {
            return [
                'id' => $emolument->id,
                'officer' => [
                    'service_number' => $emolument->officer->service_number,
                    'name' => $emolument->officer->full_name,
                ],
                'bank_name' => $emolument->bank_name,
                'bank_account_number' => $emolument->bank_account_number,
                'pfa_name' => $emolument->pfa_name,
                'rsa_number' => $emolument->rsa_pin,
            ];
        });

        return $this->successResponse($emoluments);
    }
}


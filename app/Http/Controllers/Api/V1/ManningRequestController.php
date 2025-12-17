<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ManningRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManningRequestController extends BaseController
{
    /**
     * List manning requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ManningRequest::with(['command', 'items', 'requestedBy']);

        // Role-based filtering
        if ($user->hasRole('Staff Officer')) {
            if ($user->officer?->present_station) {
                $query->where('command_id', $user->officer->present_station);
            }
        }

        if ($request->has('command_id')) {
            $query->where('command_id', $request->command_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $requests = $query->paginate($perPage);

        return $this->paginatedResponse(
            $requests->items(),
            [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage(),
            ]
        );
    }

    /**
     * Create manning request (Staff Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'items' => 'required|array|min:1',
            'items.*.rank' => 'required|string',
            'items.*.quantity_needed' => 'required|integer|min:1',
            'items.*.sex_requirement' => 'nullable|in:M,F,ANY',
            'items.*.qualification_requirement' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $manningRequest = ManningRequest::create([
            'command_id' => $request->command_id,
            'requested_by' => $user->id,
            'status' => 'DRAFT',
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $manningRequest->items()->create([
                'rank' => $item['rank'],
                'quantity_needed' => $item['quantity_needed'],
                'sex_requirement' => $item['sex_requirement'] ?? 'ANY',
                'qualification_requirement' => $item['qualification_requirement'] ?? null,
            ]);
        }

        return $this->successResponse([
            'id' => $manningRequest->id,
            'status' => $manningRequest->status,
        ], 'Manning request created successfully', 201);
    }

    /**
     * Submit manning request (Staff Officer)
     */
    public function submit(Request $request, $id): JsonResponse
    {
        $manningRequest = ManningRequest::findOrFail($id);

        if ($manningRequest->status !== 'DRAFT') {
            return $this->errorResponse(
                'Only draft requests can be submitted',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $manningRequest->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        return $this->successResponse([
            'id' => $manningRequest->id,
            'status' => $manningRequest->status,
        ], 'Manning request submitted');
    }

    /**
     * Approve manning request (Area Controller)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $manningRequest = ManningRequest::findOrFail($id);
        $user = $request->user();
        $officer = $user->officer;

        if ($manningRequest->status !== 'SUBMITTED') {
            return $this->errorResponse(
                'Request must be submitted before approval',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $manningRequest->update([
            'status' => 'APPROVED',
            'approved_by' => $officer?->id,
            'approved_at' => now(),
            'forwarded_to_hrd_at' => now(),
        ]);

        return $this->successResponse([
            'id' => $manningRequest->id,
            'status' => $manningRequest->status,
            'forwarded_to_hrd_at' => $manningRequest->forwarded_to_hrd_at,
        ], 'Manning request approved and forwarded to HRD');
    }
}


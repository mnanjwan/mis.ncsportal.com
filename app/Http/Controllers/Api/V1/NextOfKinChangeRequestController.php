<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NextOfKinChangeRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NextOfKinChangeRequestController extends BaseController
{
    /**
     * List current officer's next of kin change requests
     */
    public function index(Request $request): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $requests = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->with('nextOfKin')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

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
     * Submit add next of kin request
     */
    public function store(Request $request): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        $pending = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->where('action_type', 'add')
            ->where('name', $validated['name'])
            ->first();

        if ($pending) {
            return $this->errorResponse(
                'You have a pending add request for this Next of KIN. Please wait for it to be processed.',
                null,
                422,
                'PENDING_EXISTS'
            );
        }

        DB::beginTransaction();
        try {
            $changeRequest = NextOfKinChangeRequest::create([
                'officer_id' => $officer->id,
                'action_type' => 'add',
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
                'status' => 'PENDING',
            ]);

            app(NotificationService::class)->notifyNextOfKinChangeRequestSubmitted($changeRequest);

            DB::commit();

            return $this->successResponse([
                'id' => $changeRequest->id,
                'status' => $changeRequest->status,
            ], 'Next of KIN add request submitted successfully. It will be reviewed by the Welfare Section.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse('Failed to submit request.', null, 500);
        }
    }

    /**
     * Show single next of kin change request
     */
    public function show(Request $request, $id): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $req = NextOfKinChangeRequest::where('officer_id', $officer->id)->with('nextOfKin')->findOrFail($id);

        return $this->successResponse($req);
    }
}

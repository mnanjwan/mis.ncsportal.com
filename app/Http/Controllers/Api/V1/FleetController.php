<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FleetRequest;
use App\Models\FleetVehicle;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FleetController extends BaseController
{
    /**
     * Get user's created requests and pending inbox approvals.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Requests created by the user
        $myRequests = FleetRequest::query()
            ->where('created_by', $user->id)
            ->with(['originCommand', 'steps'])
            ->latest()
            ->take(50)
            ->get();

        // Inbox: requests where the current step role matches any of the user roles
        $userRoleNames = $user->roles()
            ->wherePivot('is_active', true)
            ->pluck('name')
            ->toArray();

        $inbox = FleetRequest::query()
            ->whereNotNull('current_step_order')
            ->whereHas('steps', function ($q) use ($userRoleNames) {
                $q->whereColumn('fleet_request_steps.step_order', 'fleet_requests.current_step_order')
                    ->whereIn('fleet_request_steps.role_name', $userRoleNames);
            })
            ->with(['originCommand', 'createdBy', 'steps'])
            ->orderByDesc('updated_at')
            ->take(50)
            ->get();

        return $this->successResponse([
            'myRequests' => $myRequests,
            'inbox' => $inbox,
        ], 'Fleet requests retrieved successfully.');
    }

    /**
     * Draft a new fleet request.
     */
    public function store(Request $request, FleetWorkflowService $service)
    {
        $data = $request->validate([
            'request_type' => [
                'required',
                'string',
                Rule::in([
                    'FLEET_NEW_VEHICLE',
                    'FLEET_RE_ALLOCATION',
                    'FLEET_OPE',
                    'FLEET_REPAIR',
                    'FLEET_USE',
                    'FLEET_REQUISITION'
                ])
            ],
            'requested_vehicle_type' => ['nullable', 'string', Rule::in(array_keys(config('fleet.vehicle_types', [])))],
            'requested_make' => ['nullable', 'string', 'max:100'],
            'requested_model' => ['nullable', 'string', 'max:100'],
            'requested_year' => ['nullable', 'integer', 'min:1950', 'max:' . (int) date('Y')],
            'requested_quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'fleet_vehicle_id' => ['nullable', 'exists:fleet_vehicles,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('fleet/requests', 'public');
        }

        try {
            $fleetRequest = $service->createRequest($request->user(), $data);
            return $this->successResponse($fleetRequest, 'Fleet request drafted successfully.', 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Submit a drafted request into the workflow.
     */
    public function submit(Request $request, $id, FleetWorkflowService $service)
    {
        $fleetRequest = FleetRequest::findOrFail($id);

        if ((int) $fleetRequest->created_by !== (int) $request->user()->id) {
            return $this->errorResponse('Only the creator can submit this request.', 403);
        }

        try {
            $fleetRequest = $service->submit($fleetRequest, $request->user());
            return $this->successResponse($fleetRequest, 'Request submitted successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Get full details of a single request.
     */
    public function show(Request $request, $id)
    {
        $fleetRequest = FleetRequest::with([
            'originCommand',
            'createdBy',
            'steps.actedBy',
            'fulfillment',
            'reservedVehicles',
            'vehicle',
        ])->findOrFail($id);

        return $this->successResponse($fleetRequest, 'Fleet request details retrieved.');
    }

    /**
     * Approve, Reject, or Forward a step.
     */
    public function act(Request $request, $id, FleetWorkflowService $service)
    {
        $fleetRequest = FleetRequest::findOrFail($id);

        $data = $request->validate([
            'decision' => ['required', 'string', Rule::in(['FORWARDED', 'APPROVED', 'REJECTED', 'REVIEWED', 'KIV'])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $fleetRequest = $service->act($fleetRequest, $request->user(), $data['decision'], $data['comment'] ?? null);
            return $this->successResponse($fleetRequest, "Action recorded successfully.");
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Vehicles assigned directly to the authenticated officer.
     */
    public function myVehicles(Request $request)
    {
        $officerId = $request->user()->officer_id;

        if (!$officerId) {
            return $this->successResponse([], 'No officer association found.');
        }

        $vehicles = FleetVehicle::with(['vehicleModel', 'currentCommand'])
            ->where('current_officer_id', $officerId)
            ->get();

        return $this->successResponse($vehicles, 'Assigned vehicles retrieved.');
    }

    /**
     * All vehicles in the user's active command (For T&L officers).
     */
    public function commandVehicles(Request $request, FleetWorkflowService $service)
    {
        $commandId = $service->getActiveCommandIdForRole($request->user(), 'Staff Officer T&L')
            ?? $service->getActiveCommandIdForRole($request->user(), 'OC Workshop')
            ?? $service->getActiveCommandIdForRole($request->user(), 'CD');

        if (!$commandId) {
            return $this->errorResponse('You are not authorized or assigned to a specific command to view its vehicles.', 403);
        }

        $vehicles = FleetVehicle::with(['vehicleModel', 'currentOfficer'])
            ->where('current_command_id', $commandId)
            ->get();

        return $this->successResponse($vehicles, 'Command vehicles retrieved.');
    }
}

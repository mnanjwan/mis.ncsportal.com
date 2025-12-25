<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Officer;
use App\Models\OfficerQuarter;
use App\Models\Quarter;
use App\Models\QuarterRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuarterController extends BaseController
{
    /**
     * List quarters
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Quarter::where('is_active', true);

        // Building Unit MUST see only quarters in their command (command-based access)
        if ($user->hasRole('Building Unit')) {
            $buildingUnitRole = $user->roles()
                ->where('name', 'Building Unit')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $buildingUnitRole?->pivot->command_id ?? null;
            
            if (!$commandId) {
                return $this->errorResponse(
                    'Building Unit user must be assigned to a command',
                    null,
                    403,
                    'NO_COMMAND_ASSIGNED'
                );
            }
            $query->where('command_id', $commandId);
        } elseif ($request->has('command_id')) {
            $query->where('command_id', $request->command_id);
        }

        if ($request->has('is_occupied')) {
            $query->where('is_occupied', $request->boolean('is_occupied'));
        }

        $quarters = $query->with(['officerQuarters' => function ($q) {
            $q->where('is_current', true)
              ->where('status', 'ACCEPTED')
              ->with('officer:id,service_number,initials,surname');
        }])->get();

        // Transform response to include officer info
        $quarters = $quarters->map(function ($quarter) {
            $currentAllocation = $quarter->officerQuarters->first();
            return [
                'id' => $quarter->id,
                'command_id' => $quarter->command_id,
                'quarter_number' => $quarter->quarter_number,
                'quarter_type' => $quarter->quarter_type,
                'is_occupied' => $quarter->is_occupied,
                'is_active' => $quarter->is_active,
                'officer' => $currentAllocation ? [
                    'id' => $currentAllocation->officer->id,
                    'service_number' => $currentAllocation->officer->service_number,
                    'initials' => $currentAllocation->officer->initials,
                    'surname' => $currentAllocation->officer->surname,
                ] : null,
            ];
        });

        return $this->successResponse($quarters);
    }

    /**
     * Get quarters statistics (Building Unit)
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can view statistics',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command (command-based access)
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }
        
        $query = Quarter::where('is_active', true)
            ->where('command_id', $commandId);

        $totalQuarters = $query->count();
        // Only count quarters with ACCEPTED allocations as occupied
        $occupiedQuarters = OfficerQuarter::whereHas('quarter', function($q) use ($commandId) {
            $q->where('command_id', $commandId)->where('is_active', true);
        })
        ->where('is_current', true)
        ->where('status', 'ACCEPTED')
        ->count();
        $availableQuarters = $totalQuarters - $occupiedQuarters;

        return $this->successResponse([
            'total_quarters' => $totalQuarters,
            'occupied' => $occupiedQuarters,
            'available' => $availableQuarters,
        ], 'Statistics retrieved successfully');
    }

    /**
     * Create quarter (Building Unit)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can create quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit can only create quarters for their own command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'quarter_number' => 'required|string|max:50',
            'quarter_type' => 'required|string',
        ]);

        // Ensure Building Unit can only create quarters for their command
        if ($request->command_id != $commandId) {
            return $this->errorResponse(
                'Building Unit can only create quarters for their assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $quarter = Quarter::create([
            'command_id' => $request->command_id,
            'quarter_number' => $request->quarter_number,
            'quarter_type' => $request->quarter_type,
            'is_occupied' => false,
            'is_active' => true,
        ]);

        // Load command relationship for notification
        $quarter->load('command');

        // Notify Building Unit users about new quarter
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterCreated($quarter, $request->user());

        return $this->successResponse([
            'id' => $quarter->id,
            'quarter_number' => $quarter->quarter_number,
        ], 'Quarter created successfully', 201);
    }

    /**
     * Allocate quarter to officer (Building Unit)
     */
    public function allocate(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can allocate quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'quarter_id' => 'required|exists:quarters,id',
            'allocation_date' => 'sometimes|date',
        ]);

        $quarter = Quarter::findOrFail($request->quarter_id);
        
        // Ensure quarter belongs to Building Unit's command
        if ($quarter->command_id != $commandId) {
            return $this->errorResponse(
                'You can only allocate quarters in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $officer = Officer::findOrFail($request->officer_id);
        
        // Ensure officer belongs to Building Unit's command
        if ($officer->present_station != $commandId) {
            return $this->errorResponse(
                'You can only allocate quarters to officers in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Check if quarter is occupied by an accepted allocation
        $acceptedAllocation = OfficerQuarter::where('quarter_id', $request->quarter_id)
            ->where('is_current', true)
            ->where('status', 'ACCEPTED')
            ->exists();

        if ($acceptedAllocation) {
            return $this->errorResponse(
                'Quarter is already occupied',
                null,
                400,
                'QUARTER_OCCUPIED'
            );
        }

        // Deallocate previous accepted quarter if any
        OfficerQuarter::where('officer_id', $request->officer_id)
            ->where('is_current', true)
            ->where('status', 'ACCEPTED')
            ->update(['is_current' => false]);

        // Cancel any pending allocations for this officer
        OfficerQuarter::where('officer_id', $request->officer_id)
            ->where('status', 'PENDING')
            ->update(['is_current' => false, 'status' => 'REJECTED', 'rejected_at' => now()]);

        // Create new allocation with PENDING status - officer must accept
        $allocation = OfficerQuarter::create([
            'officer_id' => $request->officer_id,
            'quarter_id' => $request->quarter_id,
            'allocated_date' => $request->allocation_date ?? now(),
            'is_current' => true,
            'status' => 'PENDING',
            'allocated_by' => $user->id,
        ]);

        // Refresh relationships
        $allocation->load(['officer', 'quarter']);

        // Notify officer about pending quarter allocation (needs acceptance)
        $notificationService = app(NotificationService::class);
        $allocationDate = $request->allocation_date ?? now();
        $notificationService->notifyQuarterAllocated($officer, $quarter, $allocationDate);

        return $this->successResponse([
            'id' => $allocation->id,
            'officer_id' => $request->officer_id,
            'quarter_id' => $request->quarter_id,
            'status' => 'PENDING',
            'message' => 'Quarter allocation pending officer acceptance',
        ], 'Quarter allocation created. Officer must accept the allocation.');
    }

    /**
     * Deallocate quarter (Building Unit)
     */
    public function deallocate(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can deallocate quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        // If officer_id is provided in request body, find allocation by quarter_id and officer_id
        if ($request->has('officer_id')) {
            $allocation = OfficerQuarter::where('quarter_id', $id)
                ->where('officer_id', $request->officer_id)
                ->where('is_current', true)
                ->where('status', 'ACCEPTED')
                ->firstOrFail();
        } else {
            // Otherwise, find by allocation ID
            $allocation = OfficerQuarter::findOrFail($id);
        }

        // Ensure quarter belongs to Building Unit's command
        $quarter = $allocation->quarter;
        if ($quarter->command_id != $commandId) {
            return $this->errorResponse(
                'You can only deallocate quarters in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Only deallocate accepted allocations
        if (!$allocation->isAccepted()) {
            return $this->errorResponse(
                'Only accepted allocations can be deallocated',
                null,
                400,
                'INVALID_STATUS'
            );
        }

        $allocation->update([
            'is_current' => false,
            'deallocation_date' => now(),
        ]);

        // Mark quarter as available
        $allocation->quarter->update(['is_occupied' => false]);

        // Update officer's quartered status
        $officer = $allocation->officer;
        $quarter = $allocation->quarter;
        $officer->update(['quartered' => false]);

        // Notify officer about quarter deallocation
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterDeallocated($officer, $quarter);

        return $this->successResponse([
            'id' => $allocation->id,
            'quarter_id' => $allocation->quarter_id,
            'officer_id' => $allocation->officer_id,
        ], 'Quarter deallocated successfully');
    }

    /**
     * Submit quarter request (Officer)
     */
    public function submitRequest(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse(
                'User must be linked to an officer',
                null,
                403,
                'NO_OFFICER_LINKED'
            );
        }

        // Check if officer already has a pending request
        $pendingRequest = QuarterRequest::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingRequest) {
            return $this->errorResponse(
                'You already have a pending quarter request',
                null,
                400,
                'PENDING_REQUEST_EXISTS'
            );
        }

        $request->validate([
            'quarter_id' => 'nullable|exists:quarters,id',
            'preferred_quarter_type' => 'nullable|string|max:100',
        ]);

        $quarterRequest = QuarterRequest::create([
            'officer_id' => $officer->id,
            'quarter_id' => $request->quarter_id,
            'preferred_quarter_type' => $request->preferred_quarter_type,
            'status' => 'PENDING',
        ]);

        $quarterRequest->load(['officer:id,service_number,initials,surname', 'quarter']);

        // Notify Building Unit users about new request
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterRequestSubmitted($quarterRequest);

        return $this->successResponse([
            'id' => $quarterRequest->id,
            'status' => $quarterRequest->status,
        ], 'Quarter request submitted successfully', 201);
    }

    /**
     * Get officer's own quarter requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse(
                'User must be linked to an officer',
                null,
                403,
                'NO_OFFICER_LINKED'
            );
        }

        $requests = QuarterRequest::where('officer_id', $officer->id)
            ->with(['quarter', 'rejectedBy:id,name', 'approvedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($requests);
    }

    /**
     * List all quarter requests (Building Unit)
     */
    public function requests(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can view quarter requests',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $query = QuarterRequest::with([
            'officer:id,service_number,initials,surname,present_station',
            'quarter:id,quarter_number,quarter_type,command_id',
            'rejectedBy:id,name',
            'approvedBy:id,name',
        ])
        ->whereHas('officer', function ($q) use ($commandId) {
            $q->where('present_station', $commandId);
        });

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->whereHas('officer', function ($q) use ($searchTerm, $commandId) {
                $q->where('present_station', $commandId)
                  ->where(function ($subQ) use ($searchTerm) {
                      $subQ->where('service_number', 'like', "%{$searchTerm}%")
                           ->orWhere('initials', 'like', "%{$searchTerm}%")
                           ->orWhere('surname', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse($requests);
    }

    /**
     * Approve quarter request (Building Unit)
     */
    public function approveRequest(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can approve quarter requests',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $quarterRequest = QuarterRequest::with(['officer', 'quarter'])->findOrFail($id);

        // Ensure request is for officer in Building Unit's command
        if ($quarterRequest->officer->present_station != $commandId) {
            return $this->errorResponse(
                'You can only approve requests for officers in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Ensure request is pending
        if (!$quarterRequest->isPending()) {
            return $this->errorResponse(
                'Only pending requests can be approved',
                null,
                400,
                'INVALID_STATUS'
            );
        }

        $request->validate([
            'quarter_id' => 'required|exists:quarters,id',
            'allocation_date' => 'sometimes|date',
        ]);

        $quarter = Quarter::findOrFail($request->quarter_id);

        // Ensure quarter belongs to Building Unit's command
        if ($quarter->command_id != $commandId) {
            return $this->errorResponse(
                'You can only allocate quarters in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Check if quarter is occupied by an accepted allocation
        $acceptedAllocation = OfficerQuarter::where('quarter_id', $request->quarter_id)
            ->where('is_current', true)
            ->where('status', 'ACCEPTED')
            ->exists();

        if ($acceptedAllocation) {
            return $this->errorResponse(
                'Quarter is already occupied',
                null,
                400,
                'QUARTER_OCCUPIED'
            );
        }

        DB::transaction(function () use ($quarterRequest, $quarter, $user, $request) {
            // Update request status
            $quarterRequest->update([
                'status' => 'APPROVED',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'quarter_id' => $request->quarter_id,
            ]);

            // Deallocate previous accepted quarter if any
            OfficerQuarter::where('officer_id', $quarterRequest->officer_id)
                ->where('is_current', true)
                ->where('status', 'ACCEPTED')
                ->update(['is_current' => false]);

            // Cancel any pending allocations for this officer
            OfficerQuarter::where('officer_id', $quarterRequest->officer_id)
                ->where('status', 'PENDING')
                ->update(['is_current' => false, 'status' => 'REJECTED', 'rejected_at' => now()]);

            // Create new allocation with PENDING status - officer must accept
            OfficerQuarter::create([
                'officer_id' => $quarterRequest->officer_id,
                'quarter_id' => $request->quarter_id,
                'allocated_date' => $request->allocation_date ?? now(),
                'is_current' => true,
                'status' => 'PENDING',
                'allocated_by' => $user->id,
                'request_id' => $quarterRequest->id,
            ]);
        });

        // Refresh relationships
        $quarterRequest->refresh();
        $quarter->refresh();

        // Notify officer about approval (allocation pending acceptance)
        $notificationService = app(NotificationService::class);
        $allocationDate = $request->allocation_date ?? now();
        $notificationService->notifyQuarterRequestApproved($quarterRequest, $quarter, $allocationDate);

        return $this->successResponse([
            'id' => $quarterRequest->id,
            'status' => $quarterRequest->status,
        ], 'Quarter request approved successfully');
    }

    /**
     * Reject quarter request (Building Unit) - One-time only
     */
    public function rejectRequest(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can reject quarter requests',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $quarterRequest = QuarterRequest::with('officer')->findOrFail($id);

        // Ensure request is for officer in Building Unit's command
        if ($quarterRequest->officer->present_station != $commandId) {
            return $this->errorResponse(
                'You can only reject requests for officers in your assigned command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // ONE-TIME REJECTION RULE: Cannot reject if already rejected
        if ($quarterRequest->isRejected()) {
            return $this->errorResponse(
                'This request has already been rejected and cannot be rejected again',
                null,
                400,
                'ALREADY_REJECTED'
            );
        }

        // Ensure request is pending
        if (!$quarterRequest->isPending()) {
            return $this->errorResponse(
                'Only pending requests can be rejected',
                null,
                400,
                'INVALID_STATUS'
            );
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $quarterRequest->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        // Notify officer about rejection
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterRequestRejected($quarterRequest, $request->rejection_reason);

        return $this->successResponse([
            'id' => $quarterRequest->id,
            'status' => $quarterRequest->status,
        ], 'Quarter request rejected successfully');
    }

    /**
     * Accept quarter allocation (Officer)
     */
    public function acceptAllocation(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse(
                'User must be linked to an officer',
                null,
                403,
                'NO_OFFICER_LINKED'
            );
        }

        $allocation = OfficerQuarter::with(['quarter', 'officer'])->findOrFail($id);

        // Ensure allocation belongs to the officer
        if ($allocation->officer_id != $officer->id) {
            return $this->errorResponse(
                'You can only accept your own allocations',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Ensure allocation is pending
        if (!$allocation->isPending()) {
            return $this->errorResponse(
                'Only pending allocations can be accepted',
                null,
                400,
                'INVALID_STATUS'
            );
        }

        // Check if quarter is still available (not accepted by another officer)
        $quarterOccupied = OfficerQuarter::where('quarter_id', $allocation->quarter_id)
            ->where('id', '!=', $allocation->id)
            ->where('is_current', true)
            ->where('status', 'ACCEPTED')
            ->exists();

        if ($quarterOccupied) {
            return $this->errorResponse(
                'This quarter has already been accepted by another officer',
                null,
                400,
                'QUARTER_OCCUPIED'
            );
        }

        DB::transaction(function () use ($allocation, $officer) {
            // Update allocation status to ACCEPTED
            $allocation->update([
                'status' => 'ACCEPTED',
                'accepted_at' => now(),
            ]);

            // Mark quarter as occupied
            $allocation->quarter->update(['is_occupied' => true]);

            // Update officer's quartered status
            $officer->update(['quartered' => true]);

            // Reject any other pending allocations for this officer
            OfficerQuarter::where('officer_id', $officer->id)
                ->where('id', '!=', $allocation->id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'REJECTED',
                    'rejected_at' => now(),
                    'is_current' => false,
                ]);
        });

        // Refresh relationships
        $allocation->refresh();

        // Notify Building Unit about acceptance
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterAllocationAccepted($allocation);

        return $this->successResponse([
            'id' => $allocation->id,
            'status' => $allocation->status,
            'quarter' => $allocation->quarter,
        ], 'Quarter allocation accepted successfully');
    }

    /**
     * Reject quarter allocation (Officer)
     */
    public function rejectAllocation(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse(
                'User must be linked to an officer',
                null,
                403,
                'NO_OFFICER_LINKED'
            );
        }

        $allocation = OfficerQuarter::with(['quarter', 'officer'])->findOrFail($id);

        // Ensure allocation belongs to the officer
        if ($allocation->officer_id != $officer->id) {
            return $this->errorResponse(
                'You can only reject your own allocations',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Ensure allocation is pending
        if (!$allocation->isPending()) {
            return $this->errorResponse(
                'Only pending allocations can be rejected',
                null,
                400,
                'INVALID_STATUS'
            );
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $allocation->update([
            'status' => 'REJECTED',
            'rejection_reason' => $request->rejection_reason,
            'rejected_at' => now(),
            'is_current' => false,
        ]);

        // Notify Building Unit about rejection
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarterAllocationRejected($allocation, $request->rejection_reason);

        return $this->successResponse([
            'id' => $allocation->id,
            'status' => $allocation->status,
        ], 'Quarter allocation rejected successfully');
    }

    /**
     * Get officer's pending allocations
     */
    public function myAllocations(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse(
                'User must be linked to an officer',
                null,
                403,
                'NO_OFFICER_LINKED'
            );
        }

        $allocations = OfficerQuarter::where('officer_id', $officer->id)
            ->with(['quarter:id,quarter_number,quarter_type,command_id', 'allocatedBy:id,email', 'allocatedBy.officer:id,user_id,initials,surname'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($allocations);
    }

    /**
     * Get rejected allocations (Building Unit)
     */
    public function rejectedAllocations(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can view rejected allocations',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Building Unit MUST be assigned to a command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return $this->errorResponse(
                'Building Unit user must be assigned to a command',
                null,
                403,
                'NO_COMMAND_ASSIGNED'
            );
        }

        $query = OfficerQuarter::where('status', 'REJECTED')
            ->with([
                'officer:id,service_number,initials,surname,present_station',
                'quarter:id,quarter_number,quarter_type,command_id',
                'allocatedBy:id,email',
                'allocatedBy.officer:id,user_id,initials,surname',
            ])
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->whereHas('quarter', function ($q) use ($commandId) {
                $q->where('command_id', $commandId);
            });

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('rejected_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('rejected_at', '<=', $request->to_date);
        }

        $rejectedAllocations = $query->orderBy('rejected_at', 'desc')->get();

        return $this->successResponse($rejectedAllocations);
    }
}


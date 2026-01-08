<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Officer;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerController extends BaseController
{
    /**
     * List officers with role-based filtering
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Officer::query();

        // Role-based access control
        // Priority order: Most restrictive/specific roles should be checked relative to administrative roles.
        // The 'Officer' role is usually held by everyone, so it should be checked LAST to avoid restricting admins to self-only view.

        if ($user->hasRole(['HRD', 'Area Controller', 'DC Admin', 'Admin', 'Zone Coordinator', 'Establishment', 'CGC'])) {
            // High-level roles normally see all officers, but can filter by command if requested
            if ($request->has('command_id')) {
                $query->where('present_station', $request->command_id);
            }
        } elseif ($user->hasRole('Staff Officer')) {
            // Staff Officer sees officers in their command
            if ($user->officer?->present_station) {
                $query->where('present_station', $user->officer->present_station);
            }
        } elseif ($user->hasRole('Building Unit')) {
            // Building Unit sees only officers in their command
            $buildingUnitRole = $user->roles()
                ->where('name', 'Building Unit')
                ->wherePivot('is_active', true)
                ->first();

            $commandId = $buildingUnitRole?->pivot->command_id ?? null;

            if (!$commandId) {
                // Return empty result if no command assigned
                return $this->paginatedResponse(
                    [],
                    [
                        'current_page' => 1,
                        'per_page' => $request->get('per_page', 20),
                        'total' => 0,
                        'last_page' => 1,
                        'from' => null,
                        'to' => null,
                    ],
                    [
                        'first' => null,
                        'last' => null,
                        'prev' => null,
                        'next' => null,
                    ]
                );
            }
            $query->where('present_station', (int) $commandId);
        } elseif ($user->hasRole('Officer')) {
            // Basic Officer role ONLY (fallback if no higher roles above matched)
            // Officers can only see themselves
            $query->where('id', $user->officer?->id);
        } else {
            // Fallback for any other roles that might need a default - show nothing unless specified
            if (!$request->has('command_id')) {
                $query->where('id', 0); // No results by default
            } else {
                $query->where('present_station', $request->command_id);
            }
        }
        // HRD and Area Controller see all officers (no filter)

        if ($request->has('rank')) {
            $query->where('substantive_rank', $request->rank);
        }

        if ($request->has('service_number')) {
            $query->where('service_number', 'like', '%' . $request->service_number . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_deceased')) {
            $query->where('is_deceased', $request->boolean('is_deceased'));
        }

        if ($request->has('quartered')) {
            $query->where('quartered', $request->boolean('quartered'));
        }

        if ($request->has('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                        ->orWhere('initials', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%");
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'service_number');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $officers = $query->with(['presentStation', 'currentQuarter.quarter'])->paginate($perPage);

        // Transform officers to include quarter information
        $transformedOfficers = $officers->map(function ($officer) {
            $officerData = $officer->toArray();

            // Check for pending allocations
            $pendingAllocation = \App\Models\OfficerQuarter::where('officer_id', $officer->id)
                ->where('status', 'PENDING')
                ->where('is_current', true)
                ->with('quarter:id,quarter_number,quarter_type')
                ->first();

            if ($pendingAllocation && $pendingAllocation->quarter) {
                // Officer has a pending allocation
                $quarter = $pendingAllocation->quarter;
                $officerData['has_pending_allocation'] = true;
                $officerData['pending_quarter_number'] = $quarter->quarter_number;
                $officerData['pending_quarter_type'] = $quarter->quarter_type;
                $officerData['pending_quarter_display'] = $quarter->quarter_number . ($quarter->quarter_type ? ' - ' . $quarter->quarter_type : '');
                $officerData['pending_allocation_id'] = $pendingAllocation->id;
            } else {
                $officerData['has_pending_allocation'] = false;
            }

            // Add current quarter information if officer is quartered and has an accepted allocation
            if ($officer->quartered && $officer->currentQuarter && $officer->currentQuarter->status === 'ACCEPTED') {
                $quarter = $officer->currentQuarter->quarter;
                if ($quarter) {
                    $officerData['current_quarter_number'] = $quarter->quarter_number;
                    $officerData['current_quarter_type'] = $quarter->quarter_type;
                    // Format: "Quarter Number - Type" or just "Quarter Number" if no type
                    $officerData['current_quarter_display'] = $quarter->quarter_number . ($quarter->quarter_type ? ' - ' . $quarter->quarter_type : '');
                }
            }

            return $officerData;
        });

        return $this->paginatedResponse(
            $transformedOfficers->toArray(),
            [
                'current_page' => $officers->currentPage(),
                'per_page' => $officers->perPage(),
                'total' => $officers->total(),
                'last_page' => $officers->lastPage(),
                'from' => $officers->firstItem(),
                'to' => $officers->lastItem(),
            ],
            [
                'first' => $officers->url(1),
                'last' => $officers->url($officers->lastPage()),
                'prev' => $officers->previousPageUrl(),
                'next' => $officers->nextPageUrl(),
            ]
        );
    }

    /**
     * Get officer details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $officer = Officer::with([
            'presentStation',
            'nextOfKin',
            'documents',
            'user.roles'
        ])->findOrFail($id);

        // Access control
        if ($user->hasRole('Officer') && $user->officer?->id != $officer->id) {
            return $this->errorResponse(
                'Access denied. You can only view your own record.',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        return $this->successResponse($officer);
    }

    /**
     * Create new officer (onboarding)
     */
    public function store(Request $request): JsonResponse
    {
        // This will be handled by a separate onboarding endpoint
        // Implementation will be added when we create Form Requests
        return $this->errorResponse('Use /officers/onboarding endpoint', null, 400);
    }

    /**
     * Update officer information
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $officer = Officer::findOrFail($id);

        // Access control
        if ($user->hasRole('Officer') && $user->officer?->id != $officer->id) {
            return $this->errorResponse(
                'Access denied. You can only update your own record.',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // Officers can only update limited fields
        if ($user->hasRole('Officer')) {
            $request->validate([
                'phone_number' => 'sometimes|string',
                'profile_picture' => 'sometimes|file|image|max:2048',
            ]);

            if ($request->has('phone_number')) {
                $officer->phone_number = $request->phone_number;
            }

            if ($request->hasFile('profile_picture')) {
                // Handle file upload
                $path = $request->file('profile_picture')->store('profiles', 'public');
                $officer->profile_picture_url = $path;
            }

            $officer->save();
        } else {
            // HRD/Staff Officer can update more fields
            // Full validation will be in Form Request
            $officer->update($request->only([
                'present_station',
                'date_posted_to_station',
                'unit',
            ]));
        }

        return $this->successResponse($officer->fresh(), 'Officer updated successfully');
    }

    /**
     * Update officer quartered status (Building Unit)
     */
    public function updateQuarteredStatus(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can update quartered status',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'quartered' => 'required|boolean',
        ]);

        $officer = Officer::findOrFail($id);

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

        // Verify officer is in Building Unit's command
        if ($officer->present_station !== $commandId) {
            return $this->errorResponse(
                'You can only update quartered status for officers in your command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $officer->update([
            'quartered' => $request->boolean('quartered'),
        ]);

        // Notify officer about quartered status update
        $notificationService = app(NotificationService::class);
        $notificationService->notifyQuarteredStatusUpdated($officer, $request->boolean('quartered'));

        return $this->successResponse([
            'id' => $officer->id,
            'service_number' => $officer->service_number,
            'quartered' => $officer->quartered,
        ], 'Quartered status updated successfully');
    }

    /**
     * Bulk update quartered status (Building Unit)
     */
    public function bulkUpdateQuarteredStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can bulk update quartered status',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_ids' => 'required|array',
            'officer_ids.*' => 'required|exists:officers,id',
            'quartered' => 'required|boolean',
        ]);

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

        // Verify all officers are in Building Unit's command
        $officers = Officer::whereIn('id', $request->officer_ids)
            ->where('present_station', $commandId)
            ->get();

        if ($officers->count() !== count($request->officer_ids)) {
            return $this->errorResponse(
                'Some officers are not in your command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $quartered = $request->boolean('quartered');
        Officer::whereIn('id', $request->officer_ids)->update([
            'quartered' => $quartered,
        ]);

        // Notify all affected officers
        $notificationService = app(NotificationService::class);
        $officers = Officer::whereIn('id', $request->officer_ids)->with('user')->get();
        foreach ($officers as $officer) {
            if ($officer->user) {
                $notificationService->notifyQuarteredStatusUpdated($officer, $quartered);
            }
        }

        return $this->successResponse([
            'updated_count' => count($request->officer_ids),
            'quartered' => $quartered,
        ], 'Quartered status updated successfully for ' . count($request->officer_ids) . ' officer(s)');
    }
}


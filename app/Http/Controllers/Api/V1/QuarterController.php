<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Officer;
use App\Models\OfficerQuarter;
use App\Models\Quarter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuarterController extends BaseController
{
    /**
     * List quarters
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Quarter::where('is_active', true);

        // Building Unit sees quarters in their command
        if ($user->hasRole('Building Unit') && $user->officer?->present_station) {
            $query->where('command_id', $user->officer->present_station);
        } elseif ($request->has('command_id')) {
            $query->where('command_id', $request->command_id);
        }

        if ($request->has('is_occupied')) {
            $query->where('is_occupied', $request->boolean('is_occupied'));
        }

        $quarters = $query->with(['officerQuarters' => function ($q) {
            $q->where('is_current', true)->with('officer:id,service_number,initials,surname');
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

        $commandId = $user->officer?->present_station;
        
        $query = Quarter::where('is_active', true);
        if ($commandId) {
            $query->where('command_id', $commandId);
        }

        $totalQuarters = $query->count();
        $occupiedQuarters = (clone $query)->where('is_occupied', true)->count();
        $availableQuarters = $totalQuarters - $occupiedQuarters;

        return $this->successResponse([
            'total_quarters' => $totalQuarters,
            'occupied' => $occupiedQuarters,
            'available' => $availableQuarters,
        ]);
    }

    /**
     * Create quarter (Building Unit)
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can create quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'quarter_number' => 'required|string|max:50',
            'quarter_type' => 'required|string',
        ]);

        $quarter = Quarter::create([
            'command_id' => $request->command_id,
            'quarter_number' => $request->quarter_number,
            'quarter_type' => $request->quarter_type,
            'is_occupied' => false,
            'is_active' => true,
        ]);

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
        if (!$request->user()->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can allocate quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'quarter_id' => 'required|exists:quarters,id',
            'allocation_date' => 'sometimes|date',
        ]);

        $quarter = Quarter::findOrFail($request->quarter_id);

        if ($quarter->is_occupied) {
            return $this->errorResponse(
                'Quarter is already occupied',
                null,
                400,
                'QUARTER_OCCUPIED'
            );
        }

        // Deallocate previous quarter if any
        OfficerQuarter::where('officer_id', $request->officer_id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Create new allocation
        OfficerQuarter::create([
            'officer_id' => $request->officer_id,
            'quarter_id' => $request->quarter_id,
            'allocated_date' => $request->allocation_date ?? now(),
            'is_current' => true,
            'allocated_by' => $request->user()->id,
        ]);

        // Mark quarter as occupied
        $quarter->update(['is_occupied' => true]);

        // Update officer's quartered status
        $officer = Officer::findOrFail($request->officer_id);
        $officer->update(['quartered' => true]);

        return $this->successResponse([
            'officer_id' => $request->officer_id,
            'quarter_id' => $request->quarter_id,
        ], 'Quarter allocated successfully');
    }

    /**
     * Deallocate quarter (Building Unit)
     */
    public function deallocate(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('Building Unit')) {
            return $this->errorResponse(
                'Only Building Unit can deallocate quarters',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        // If officer_id is provided in request body, find allocation by quarter_id and officer_id
        if ($request->has('officer_id')) {
            $allocation = OfficerQuarter::where('quarter_id', $id)
                ->where('officer_id', $request->officer_id)
                ->where('is_current', true)
                ->firstOrFail();
        } else {
            // Otherwise, find by allocation ID
            $allocation = OfficerQuarter::findOrFail($id);
        }

        $allocation->update([
            'is_current' => false,
            'deallocation_date' => now(),
        ]);

        // Mark quarter as available
        $allocation->quarter->update(['is_occupied' => false]);

        // Update officer's quartered status
        $officer = $allocation->officer;
        $officer->update(['quartered' => false]);

        return $this->successResponse([
            'id' => $allocation->id,
            'quarter_id' => $allocation->quarter_id,
            'officer_id' => $allocation->officer_id,
        ], 'Quarter deallocated successfully');
    }
}


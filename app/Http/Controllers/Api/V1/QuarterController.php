<?php

namespace App\Http\Controllers\Api\V1;

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
        $query = Quarter::where('is_active', true);

        if ($request->has('command_id')) {
            $query->where('command_id', $request->command_id);
        }

        if ($request->has('is_occupied')) {
            $query->where('is_occupied', $request->boolean('is_occupied'));
        }

        $quarters = $query->get();

        return $this->successResponse($quarters);
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
            'address' => 'required|string',
        ]);

        $quarter = Quarter::create([
            'command_id' => $request->command_id,
            'quarter_number' => $request->quarter_number,
            'quarter_type' => $request->quarter_type,
            'address' => $request->address,
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
            'allocation_date' => 'required|date',
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
            'allocation_date' => $request->allocation_date,
            'is_current' => true,
        ]);

        // Mark quarter as occupied
        $quarter->update(['is_occupied' => true]);

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

        $allocation = OfficerQuarter::findOrFail($id);

        $allocation->update([
            'is_current' => false,
            'deallocation_date' => now(),
        ]);

        // Mark quarter as available
        $allocation->quarter->update(['is_occupied' => false]);

        return $this->successResponse([
            'id' => $allocation->id,
        ], 'Quarter deallocated successfully');
    }
}


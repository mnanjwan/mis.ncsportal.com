<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Officer;
use App\Models\OfficerPosting;
use App\Models\StaffOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffOrderController extends BaseController
{
    /**
     * List staff orders (HRD)
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can view staff orders',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $query = StaffOrder::with(['officer', 'fromCommand', 'toCommand']);

        if ($request->has('status')) {
            $query->where('order_type', $request->status);
        }

        if ($request->has('command_id')) {
            $query->where('to_command_id', $request->command_id);
        }

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        $perPage = $request->get('per_page', 20);
        $orders = $query->paginate($perPage);

        return $this->paginatedResponse(
            $orders->items(),
            [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ]
        );
    }

    /**
     * Create staff order (HRD)
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can create staff orders',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'to_command_id' => 'required|exists:commands,id',
            'effective_date' => 'required|date|after_or_equal:today',
        ]);

        $officer = Officer::findOrFail($request->officer_id);
        $fromCommandId = $officer->present_station;

        // Generate order number
        $orderNumber = 'SO/' . now()->year . '/' . str_pad(StaffOrder::whereYear('created_at', now()->year)->count() + 1, 4, '0', STR_PAD_LEFT);

        $staffOrder = StaffOrder::create([
            'order_number' => $orderNumber,
            'officer_id' => $request->officer_id,
            'from_command_id' => $fromCommandId,
            'to_command_id' => $request->to_command_id,
            'effective_date' => $request->effective_date,
            'order_type' => 'STAFF_ORDER',
            'created_by' => $request->user()->id,
        ]);

        // Create posting record (pending until Staff Officer documents arrival)
        OfficerPosting::create([
            'officer_id' => $request->officer_id,
            'command_id' => $request->to_command_id,
            'staff_order_id' => $staffOrder->id,
            'posting_date' => $request->effective_date,
            'is_current' => false,
            'documented_by' => null,
            'documented_at' => null,
        ]);

        return $this->successResponse([
            'id' => $staffOrder->id,
            'order_number' => $staffOrder->order_number,
            'status' => 'ACTIVE',
        ], 'Staff order created successfully', 201);
    }
}


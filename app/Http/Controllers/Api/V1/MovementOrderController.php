<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\MovementOrder;
use App\Models\Officer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovementOrderController extends BaseController
{
    /**
     * List movement orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = MovementOrder::with(['officer', 'fromCommand', 'toCommand']);

        // Role-based filtering
        if ($user->hasRole('Staff Officer')) {
            if ($user->officer?->present_station) {
                $query->where(function ($q) use ($user) {
                    $q->where('from_command_id', $user->officer->present_station)
                      ->orWhere('to_command_id', $user->officer->present_station);
                });
            }
        }

        if ($request->has('command_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_command_id', $request->command_id)
                  ->orWhere('to_command_id', $request->command_id);
            });
        }

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        if ($request->has('status')) {
            $query->where('order_type', $request->status);
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
     * Create movement order (HRD)
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can create movement orders',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'to_command_id' => 'required|exists:commands,id',
            'effective_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string',
        ]);

        $officer = Officer::findOrFail($request->officer_id);
        $fromCommandId = $officer->present_station;

        // Generate order number
        $orderNumber = 'MO/' . now()->year . '/' . str_pad(
            MovementOrder::whereYear('created_at', now()->year)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        $movementOrder = MovementOrder::create([
            'order_number' => $orderNumber,
            'officer_id' => $request->officer_id,
            'from_command_id' => $fromCommandId,
            'to_command_id' => $request->to_command_id,
            'effective_date' => $request->effective_date,
            'order_type' => 'MOVEMENT_ORDER',
            'reason' => $request->reason,
            'created_by' => $request->user()->id,
        ]);

        return $this->successResponse([
            'id' => $movementOrder->id,
            'order_number' => $movementOrder->order_number,
            'status' => 'ACTIVE',
        ], 'Movement order created successfully', 201);
    }

    /**
     * Get movement order details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $order = MovementOrder::with(['officer', 'fromCommand', 'toCommand'])->findOrFail($id);

        return $this->successResponse($order);
    }
}


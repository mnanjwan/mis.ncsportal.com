<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PostingWorkflowService;

class MovementOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = \App\Models\MovementOrder::with(['manningRequest', 'createdBy']);

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'order_number' => 'order_number',
            'criteria' => 'criteria_months_at_station',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($column, $order);

        $orders = $query->paginate(20)->withQueryString();
        
        return view('dashboards.hrd.movement-orders', compact('orders'));
    }

    public function create()
    {
        // Get all manning requests (show SUBMITTED, APPROVED, or DRAFT statuses)
        // Status enum: DRAFT, SUBMITTED, APPROVED, REJECTED, FULFILLED
        $manningRequests = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
            ->with('command')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Generate order number (format: MO-YYYY-MMDD-XXX)
        $lastOrder = \App\Models\MovementOrder::orderBy('created_at', 'desc')->first();
        $orderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        return view('forms.movement-order.create', compact('manningRequests', 'orderNumber'));
    }

    public function store(Request $request)
    {
        // Auto-generate order number if not provided
        $orderNumber = $request->order_number;
        if (empty($orderNumber)) {
            $lastOrder = \App\Models\MovementOrder::orderBy('created_at', 'desc')->first();
            $orderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        }

        // Check if order number already exists
        if (\App\Models\MovementOrder::where('order_number', $orderNumber)->exists()) {
            $counter = 1;
            do {
                $newOrderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $counter++;
            } while (\App\Models\MovementOrder::where('order_number', $newOrderNumber)->exists());
            $orderNumber = $newOrderNumber;
        }

        $validated = $request->validate([
            'criteria_months_at_station' => 'required|integer|min:1',
            'manning_request_id' => 'nullable|exists:manning_requests,id',
            'status' => 'required|in:DRAFT,PUBLISHED,CANCELLED',
        ]);

        $validated['order_number'] = $orderNumber;
        $validated['created_by'] = auth()->id();
        
        try {
            $order = \App\Models\MovementOrder::create($validated);
            return redirect()->route('hrd.movement-orders')
                ->with('success', 'Movement order created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create movement order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest', 'createdBy', 'postings.officer.presentStation', 'postings.command'])
            ->findOrFail($id);
        return view('dashboards.hrd.movement-order-show', compact('order'));
    }
}



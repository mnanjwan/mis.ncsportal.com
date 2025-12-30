<?php

namespace App\Http\Controllers;

use App\Models\InternalStaffOrder;
use App\Models\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalStaffOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Staff Officer');
    }

    /**
     * Get the command ID for the current Staff Officer
     */
    private function getStaffOfficerCommandId()
    {
        $user = Auth::user();
        
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        return $staffOfficerRole?->pivot->command_id ?? null;
    }

    /**
     * Display a listing of internal staff orders
     */
    public function index(Request $request)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $query = InternalStaffOrder::with(['command', 'preparedBy.officer'])
            ->where('command_id', $commandId);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate(20)->withQueryString();
        $command = Command::find($commandId);

        return view('dashboards.staff-officer.internal-staff-orders.index', compact('orders', 'command'));
    }

    /**
     * Show the form for creating a new internal staff order
     */
    public function create()
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $command = Command::find($commandId);

        // Generate order number (format: ISO-YYYY-MMDD-XXX)
        $lastOrder = InternalStaffOrder::where('command_id', $commandId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $year = date('Y');
        $monthDay = date('md');
        $sequence = $lastOrder && preg_match('/-(\d{3})$/', $lastOrder->order_number, $matches) 
            ? (int)$matches[1] + 1 
            : 1;
        
        $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        // Check if order number already exists, increment if needed
        while (InternalStaffOrder::where('order_number', $orderNumber)->exists()) {
            $sequence++;
            $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }

        return view('dashboards.staff-officer.internal-staff-orders.create', compact('command', 'orderNumber'));
    }

    /**
     * Store a newly created internal staff order
     */
    public function store(Request $request)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        // Auto-generate order number if not provided
        $orderNumber = $request->order_number;
        if (empty($orderNumber)) {
            $lastOrder = InternalStaffOrder::where('command_id', $commandId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $year = date('Y');
            $monthDay = date('md');
            $sequence = $lastOrder && preg_match('/-(\d{3})$/', $lastOrder->order_number, $matches) 
                ? (int)$matches[1] + 1 
                : 1;
            
            $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }

        // Check if order number already exists
        if (InternalStaffOrder::where('order_number', $orderNumber)->exists()) {
            // If exists, generate a new one
            $year = date('Y');
            $monthDay = date('md');
            $sequence = 1;
            do {
                $newOrderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $sequence++;
            } while (InternalStaffOrder::where('order_number', $newOrderNumber)->exists());
            $orderNumber = $newOrderNumber;
        }

        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:internal_staff_orders,order_number',
            'order_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['command_id'] = $commandId;
        $validated['prepared_by'] = Auth::id();

        try {
            $order = InternalStaffOrder::create($validated);

            return redirect()->route('staff-officer.internal-staff-orders.index')
                ->with('success', 'Internal Staff Order created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create internal staff order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified internal staff order
     */
    public function show($id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::with(['command', 'preparedBy.officer'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        return view('dashboards.staff-officer.internal-staff-orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified internal staff order
     */
    public function edit($id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::with(['command'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        return view('dashboards.staff-officer.internal-staff-orders.edit', compact('order'));
    }

    /**
     * Update the specified internal staff order
     */
    public function update(Request $request, $id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:internal_staff_orders,order_number,' . $id,
            'order_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            $order->update($validated);

            return redirect()->route('staff-officer.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update internal staff order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified internal staff order
     */
    public function destroy($id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        try {
            $order->delete();

            return redirect()->route('staff-officer.internal-staff-orders.index')
                ->with('success', 'Internal Staff Order deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete internal staff order: ' . $e->getMessage());
        }
    }
}

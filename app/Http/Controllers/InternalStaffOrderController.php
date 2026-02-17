<?php

namespace App\Http\Controllers;

use App\Models\InternalStaffOrder;
use App\Models\Command;
use App\Models\Officer;
use App\Models\DutyRoster;
use App\Models\RosterAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

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
     * Get officer's current unit and role from active duty roster
     */
    private function getOfficerCurrentAssignment($officerId, $commandId)
    {
        // Find active (APPROVED) duty roster for this command
        $activeRoster = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->where(function($query) use ($officerId) {
                $query->where('oic_officer_id', $officerId)
                      ->orWhere('second_in_command_officer_id', $officerId)
                      ->orWhereHas('assignments', function($q) use ($officerId) {
                          $q->where('officer_id', $officerId);
                      });
            })
            ->with(['assignments', 'oicOfficer', 'secondInCommandOfficer'])
            ->first();

        if (!$activeRoster) {
            return [
                'unit' => null,
                'role' => null,
                'roster_id' => null,
            ];
        }

        // Determine role
        $role = 'Member';
        if ($activeRoster->oic_officer_id == $officerId) {
            $role = 'OIC';
        } elseif ($activeRoster->second_in_command_officer_id == $officerId) {
            $role = '2IC';
        }

        return [
            'unit' => $activeRoster->unit,
            'role' => $role,
            'roster_id' => $activeRoster->id,
        ];
    }

    /**
     * Check for OIC/2IC conflicts in target unit
     */
    private function checkTargetUnitConflicts($targetUnit, $targetRole, $commandId, $excludeOfficerId = null)
    {
        if (!in_array($targetRole, ['OIC', '2IC'])) {
            return null; // No conflict for Member role
        }

        // Find active roster for target unit
        $targetRoster = DutyRoster::where('command_id', $commandId)
            ->where('unit', $targetUnit)
            ->where('status', 'APPROVED')
            ->first();

        if (!$targetRoster) {
            return null; // No active roster, no conflict
        }

        $conflict = null;

        if ($targetRole === 'OIC' && $targetRoster->oic_officer_id) {
            $currentOIC = Officer::find($targetRoster->oic_officer_id);
            if ($currentOIC && $currentOIC->id != $excludeOfficerId) {
                $conflict = [
                    'type' => 'OIC',
                    'officer' => $currentOIC,
                    'roster_id' => $targetRoster->id,
                ];
            }
        } elseif ($targetRole === '2IC' && $targetRoster->second_in_command_officer_id) {
            $current2IC = Officer::find($targetRoster->second_in_command_officer_id);
            if ($current2IC && $current2IC->id != $excludeOfficerId) {
                $conflict = [
                    'type' => '2IC',
                    'officer' => $current2IC,
                    'roster_id' => $targetRoster->id,
                ];
            }
        }

        return $conflict;
    }

    /**
     * Get available units for the command
     */
    private function getAvailableUnits($commandId)
    {
        // Get predefined units (same as Create Duty Roster)
        $predefinedUnits = [
            'Revenue',
            'Admin',
            'Enforcement',
            'ICT',
            'Accounts',
            'Transport and Logistics',
            'Medical',
            'Escort',
            'Guard Duty'
        ];

        // Get custom units from all rosters (any status) for this command
        $customUnits = DutyRoster::where('command_id', $commandId)
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->whereNotIn('unit', $predefinedUnits)
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->filter()
            ->values()
            ->toArray();

        // Merge predefined and custom units, then sort
        $allUnits = array_merge($predefinedUnits, $customUnits);
        sort($allUnits);

        return $allUnits;
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

        $query = InternalStaffOrder::with(['command', 'officer', 'preparedBy.officer'])
            ->where('command_id', $commandId);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('officer', function($officerQuery) use ($search) {
                      $officerQuery->where('service_number', 'like', "%{$search}%")
                                   ->orWhere('surname', 'like', "%{$search}%")
                                   ->orWhere('initials', 'like', "%{$search}%");
                  });
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

        // Get officers in this command
        $officers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        // Get available units
        $availableUnits = $this->getAvailableUnits($commandId);

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

        return view('dashboards.staff-officer.internal-staff-orders.create', compact(
            'command', 
            'orderNumber', 
            'officers',
            'availableUnits'
        ));
    }

    /**
     * Get officer's current assignment (AJAX endpoint)
     */
    public function getOfficerAssignment(Request $request)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return response()->json(['error' => 'Command not found'], 404);
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
        ]);

        $officer = Officer::findOrFail($request->officer_id);
        
        if ($officer->present_station != $commandId) {
            return response()->json(['error' => 'Officer is not in your command'], 403);
        }

        $assignment = $this->getOfficerCurrentAssignment($officer->id, $commandId);
        $command = Command::find($commandId);

        return response()->json([
            'current_command' => $command->name ?? 'N/A',
            'current_unit' => $assignment['unit'] ?? 'Not Assigned',
            'current_role' => $assignment['role'] ?? 'Not Assigned',
            'roster_id' => $assignment['roster_id'],
        ]);
    }

    /**
     * Check target unit conflicts (AJAX endpoint)
     */
    public function checkConflicts(Request $request)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return response()->json(['error' => 'Command not found'], 404);
        }

        $request->validate([
            'target_unit' => 'required|string',
            'target_role' => 'required|in:OIC,2IC,Member',
            'officer_id' => 'nullable|exists:officers,id',
        ]);

        $conflict = $this->checkTargetUnitConflicts(
            $request->target_unit,
            $request->target_role,
            $commandId,
            $request->officer_id
        );

        if ($conflict) {
            return response()->json([
                'has_conflict' => true,
                'conflict_type' => $conflict['type'],
                'current_holder' => [
                    'id' => $conflict['officer']->id,
                    'name' => ($conflict['officer']->initials ?? '') . ' ' . ($conflict['officer']->surname ?? ''),
                    'service_number' => $conflict['officer']->service_number ?? 'N/A',
                    'rank' => $conflict['officer']->substantive_rank ?? 'N/A',
                ],
                'message' => "This action will replace the current {$conflict['type']} in the selected unit.",
            ]);
        }

        return response()->json([
            'has_conflict' => false,
        ]);
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
            'officer_id' => 'required|exists:officers,id',
            'target_unit' => 'required|string|max:255',
            'target_role' => 'required|in:OIC,2IC,Member',
            'description' => 'nullable|string',
        ]);

        $officer = Officer::findOrFail($validated['officer_id']);
        if ($officer->present_station != $commandId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Selected officer is not in your command.');
        }

        $currentAssignment = $this->getOfficerCurrentAssignment($officer->id, $commandId);

        // Check if officer is already in target unit
        if ($currentAssignment['unit'] === $validated['target_unit']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Officer is already assigned to the target unit.');
        }

        // Check for conflicts
        $conflict = $this->checkTargetUnitConflicts(
            $validated['target_unit'],
            $validated['target_role'],
            $commandId,
            $officer->id
        );

        $validated['command_id'] = $commandId;
        $validated['prepared_by'] = Auth::id();
        $validated['current_unit'] = $currentAssignment['unit'];
        $validated['current_role'] = $currentAssignment['role'];
        $validated['status'] = 'DRAFT';

        try {
            $order = InternalStaffOrder::create($validated);

            return redirect()->route('staff-officer.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order created successfully! You can now submit it for approval.');
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

        $order = InternalStaffOrder::with(['command', 'officer', 'preparedBy.officer'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        // Get conflict information if applicable
        $conflict = null;
        if (in_array($order->target_role, ['OIC', '2IC'])) {
            $conflict = $this->checkTargetUnitConflicts(
                $order->target_unit,
                $order->target_role,
                $commandId,
                $order->officer_id
            );
        }

        return view('dashboards.staff-officer.internal-staff-orders.show', compact('order', 'conflict'));
    }

    /**
     * Submit order for approval
     */
    public function submit(Request $request, $id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->back()
                ->with('error', 'Only DRAFT orders can be submitted for approval.');
        }

        // Verify all required fields
        if (!$order->officer_id || !$order->target_unit || !$order->target_role) {
            return redirect()->back()
                ->with('error', 'Order is incomplete. Please ensure all fields are filled.');
        }

        try {
            $order->update(['status' => 'PENDING_APPROVAL']);

            // Notify DC Admins
            $notificationService = app(NotificationService::class);
            if (method_exists($notificationService, 'notifyInternalStaffOrderSubmitted')) {
                $notificationService->notifyInternalStaffOrderSubmitted($order);
            }

            return redirect()->route('staff-officer.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order submitted for DC Admin approval.');
        } catch (\Exception $e) {
            Log::error('Failed to submit internal staff order: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit order: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified internal staff order (only DRAFT)
     */
    public function edit($id)
    {
        $commandId = $this->getStaffOfficerCommandId();
        
        if (!$commandId) {
            return redirect()->route('staff-officer.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::with(['officer'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->route('staff-officer.internal-staff-orders.show', $order->id)
                ->with('error', 'Only DRAFT orders can be edited.');
        }

        $command = Command::find($commandId);
        $officers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        $availableUnits = $this->getAvailableUnits($commandId);

        return view('dashboards.staff-officer.internal-staff-orders.edit', compact(
            'order', 
            'command', 
            'officers',
            'availableUnits'
        ));
    }

    /**
     * Update the specified internal staff order (only DRAFT)
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

        if ($order->status !== 'DRAFT') {
            return redirect()->route('staff-officer.internal-staff-orders.show', $order->id)
                ->with('error', 'Only DRAFT orders can be edited.');
        }

        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:internal_staff_orders,order_number,' . $id,
            'order_date' => 'required|date',
            'officer_id' => 'required|exists:officers,id',
            'target_unit' => 'required|string|max:255',
            'target_role' => 'required|in:OIC,2IC,Member',
            'description' => 'nullable|string',
        ]);

        $officer = Officer::findOrFail($validated['officer_id']);
        if ($officer->present_station != $commandId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Selected officer is not in your command.');
        }

        $currentAssignment = $this->getOfficerCurrentAssignment($officer->id, $commandId);

        // Check if officer is already in target unit
        if ($currentAssignment['unit'] === $validated['target_unit'] && $order->officer_id == $validated['officer_id']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Officer is already assigned to the target unit.');
        }

        $validated['current_unit'] = $currentAssignment['unit'];
        $validated['current_role'] = $currentAssignment['role'];

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
     * Remove the specified internal staff order (only DRAFT)
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

        if ($order->status !== 'DRAFT') {
            return redirect()->back()
                ->with('error', 'Only DRAFT orders can be deleted.');
        }

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

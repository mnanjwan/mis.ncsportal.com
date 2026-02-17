<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\InternalStaffOrder;
use App\Models\Command;
use App\Models\Officer;
use App\Models\DutyRoster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

/**
 * CD Internal Staff Order Controller
 *
 * CD handles internal posting for Transport officers within the command,
 * mirroring the Staff Officer flow for normal officers. Staff Officer handles
 * non-Transport officers; CD handles Transport officers only.
 */
class CdInternalStaffOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:CD');
    }

    private function getCdCommandId()
    {
        $user = Auth::user();
        $cdRole = $user->roles()
            ->where('name', 'CD')
            ->wherePivot('is_active', true)
            ->first();
        return $cdRole?->pivot->command_id ?? null;
    }

    private function getOfficerCurrentAssignment($officerId, $commandId)
    {
        $activeRoster = DutyRoster::where('command_id', $commandId)
            ->where('status', 'APPROVED')
            ->where(function ($query) use ($officerId) {
                $query->where('oic_officer_id', $officerId)
                    ->orWhere('second_in_command_officer_id', $officerId)
                    ->orWhereHas('assignments', fn ($q) => $q->where('officer_id', $officerId));
            })
            ->with(['assignments', 'oicOfficer', 'secondInCommandOfficer'])
            ->first();

        if (!$activeRoster) {
            return ['unit' => null, 'role' => null, 'roster_id' => null];
        }

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

    private function checkTargetUnitConflicts($targetUnit, $targetRole, $commandId, $excludeOfficerId = null)
    {
        if (!in_array($targetRole, ['OIC', '2IC'])) {
            return null;
        }

        $targetRoster = DutyRoster::where('command_id', $commandId)
            ->where('unit', $targetUnit)
            ->where('status', 'APPROVED')
            ->first();

        if (!$targetRoster) {
            return null;
        }

        $conflict = null;
        if ($targetRole === 'OIC' && $targetRoster->oic_officer_id) {
            $currentOIC = Officer::find($targetRoster->oic_officer_id);
            if ($currentOIC && $currentOIC->id != $excludeOfficerId) {
                $conflict = ['type' => 'OIC', 'officer' => $currentOIC, 'roster_id' => $targetRoster->id];
            }
        } elseif ($targetRole === '2IC' && $targetRoster->second_in_command_officer_id) {
            $current2IC = Officer::find($targetRoster->second_in_command_officer_id);
            if ($current2IC && $current2IC->id != $excludeOfficerId) {
                $conflict = ['type' => '2IC', 'officer' => $current2IC, 'roster_id' => $targetRoster->id];
            }
        }

        return $conflict;
    }

    private function getAvailableUnits($commandId)
    {
        $predefinedUnits = [
            'Revenue', 'Admin', 'Enforcement', 'ICT', 'Accounts',
            'Transport and Logistics', 'Medical', 'Escort', 'Guard Duty',
        ];
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
        $allUnits = array_merge($predefinedUnits, $customUnits);
        sort($allUnits);
        return $allUnits;
    }

    public function index(Request $request)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $query = InternalStaffOrder::with(['command', 'officer', 'preparedBy.officer'])
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'));

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('officer', function ($oq) use ($search) {
                        $oq->where('service_number', 'like', "%{$search}%")
                            ->orWhere('surname', 'like', "%{$search}%")
                            ->orWhere('initials', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate(20)->withQueryString();
        $command = Command::find($commandId);

        return view('dashboards.fleet.cd-internal-staff-orders.index', compact('orders', 'command'));
    }

    public function create()
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $command = Command::find($commandId);
        $officers = Officer::where('present_station', $commandId)
            ->where('unit', 'Transport')
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        $availableUnits = $this->getAvailableUnits($commandId);

        $lastOrder = InternalStaffOrder::where('command_id', $commandId)
            ->orderBy('created_at', 'desc')
            ->first();
        $year = date('Y');
        $monthDay = date('md');
        $sequence = $lastOrder && preg_match('/-(\d{3})$/', $lastOrder->order_number, $matches)
            ? (int) $matches[1] + 1
            : 1;
        $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        while (InternalStaffOrder::where('order_number', $orderNumber)->exists()) {
            $sequence++;
            $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }

        return view('dashboards.fleet.cd-internal-staff-orders.create', compact(
            'command', 'orderNumber', 'officers', 'availableUnits'
        ));
    }

    public function getOfficerAssignment(Request $request)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return response()->json(['error' => 'Command not found'], 404);
        }

        $request->validate(['officer_id' => 'required|exists:officers,id']);
        $officer = Officer::findOrFail($request->officer_id);

        if ($officer->present_station != $commandId) {
            return response()->json(['error' => 'Officer is not in your command'], 403);
        }
        if ($officer->unit !== 'Transport') {
            return response()->json(['error' => 'Only Transport officers can be posted via CD internal staff orders'], 403);
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

    public function checkConflicts(Request $request)
    {
        $commandId = $this->getCdCommandId();
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

        return response()->json(['has_conflict' => false]);
    }

    public function store(Request $request)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $orderNumber = $request->order_number;
        if (empty($orderNumber)) {
            $lastOrder = InternalStaffOrder::where('command_id', $commandId)->orderBy('created_at', 'desc')->first();
            $year = date('Y');
            $monthDay = date('md');
            $sequence = $lastOrder && preg_match('/-(\d{3})$/', $lastOrder->order_number, $matches)
                ? (int) $matches[1] + 1
                : 1;
            $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }
        if (InternalStaffOrder::where('order_number', $orderNumber)->exists()) {
            $sequence = 1;
            do {
                $orderNumber = 'ISO-' . date('Y') . '-' . date('md') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $sequence++;
            } while (InternalStaffOrder::where('order_number', $orderNumber)->exists());
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
            return redirect()->back()->withInput()->with('error', 'Selected officer is not in your command.');
        }
        if ($officer->unit !== 'Transport') {
            return redirect()->back()->withInput()->with('error', 'Only Transport officers can be posted via CD internal staff orders.');
        }

        $currentAssignment = $this->getOfficerCurrentAssignment($officer->id, $commandId);
        if ($currentAssignment['unit'] === $validated['target_unit']) {
            return redirect()->back()->withInput()->with('error', 'Officer is already assigned to the target unit.');
        }

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
            return redirect()->route('fleet.cd.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order created successfully! You can now submit it for approval.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create internal staff order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::with(['command', 'officer', 'preparedBy.officer'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->firstOrFail();

        $conflict = null;
        if (in_array($order->target_role, ['OIC', '2IC'])) {
            $conflict = $this->checkTargetUnitConflicts(
                $order->target_unit,
                $order->target_role,
                $commandId,
                $order->officer_id
            );
        }

        return view('dashboards.fleet.cd-internal-staff-orders.show', compact('order', 'conflict'));
    }

    public function submit(Request $request, $id)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT orders can be submitted for approval.');
        }
        if (!$order->officer_id || !$order->target_unit || !$order->target_role) {
            return redirect()->back()->with('error', 'Order is incomplete. Please ensure all fields are filled.');
        }

        try {
            $order->update(['status' => 'PENDING_APPROVAL']);
            $notificationService = app(NotificationService::class);
            if (method_exists($notificationService, 'notifyInternalStaffOrderSubmitted')) {
                $notificationService->notifyInternalStaffOrderSubmitted($order);
            }
            return redirect()->route('fleet.cd.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order submitted for DC Admin approval.');
        } catch (\Exception $e) {
            Log::error('Failed to submit internal staff order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit order: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::with(['officer'])
            ->where('id', $id)
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->route('fleet.cd.internal-staff-orders.show', $order->id)
                ->with('error', 'Only DRAFT orders can be edited.');
        }

        $command = Command::find($commandId);
        $officers = Officer::where('present_station', $commandId)
            ->where('unit', 'Transport')
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();
        $availableUnits = $this->getAvailableUnits($commandId);

        return view('dashboards.fleet.cd-internal-staff-orders.edit', compact('order', 'command', 'officers', 'availableUnits'));
    }

    public function update(Request $request, $id)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->route('fleet.cd.internal-staff-orders.show', $order->id)
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
            return redirect()->back()->withInput()->with('error', 'Selected officer is not in your command.');
        }
        if ($officer->unit !== 'Transport') {
            return redirect()->back()->withInput()->with('error', 'Only Transport officers can be posted via CD internal staff orders.');
        }

        $currentAssignment = $this->getOfficerCurrentAssignment($officer->id, $commandId);
        if ($currentAssignment['unit'] === $validated['target_unit'] && $order->officer_id == $validated['officer_id']) {
            return redirect()->back()->withInput()->with('error', 'Officer is already assigned to the target unit.');
        }

        $validated['current_unit'] = $currentAssignment['unit'];
        $validated['current_role'] = $currentAssignment['role'];

        try {
            $order->update($validated);
            return redirect()->route('fleet.cd.internal-staff-orders.show', $order->id)
                ->with('success', 'Internal Staff Order updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update internal staff order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $commandId = $this->getCdCommandId();
        if (!$commandId) {
            return redirect()->route('fleet.cd.dashboard')
                ->with('error', 'You are not assigned to a command. Please contact HRD.');
        }

        $order = InternalStaffOrder::where('id', $id)
            ->where('command_id', $commandId)
            ->whereHas('officer', fn ($q) => $q->where('unit', 'Transport'))
            ->firstOrFail();

        if ($order->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT orders can be deleted.');
        }

        try {
            $order->delete();
            return redirect()->route('fleet.cd.internal-staff-orders.index')
                ->with('success', 'Internal Staff Order deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete internal staff order: ' . $e->getMessage());
        }
    }
}

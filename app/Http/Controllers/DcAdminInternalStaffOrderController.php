<?php

namespace App\Http\Controllers;

use App\Models\InternalStaffOrder;
use App\Models\DutyRoster;
use App\Models\RosterAssignment;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class DcAdminInternalStaffOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:DC Admin');
    }

    /**
     * Get the command ID for the current DC Admin
     */
    private function getDcAdminCommandId()
    {
        $user = Auth::user();
        
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();
        
        return $dcAdminRole?->pivot->command_id ?? null;
    }

    /**
     * Display a listing of pending internal staff orders
     */
    public function index(Request $request)
    {
        $commandId = $this->getDcAdminCommandId();
        
        $query = InternalStaffOrder::with(['command', 'officer', 'preparedBy.officer'])
            ->where('status', 'PENDING_APPROVAL')
            ->orderBy('created_at', 'desc');

        // Filter by command if DC Admin is assigned to a command
        if ($commandId) {
            $query->where('command_id', $commandId);
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

        $orders = $query->paginate(20)->withQueryString();

        return view('dashboards.dc-admin.internal-staff-orders.index', compact('orders'));
    }

    /**
     * Display the specified internal staff order for approval
     */
    public function show($id)
    {
        $order = InternalStaffOrder::with([
            'command', 
            'officer', 
            'preparedBy.officer'
        ])->findOrFail($id);

        // Only show PENDING_APPROVAL orders
        if ($order->status !== 'PENDING_APPROVAL') {
            abort(403, 'This order is not pending approval');
        }

        // Check command access if DC Admin is assigned to a command
        $commandId = $this->getDcAdminCommandId();
        if ($commandId && $order->command_id != $commandId) {
            abort(403, 'You can only approve orders for your assigned command');
        }

        // Get conflict information if applicable
        $conflict = null;
        $outgoingOfficer = null;
        
        if (in_array($order->target_role, ['OIC', '2IC'])) {
            // Find active roster for target unit
            $targetRoster = DutyRoster::where('command_id', $order->command_id)
                ->where('unit', $order->target_unit)
                ->where('status', 'APPROVED')
                ->first();

            if ($targetRoster) {
                if ($order->target_role === 'OIC' && $targetRoster->oic_officer_id) {
                    $outgoingOfficer = Officer::find($targetRoster->oic_officer_id);
                } elseif ($order->target_role === '2IC' && $targetRoster->second_in_command_officer_id) {
                    $outgoingOfficer = Officer::find($targetRoster->second_in_command_officer_id);
                }
            }
        }

        return view('dashboards.dc-admin.internal-staff-orders.show', compact('order', 'conflict', 'outgoingOfficer'));
    }

    /**
     * Approve the internal staff order
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can approve internal staff orders');
        }

        $order = InternalStaffOrder::with(['officer', 'command'])->findOrFail($id);

        // Only allow approving PENDING_APPROVAL orders
        if ($order->status !== 'PENDING_APPROVAL') {
            return redirect()->back()
                ->with('error', 'Only PENDING_APPROVAL orders can be approved.');
        }

        // Check command access if DC Admin is assigned to a command
        $commandId = $this->getDcAdminCommandId();
        if ($commandId && $order->command_id != $commandId) {
            abort(403, 'You can only approve orders for your assigned command');
        }

        try {
            DB::beginTransaction();

            // Get target roster
            $targetRoster = DutyRoster::where('command_id', $order->command_id)
                ->where('unit', $order->target_unit)
                ->where('status', 'APPROVED')
                ->first();

            if (!$targetRoster) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Target unit does not have an active approved roster. Please ensure a roster exists for the target unit.');
            }

            $outgoingOfficer = null;

            // Handle role takeover if OIC/2IC
            if (in_array($order->target_role, ['OIC', '2IC'])) {
                if ($order->target_role === 'OIC' && $targetRoster->oic_officer_id) {
                    $outgoingOfficer = Officer::find($targetRoster->oic_officer_id);
                    
                    // Remove from OIC position
                    $targetRoster->oic_officer_id = null;
                    $targetRoster->save();
                    
                    // Add as regular member if not already in assignments
                    $existingAssignment = RosterAssignment::where('roster_id', $targetRoster->id)
                        ->where('officer_id', $outgoingOfficer->id)
                        ->first();
                    
                    if (!$existingAssignment) {
                        RosterAssignment::create([
                            'roster_id' => $targetRoster->id,
                            'officer_id' => $outgoingOfficer->id,
                        ]);
                    }
                } elseif ($order->target_role === '2IC' && $targetRoster->second_in_command_officer_id) {
                    $outgoingOfficer = Officer::find($targetRoster->second_in_command_officer_id);
                    
                    // Remove from 2IC position
                    $targetRoster->second_in_command_officer_id = null;
                    $targetRoster->save();
                    
                    // Add as regular member if not already in assignments
                    $existingAssignment = RosterAssignment::where('roster_id', $targetRoster->id)
                        ->where('officer_id', $outgoingOfficer->id)
                        ->first();
                    
                    if (!$existingAssignment) {
                        RosterAssignment::create([
                            'roster_id' => $targetRoster->id,
                            'officer_id' => $outgoingOfficer->id,
                        ]);
                    }
                }
            }

            // Assign new officer to target unit
            if ($order->target_role === 'OIC') {
                $targetRoster->oic_officer_id = $order->officer_id;
                $targetRoster->save();
            } elseif ($order->target_role === '2IC') {
                $targetRoster->second_in_command_officer_id = $order->officer_id;
                $targetRoster->save();
            } else {
                // Add as regular member
                $existingAssignment = RosterAssignment::where('roster_id', $targetRoster->id)
                    ->where('officer_id', $order->officer_id)
                    ->first();
                
                if (!$existingAssignment) {
                    RosterAssignment::create([
                        'roster_id' => $targetRoster->id,
                        'officer_id' => $order->officer_id,
                    ]);
                }
            }

            // Remove officer from current roster if applicable
            if ($order->current_unit) {
                $currentRoster = DutyRoster::where('command_id', $order->command_id)
                    ->where('unit', $order->current_unit)
                    ->where('status', 'APPROVED')
                    ->first();

                if ($currentRoster) {
                    // Remove from OIC/2IC position if applicable
                    if ($currentRoster->oic_officer_id == $order->officer_id) {
                        $currentRoster->oic_officer_id = null;
                        $currentRoster->save();
                    }
                    if ($currentRoster->second_in_command_officer_id == $order->officer_id) {
                        $currentRoster->second_in_command_officer_id = null;
                        $currentRoster->save();
                    }

                    // Remove from assignments
                    RosterAssignment::where('roster_id', $currentRoster->id)
                        ->where('officer_id', $order->officer_id)
                        ->delete();
                }
            }

            // Update order status
            $order->status = 'APPROVED';
            $order->approved_by = $user->id;
            $order->approved_at = now();
            $order->save();

            // Refresh order to load relationships
            $order->refresh();
            $order->load(['officer', 'command', 'preparedBy']);

            // Send notifications
            $notificationService = app(NotificationService::class);
            if (method_exists($notificationService, 'notifyInternalStaffOrderApproved')) {
                $notificationService->notifyInternalStaffOrderApproved($order, $outgoingOfficer);
            }

            DB::commit();

            return redirect()->route('dc-admin.internal-staff-orders')
                ->with('success', 'Internal Staff Order approved successfully. All affected officers and Staff Officer have been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve internal staff order: ' . $e->getMessage(), [
                'order_id' => $id,
                'error' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    /**
     * Reject the internal staff order
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can reject internal staff orders');
        }

        $order = InternalStaffOrder::findOrFail($id);

        // Only allow rejecting PENDING_APPROVAL orders
        if ($order->status !== 'PENDING_APPROVAL') {
            return redirect()->back()
                ->with('error', 'Only PENDING_APPROVAL orders can be rejected.');
        }

        // Check command access if DC Admin is assigned to a command
        $commandId = $this->getDcAdminCommandId();
        if ($commandId && $order->command_id != $commandId) {
            abort(403, 'You can only reject orders for your assigned command');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $order->status = 'REJECTED';
            $order->rejection_reason = $request->rejection_reason;
            $order->approved_by = $user->id;
            $order->approved_at = now();
            $order->save();

            // Refresh order to load relationships
            $order->refresh();
            $order->load(['officer', 'command', 'preparedBy']);

            // Notify Staff Officer about rejection
            $notificationService = app(NotificationService::class);
            if (method_exists($notificationService, 'notifyInternalStaffOrderRejected')) {
                $notificationService->notifyInternalStaffOrderRejected($order, $user, $request->rejection_reason);
            }

            DB::commit();

            return redirect()->route('dc-admin.internal-staff-orders')
                ->with('success', 'Internal Staff Order rejected. Staff Officer has been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject internal staff order: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }
}

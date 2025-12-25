<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\QuarterController as ApiQuarterController;
use App\Models\OfficerQuarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuarterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboards.building.quarters');
    }

    public function create()
    {
        return view('forms.quarter.create');
    }

    public function allocate()
    {
        return view('forms.quarter.allocate');
    }

    public function officers()
    {
        return view('dashboards.building.officers');
    }

    /**
     * Show quarter requests management page (Building Unit)
     */
    public function requests()
    {
        return view('dashboards.building.requests');
    }

    /**
     * Show officer's own quarter requests
     */
    public function myRequests()
    {
        return view('officer.quarter-requests.index');
    }

    /**
     * Show create quarter request form (Officer)
     */
    public function createRequest()
    {
        return view('officer.quarter-requests.create');
    }

    /**
     * Accept quarter allocation (Officer) - Web route
     */
    public function acceptAllocation(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'User must be linked to an officer');
        }

        $allocation = OfficerQuarter::with(['quarter', 'officer'])->findOrFail($id);

        // Ensure allocation belongs to the officer
        if ($allocation->officer_id != $officer->id) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'You can only accept your own allocations');
        }

        // Ensure allocation is pending
        if (!$allocation->isPending()) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Only pending allocations can be accepted');
        }

        // Check if quarter is still available
        $quarterOccupied = OfficerQuarter::where('quarter_id', $allocation->quarter_id)
            ->where('id', '!=', $allocation->id)
            ->where('is_current', true)
            ->where('status', 'ACCEPTED')
            ->exists();

        if ($quarterOccupied) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'This quarter has already been accepted by another officer');
        }

        try {
            DB::transaction(function () use ($allocation, $officer) {
                // Update allocation status to ACCEPTED
                $allocation->update([
                    'status' => 'ACCEPTED',
                    'accepted_at' => now(),
                ]);

                // Mark quarter as occupied
                $allocation->quarter->update(['is_occupied' => true]);

                // Update officer's quartered status
                $officer->update(['quartered' => true]);

                // Reject any other pending allocations for this officer
                OfficerQuarter::where('officer_id', $officer->id)
                    ->where('id', '!=', $allocation->id)
                    ->where('status', 'PENDING')
                    ->update([
                        'status' => 'REJECTED',
                        'rejected_at' => now(),
                        'is_current' => false,
                    ]);
            });

            // Notify Building Unit about acceptance
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyQuarterAllocationAccepted($allocation);

            return redirect()->route('officer.dashboard')
                ->with('success', 'Quarter allocation accepted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Reject quarter allocation (Officer) - Web route
     */
    public function rejectAllocation(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'User must be linked to an officer');
        }

        $allocation = OfficerQuarter::with(['quarter', 'officer'])->findOrFail($id);

        // Ensure allocation belongs to the officer
        if ($allocation->officer_id != $officer->id) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'You can only reject your own allocations');
        }

        // Ensure allocation is pending
        if (!$allocation->isPending()) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Only pending allocations can be rejected');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $allocation->update([
                'status' => 'REJECTED',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'is_current' => false,
            ]);

            // Notify Building Unit about rejection
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyQuarterAllocationRejected($allocation, $request->rejection_reason);

            return redirect()->route('officer.dashboard')
                ->with('success', 'Quarter allocation rejected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show rejected allocations (Building Unit)
     */
    public function rejectedAllocations(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('Building Unit')) {
            return redirect()->route('building.dashboard')
                ->with('error', 'Unauthorized access');
        }

        // Get Building Unit command
        $buildingUnitRole = $user->roles()
            ->where('name', 'Building Unit')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $buildingUnitRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('building.dashboard')
                ->with('error', 'Building Unit user must be assigned to a command');
        }

        // Get rejected allocations that haven't been re-allocated
        // Exclude rejected allocations if there's a newer allocation (PENDING or ACCEPTED) for the same officer
        $query = \App\Models\OfficerQuarter::where('status', 'REJECTED')
            ->with([
                'officer:id,service_number,initials,surname,present_station',
                'quarter:id,quarter_number,quarter_type,command_id',
                'allocatedBy:id,email',
                'allocatedBy.officer:id,user_id,initials,surname',
            ])
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->whereHas('quarter', function ($q) use ($commandId) {
                $q->where('command_id', $commandId);
            })
            ->whereNotExists(function ($q) {
                // Exclude if there's a newer allocation (PENDING or ACCEPTED) for this officer
                $q->select(DB::raw(1))
                  ->from('officer_quarters as newer_allocations')
                  ->whereColumn('newer_allocations.officer_id', 'officer_quarters.officer_id')
                  ->whereIn('newer_allocations.status', ['PENDING', 'ACCEPTED'])
                  ->whereColumn('newer_allocations.created_at', '>', 'officer_quarters.created_at');
            });

        // Apply date filters if provided
        if ($request->has('from_date')) {
            $query->whereDate('rejected_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('rejected_at', '<=', $request->to_date);
        }

        $rejectedAllocations = $query->orderBy('rejected_at', 'desc')
            ->get()
            ->map(function ($allocation) {
                // Ensure allocatedBy relationship is loaded even if user doesn't have officer
                if ($allocation->allocatedBy && !$allocation->allocatedBy->relationLoaded('officer')) {
                    $allocation->allocatedBy->load('officer:id,user_id,initials,surname');
                }
                return $allocation;
            });

        return view('dashboards.building.rejected-allocations', compact('rejectedAllocations'));
    }
}



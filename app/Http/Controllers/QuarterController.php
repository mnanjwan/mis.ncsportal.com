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
        $user = auth()->user();
        
        // Get Building Unit's command
        $commandId = null;
        $commandName = null;
        
        if ($user->hasRole('Building Unit')) {
            $buildingUnitRole = $user->roles()
                ->where('name', 'Building Unit')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $buildingUnitRole?->pivot->command_id ?? null;
            
            if ($commandId) {
                $command = \App\Models\Command::find($commandId);
                $commandName = $command?->name ?? 'N/A';
            }
        }
        
        return view('forms.quarter.create', compact('commandId', 'commandName'));
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
        // Only show rejected allocations if there's no newer allocation (any status) for the same officer
        // This prevents showing old rejections when officer has been re-allocated
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
                // Exclude if there's ANY newer allocation (PENDING, ACCEPTED, or even REJECTED) for this officer
                // This ensures we only show the most recent rejected allocation if it's the latest action
                $q->select(DB::raw(1))
                  ->from('officer_quarters as newer_allocations')
                  ->whereColumn('newer_allocations.officer_id', 'officer_quarters.officer_id')
                  ->whereColumn('newer_allocations.created_at', '>', 'officer_quarters.created_at');
            });

        // Apply date filters if provided
        if ($request->has('from_date')) {
            $query->whereDate('rejected_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('rejected_at', '<=', $request->to_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'rejected_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Map sort column names - use subqueries to avoid join conflicts
        if ($sortBy === 'rejected_at') {
            $query->orderBy('officer_quarters.rejected_at', $sortOrder);
        } elseif ($sortBy === 'officer_name') {
            $query->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                  ->orderBy('officers.surname', $sortOrder)
                  ->orderBy('officers.initials', $sortOrder)
                  ->select('officer_quarters.*');
        } elseif ($sortBy === 'service_number') {
            $query->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                  ->orderBy('officers.service_number', $sortOrder)
                  ->select('officer_quarters.*');
        } elseif ($sortBy === 'quarter_number') {
            $query->join('quarters', 'officer_quarters.quarter_id', '=', 'quarters.id')
                  ->orderBy('quarters.quarter_number', $sortOrder)
                  ->select('officer_quarters.*');
        } elseif ($sortBy === 'quarter_type') {
            $query->join('quarters', 'officer_quarters.quarter_id', '=', 'quarters.id')
                  ->orderBy('quarters.quarter_type', $sortOrder)
                  ->select('officer_quarters.*');
        } else {
            $query->orderBy('officer_quarters.rejected_at', $sortOrder);
        }

        $rejectedAllocations = $query
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

    /**
     * Show pending allocations (Building Unit)
     */
    public function pendingAllocations(Request $request)
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

        // Get pending allocations for officers in Building Unit's command
        $query = \App\Models\OfficerQuarter::where('status', 'PENDING')
            ->with([
                'officer:id,service_number,initials,surname,present_station,substantive_rank',
                'quarter:id,quarter_number,quarter_type,command_id',
                'allocatedBy:id,email',
                'allocatedBy.officer:id,user_id,initials,surname',
            ])
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->whereHas('quarter', function ($q) use ($commandId) {
                $q->where('command_id', $commandId);
            });

        // Apply date filters if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Map sort column names
        if ($sortBy === 'created_at') {
            $query->orderBy('officer_quarters.created_at', $sortOrder);
        } elseif ($sortBy === 'officer_name') {
            $query->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                  ->orderBy('officers.surname', $sortOrder)
                  ->orderBy('officers.initials', $sortOrder)
                  ->select('officer_quarters.*');
        } elseif ($sortBy === 'service_number') {
            $query->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                  ->orderBy('officers.service_number', $sortOrder)
                  ->select('officer_quarters.*');
        } elseif ($sortBy === 'quarter_number') {
            $query->join('quarters', 'officer_quarters.quarter_id', '=', 'quarters.id')
                  ->orderBy('quarters.quarter_number', $sortOrder)
                  ->select('officer_quarters.*');
        } else {
            $query->orderBy('officer_quarters.created_at', $sortOrder);
        }

        $pendingAllocations = $query
            ->get()
            ->map(function ($allocation) {
                // Ensure allocatedBy relationship is loaded
                if ($allocation->allocatedBy && !$allocation->allocatedBy->relationLoaded('officer')) {
                    $allocation->allocatedBy->load('officer:id,user_id,initials,surname');
                }
                return $allocation;
            });

        return view('dashboards.building.pending-allocations', compact('pendingAllocations'));
    }

    /**
     * Show all quarter allocations with tabs (Building Unit)
     */
    public function allocations(Request $request)
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

        $activeTab = $request->get('tab', 'pending');

        // Base query for all allocations in Building Unit's command
        $baseQuery = \App\Models\OfficerQuarter::with([
                'officer:id,service_number,initials,surname,present_station,substantive_rank',
                'quarter:id,quarter_number,quarter_type,command_id',
                'allocatedBy:id,email',
                'allocatedBy.officer:id,user_id,initials,surname',
            ])
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->whereHas('quarter', function ($q) use ($commandId) {
                $q->where('command_id', $commandId);
            });

        // Get Pending Allocations
        $pendingQuery = (clone $baseQuery)->where('status', 'PENDING');
        if ($request->has('from_date') && $activeTab === 'pending') {
            $pendingQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $activeTab === 'pending') {
            $pendingQuery->whereDate('created_at', '<=', $request->to_date);
        }
        $pendingAllocations = $pendingQuery->orderBy('created_at', 'desc')->get();
        $pendingCount = $pendingAllocations->count();

        // Get Rejected Allocations (only most recent if re-allocated)
        $rejectedQuery = (clone $baseQuery)->where('status', 'REJECTED')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('officer_quarters as newer_allocations')
                  ->whereColumn('newer_allocations.officer_id', 'officer_quarters.officer_id')
                  ->whereColumn('newer_allocations.created_at', '>', 'officer_quarters.created_at');
            });
        if ($request->has('from_date') && $activeTab === 'rejected') {
            $rejectedQuery->whereDate('rejected_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $activeTab === 'rejected') {
            $rejectedQuery->whereDate('rejected_at', '<=', $request->to_date);
        }
        
        // Sorting for rejected
        if ($activeTab === 'rejected') {
            $sortBy = $request->get('sort_by', 'rejected_at');
            $sortOrder = $request->get('sort_order', 'desc');
            if ($sortBy === 'rejected_at') {
                $rejectedQuery->orderBy('officer_quarters.rejected_at', $sortOrder);
            } elseif ($sortBy === 'officer_name') {
                $rejectedQuery->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                      ->orderBy('officers.surname', $sortOrder)
                      ->orderBy('officers.initials', $sortOrder)
                      ->select('officer_quarters.*');
            } elseif ($sortBy === 'service_number') {
                $rejectedQuery->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                      ->orderBy('officers.service_number', $sortOrder)
                      ->select('officer_quarters.*');
            } elseif ($sortBy === 'quarter_number') {
                $rejectedQuery->join('quarters', 'officer_quarters.quarter_id', '=', 'quarters.id')
                      ->orderBy('quarters.quarter_number', $sortOrder)
                      ->select('officer_quarters.*');
            } else {
                $rejectedQuery->orderBy('officer_quarters.rejected_at', $sortOrder);
            }
        } else {
            $rejectedQuery->orderBy('officer_quarters.rejected_at', 'desc');
        }
        $rejectedAllocations = $rejectedQuery->get();
        $rejectedCount = $rejectedAllocations->count();

        // Get Successful (Accepted) Allocations
        $successfulQuery = (clone $baseQuery)->where('status', 'ACCEPTED')
            ->where('is_current', true);
        if ($request->has('from_date') && $activeTab === 'successful') {
            $successfulQuery->whereDate('accepted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $activeTab === 'successful') {
            $successfulQuery->whereDate('accepted_at', '<=', $request->to_date);
        }
        
        // Sorting for successful
        if ($activeTab === 'successful') {
            $sortBy = $request->get('sort_by', 'accepted_at');
            $sortOrder = $request->get('sort_order', 'desc');
            if ($sortBy === 'accepted_at') {
                $successfulQuery->orderBy('officer_quarters.accepted_at', $sortOrder);
            } elseif ($sortBy === 'officer_name') {
                $successfulQuery->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                      ->orderBy('officers.surname', $sortOrder)
                      ->orderBy('officers.initials', $sortOrder)
                      ->select('officer_quarters.*');
            } elseif ($sortBy === 'service_number') {
                $successfulQuery->join('officers', 'officer_quarters.officer_id', '=', 'officers.id')
                      ->orderBy('officers.service_number', $sortOrder)
                      ->select('officer_quarters.*');
            } elseif ($sortBy === 'quarter_number') {
                $successfulQuery->join('quarters', 'officer_quarters.quarter_id', '=', 'quarters.id')
                      ->orderBy('quarters.quarter_number', $sortOrder)
                      ->select('officer_quarters.*');
            } else {
                $successfulQuery->orderBy('officer_quarters.accepted_at', $sortOrder);
            }
        } else {
            $successfulQuery->orderBy('officer_quarters.accepted_at', 'desc');
        }
        $successfulAllocations = $successfulQuery->get();
        $successfulCount = $successfulAllocations->count();

        // Map allocations to ensure relationships are loaded
        $mapAllocations = function ($allocation) {
            if ($allocation->allocatedBy && !$allocation->allocatedBy->relationLoaded('officer')) {
                $allocation->allocatedBy->load('officer:id,user_id,initials,surname');
            }
            return $allocation;
        };

        $pendingAllocations = $pendingAllocations->map($mapAllocations);
        $rejectedAllocations = $rejectedAllocations->map($mapAllocations);
        $successfulAllocations = $successfulAllocations->map($mapAllocations);

        return view('dashboards.building.allocations', compact(
            'pendingAllocations',
            'rejectedAllocations',
            'successfulAllocations',
            'pendingCount',
            'rejectedCount',
            'successfulCount',
            'activeTab'
        ));
    }
}



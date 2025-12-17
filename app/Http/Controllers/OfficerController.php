<?php

namespace App\Http\Controllers;

use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use Illuminate\Http\Request;

class OfficerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // HRD Methods (for managing officers)
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = \App\Models\Officer::with(['presentStation.zone', 'currentPosting']);
        
        // If Staff Officer, filter by their command
        if ($user->hasRole('Staff Officer')) {
            $staffOfficerRole = $user->roles()
                ->where('name', 'Staff Officer')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $staffOfficerRole?->pivot->command_id ?? null;
            if ($commandId) {
                $query->where('present_station', $commandId);
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                  ->orWhere('initials', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Rank filter
        if ($request->filled('rank')) {
            $query->where('substantive_rank', $request->rank);
        }

        // Command filter (for HRD)
        if ($request->filled('command_id') && !$user->hasRole('Staff Officer')) {
            $query->where('present_station', $request->command_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Map sort_by to actual column names
        $sortableColumns = [
            'service_number' => 'service_number',
            'name' => 'surname', // Sort by surname for name
            'rank' => 'substantive_rank',
            'command' => 'present_station',
            'zone' => 'present_station', // Sort by command, then we'll need to join for zone name
            'status' => 'is_active',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        // Handle zone sorting - need to join with commands and zones
        if ($sortBy === 'zone') {
            $query->leftJoin('commands', 'officers.present_station', '=', 'commands.id')
                  ->leftJoin('zones', 'commands.zone_id', '=', 'zones.id')
                  ->select('officers.*')
                  ->orderBy('zones.name', $order)
                  ->orderBy('commands.name', $order); // Secondary sort by command name
        } elseif ($sortBy === 'command') {
            $query->leftJoin('commands', 'officers.present_station', '=', 'commands.id')
                  ->select('officers.*')
                  ->orderBy('commands.name', $order);
        } else {
            $query->orderBy($column, $order);
        }

        // Get unique ranks for filter dropdown
        $ranks = \App\Models\Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->orderBy('substantive_rank')
            ->pluck('substantive_rank')
            ->filter()
            ->values();

        // Get all commands for filter dropdown
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        $officers = $query->paginate(20)->withQueryString();

        // Return appropriate view based on role
        if ($user->hasRole('Staff Officer')) {
            return view('dashboards.staff-officer.officers-list', compact('officers', 'ranks', 'commands'));
        }

        return view('dashboards.hrd.officers-list', compact('officers', 'ranks', 'commands'));
    }
    
    // Document an officer (Staff Officer only)
    public function document($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('Staff Officer')) {
            abort(403, 'Unauthorized');
        }
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        $officer = \App\Models\Officer::findOrFail($id);
        
        // Verify officer is in Staff Officer's command
        if ($officer->present_station != $commandId) {
            return redirect()->back()->with('error', 'You can only document officers in your command.');
        }
        
        // Get current posting
        $posting = \App\Models\OfficerPosting::where('officer_id', $officer->id)
            ->where('is_current', true)
            ->whereNull('documented_at')
            ->first();
        
        if (!$posting) {
            return redirect()->back()->with('error', 'Officer is already documented or has no pending posting.');
        }
        
        // Document the officer
        $posting->update([
            'documented_at' => now(),
            'documented_by' => $user->id,
        ]);
        
        return redirect()->back()->with('success', "Officer {$officer->service_number} has been documented successfully.");
    }

    // Zone Coordinator - View officers in their zone
    public function zoneOfficers(Request $request)
    {
        $user = auth()->user();
        
        // Get the zone coordinator's zone from their command assignment
        $zoneCoordinatorRole = $user->roles()
            ->where('name', 'Zone Coordinator')
            ->wherePivot('is_active', true)
            ->first();
        
        if (!$zoneCoordinatorRole || !$zoneCoordinatorRole->pivot->command_id) {
            abort(403, 'You are not assigned to a zone. Please contact HRD.');
        }
        
        $coordinatorCommand = \App\Models\Command::find($zoneCoordinatorRole->pivot->command_id);
        $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
        
        if (!$coordinatorZone) {
            abort(403, 'Your assigned command does not have a zone. Please contact HRD.');
        }
        
        // Get all commands in the zone
        $zoneCommandIds = \App\Models\Command::where('zone_id', $coordinatorZone->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        
        // Query officers in the zone
        $query = \App\Models\Officer::whereIn('present_station', $zoneCommandIds)
            ->where('is_active', true)
            ->with(['presentStation.zone']);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                  ->orWhere('initials', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Rank filter
        if ($request->filled('rank')) {
            $query->where('substantive_rank', $request->rank);
        }
        
        // Command filter (within zone only)
        if ($request->filled('command_id')) {
            $commandId = $request->command_id;
            if (in_array($commandId, $zoneCommandIds)) {
                $query->where('present_station', $commandId);
            }
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'surname');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $sortableColumns = [
            'service_number' => 'service_number',
            'name' => 'surname',
            'rank' => 'substantive_rank',
            'command' => 'present_station',
            'status' => 'is_active',
        ];
        
        $column = $sortableColumns[$sortBy] ?? 'surname';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';
        
        if ($sortBy === 'command') {
            $query->leftJoin('commands', 'officers.present_station', '=', 'commands.id')
                  ->select('officers.*')
                  ->orderBy('commands.name', $order);
        } else {
            $query->orderBy($column, $order);
        }
        
        // If sorting by name, add initials as secondary sort
        if ($sortBy === 'name' || !$request->has('sort_by')) {
            $query->orderBy('initials', $order);
        }
        
        // Get unique ranks for filter dropdown (from zone officers only)
        $ranks = \App\Models\Officer::whereIn('present_station', $zoneCommandIds)
            ->whereNotNull('substantive_rank')
            ->distinct()
            ->orderBy('substantive_rank')
            ->pluck('substantive_rank')
            ->filter()
            ->values();
        
        // Get commands in the zone for filter dropdown
        $commands = \App\Models\Command::whereIn('id', $zoneCommandIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $officers = $query->select('officers.*')->paginate(20)->withQueryString();
        
        return view('dashboards.zone-coordinator.officers', compact('officers', 'ranks', 'commands', 'coordinatorZone'));
    }

    public function show($id)
    {
        $officer = \App\Models\Officer::with(['presentStation', 'user', 'nextOfKin', 'documents'])
            ->findOrFail($id);
        
        return view('dashboards.hrd.officer-show', compact('officer'));
    }

    public function edit($id)
    {
        return view('forms.officer.edit', compact('id'));
    }

    // Officer Dashboard Methods
    public function dashboard()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            // Handle case where officer profile doesn't exist yet
            return view('dashboards.officer.dashboard', [
                'officer' => null,
                'emolumentStatus' => 'Not Raised',
                'leaveBalance' => 0,
                'passStatus' => 'Unavailable',
                'recentApplications' => [],
                'activeTimeline' => null,
            ]);
        }

        // 1. Active Timeline
        $activeTimeline = EmolumentTimeline::where('is_active', true)->first();

        // 2. Emolument Status
        $emolumentStatus = 'Not Raised';
        if ($activeTimeline) {
            $emolument = Emolument::where('officer_id', $officer->id)
                ->where('timeline_id', $activeTimeline->id)
                ->first();

            if ($emolument) {
                $emolumentStatus = ucfirst(strtolower($emolument->status));
            }
        }

        // 3. Leave Balance (Mock logic for now, or fetch from DB if table exists)
        // Assuming 30 days annual leave for now
        $usedLeave = LeaveApplication::where('officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->whereYear('start_date', now()->year)
            ->sum('duration');
        $leaveBalance = 30 - $usedLeave;

        // 4. Pass Eligibility
        // Simple check: Eligible if no active pass
        $activePass = PassApplication::where('officer_id', $officer->id)
            ->where('status', 'APPROVED')
            ->where('end_date', '>=', now())
            ->exists();
        $passStatus = $activePass ? 'On Pass' : 'Available';

        // 5. Recent Applications
        $recentLeaves = LeaveApplication::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                $item->type = 'Leave Application';
                return $item;
            });

        $recentPasses = PassApplication::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($item) {
                $item->type = 'Pass Application';
                return $item;
            });

        $recentApplications = $recentLeaves->concat($recentPasses)
            ->sortByDesc('created_at')
            ->take(5);

        return view('dashboards.officer.dashboard', compact(
            'officer',
            'emolumentStatus',
            'leaveBalance',
            'passStatus',
            'recentApplications',
            'activeTimeline'
        ));
    }

    public function profile()
    {
        return view('dashboards.officer.profile');
    }

    public function emoluments(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            $emoluments = collect([])->paginate(20);
        } else {
            $query = Emolument::where('officer_id', $officer->id)
                ->with(['officer', 'timeline'])
                ->orderBy('created_at', 'desc');

            $emoluments = $query->paginate(20);
        }

        return view('dashboards.officer.emoluments-list', compact('emoluments'));
    }

    public function leaveApplications(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            $leaves = collect([])->paginate(20);
        } else {
            $query = LeaveApplication::where('officer_id', $officer->id)
                ->orderBy('created_at', 'desc');

            $leaves = $query->paginate(20);
        }

        return view('dashboards.officer.leave-applications-list', compact('leaves'));
    }

    public function passApplications(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            $passes = collect([])->paginate(20);
        } else {
            $query = PassApplication::where('officer_id', $officer->id)
                ->orderBy('created_at', 'desc');

            $passes = $query->paginate(20);
        }

        return view('dashboards.officer.pass-applications-list', compact('passes'));
    }

    public function applicationHistory()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        // Get leave applications
        $leaveQuery = LeaveApplication::with('leaveType')
            ->where('officer_id', $officer->id);

        // Get pass applications
        $passQuery = PassApplication::where('officer_id', $officer->id);

        // Filter by type
        $type = request('type');
        if ($type === 'leave') {
            $passQuery->whereRaw('1 = 0'); // Exclude passes
        } elseif ($type === 'pass') {
            $leaveQuery->whereRaw('1 = 0'); // Exclude leaves
        }

        // Filter by status
        if ($status = request('status')) {
            $leaveQuery->where('status', $status);
            $passQuery->where('status', $status);
        }

        // Filter by year
        if ($year = request('year')) {
            $leaveQuery->whereYear('start_date', $year);
            $passQuery->whereYear('start_date', $year);
        }

        // Get all applications
        $leaves = $leaveQuery->get()->map(function ($item) {
            $item->application_type = 'Leave';
            $item->application_id = $item->id;
            $item->type_name = $item->leaveType->name ?? 'N/A';
            $item->submitted_date = $item->submitted_at ?? $item->created_at;
            return $item;
        });

        $passes = $passQuery->get()->map(function ($item) {
            $item->application_type = 'Pass';
            $item->application_id = $item->id;
            $item->type_name = 'Pass';
            $item->submitted_date = $item->submitted_at ?? $item->created_at;
            return $item;
        });

        // Combine and sort
        $applications = $leaves->concat($passes)
            ->sortByDesc(function ($item) {
                return $item->submitted_date ?? $item->created_at;
            });

        // Get unique years for filter (database-agnostic approach)
        $leaveYears = LeaveApplication::where('officer_id', $officer->id)
            ->get()
            ->pluck('start_date')
            ->map(function ($date) {
                return $date ? $date->format('Y') : null;
            })
            ->filter()
            ->unique()
            ->map(function ($year) {
                return (int) $year;
            })
            ->sort()
            ->reverse()
            ->values();

        $passYears = PassApplication::where('officer_id', $officer->id)
            ->get()
            ->pluck('start_date')
            ->map(function ($date) {
                return $date ? $date->format('Y') : null;
            })
            ->filter()
            ->unique()
            ->map(function ($year) {
                return (int) $year;
            })
            ->sort()
            ->reverse()
            ->values();

        $years = $leaveYears->concat($passYears)->unique()->sort()->reverse()->values();

        // Paginate manually
        $perPage = 20;
        $currentPage = request('page', 1);
        $items = $applications->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $applications->count();
        $applications = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('dashboards.officer.application-history', compact('applications', 'years'));
    }
}



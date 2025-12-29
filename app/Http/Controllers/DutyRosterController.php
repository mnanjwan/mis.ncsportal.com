<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class DutyRosterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow Staff Officer, Area Controller, and DC Admin access
        $this->middleware('role:Staff Officer|Area Controller|DC Admin');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? \App\Models\Command::find($commandId) : null;
        
        // Get month from request or use current month
        $month = $request->get('month', date('Y-m'));
        
        // Get duty rosters for this command and month
        $rosters = \App\Models\DutyRoster::where('command_id', $commandId)
            ->whereYear('roster_period_start', date('Y', strtotime($month . '-01')))
            ->whereMonth('roster_period_start', date('m', strtotime($month . '-01')))
            ->with(['assignments.officer'])
            ->orderBy('roster_period_start', 'asc')
            ->get();
        
        return view('dashboards.staff-officer.roster', compact('rosters', 'command', 'month'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? \App\Models\Command::find($commandId) : null;
        
        return view('forms.roster.create', compact('command'));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->back()->with('error', 'You are not assigned to a command. Please contact HRD.')->withInput();
        }
        
        $request->validate([
            'roster_period_start' => 'required|date',
            'roster_period_end' => 'required|date|after:roster_period_start',
            'command_id' => 'required|exists:commands,id',
        ]);
        
        // Verify command matches Staff Officer's command
        if ($request->command_id != $commandId) {
            return redirect()->back()->with('error', 'You can only create rosters for your assigned command.')->withInput();
        }
        
        try {
            \App\Models\DutyRoster::create([
                'command_id' => $commandId,
                'roster_period_start' => $request->roster_period_start,
                'roster_period_end' => $request->roster_period_end,
                'prepared_by' => $user->id,
                'status' => 'DRAFT',
            ]);
            
            return redirect()->route('staff-officer.roster')
                ->with('success', 'Duty roster created successfully! You can now edit it to add officer assignments.');
                
        } catch (\Exception $e) {
            Log::error('Failed to create duty roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create duty roster: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'assignments.officer', 'preparedBy', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);
        
        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only view rosters for your assigned command');
        }
        
        return view('dashboards.staff-officer.roster-show', compact('roster'));
    }

    public function edit($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'assignments.officer'])->findOrFail($id);
        
        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only edit rosters for your assigned command');
        }
        
        // Get officers in the command
        $officers = \App\Models\Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->get();
        
        return view('forms.roster.edit', compact('roster', 'officers'));
    }
    
    public function update(Request $request, $id)
    {
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only edit rosters for your assigned command');
        }
        
        // Only allow editing DRAFT rosters
        if ($roster->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT rosters can be edited.');
        }
        
        $request->validate([
            'oic_officer_id' => 'nullable|exists:officers,id',
            'second_in_command_officer_id' => 'nullable|exists:officers,id',
            'assignments' => 'nullable|array',
            'assignments.*.officer_id' => 'required|exists:officers,id',
            'assignments.*.duty_date' => 'required|date',
            'assignments.*.shift' => 'nullable|string|max:50',
            'assignments.*.notes' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update OIC and 2IC
            $roster->update([
                'oic_officer_id' => $request->oic_officer_id,
                'second_in_command_officer_id' => $request->second_in_command_officer_id,
            ]);
            
            // Get assigned officer IDs before deleting assignments
            $assignedOfficerIds = [];
            if ($request->has('assignments')) {
                foreach ($request->assignments as $assignment) {
                    if (!in_array($assignment['officer_id'], $assignedOfficerIds)) {
                        $assignedOfficerIds[] = $assignment['officer_id'];
                    }
                }
            }
            
            // Delete existing assignments if provided
            if ($request->has('assignments')) {
                $roster->assignments()->delete();
                
                // Create new assignments
                foreach ($request->assignments as $assignment) {
                    \App\Models\RosterAssignment::create([
                        'roster_id' => $roster->id,
                        'officer_id' => $assignment['officer_id'],
                        'duty_date' => $assignment['duty_date'],
                        'shift' => $assignment['shift'] ?? null,
                        'notes' => $assignment['notes'] ?? null,
                    ]);
                }
            }
            
            // Refresh roster to get updated relationships
            $roster->refresh();
            
            // Notify all assigned officers
            if (!empty($assignedOfficerIds)) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyDutyRosterAssigned($roster, $assignedOfficerIds);
            }
            
            DB::commit();
            
            return redirect()->route('staff-officer.roster.show', $roster->id)
                ->with('success', 'Roster updated successfully! All assigned officers have been notified.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update roster: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function submit($id)
    {
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only submit rosters for your assigned command');
        }
        
        // Only allow submitting DRAFT rosters
        if ($roster->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT rosters can be submitted.');
        }
        
        // Check if roster has assignments
        if ($roster->assignments->count() === 0) {
            return redirect()->back()->with('error', 'Please add at least one officer assignment before submitting.');
        }
        
        try {
            $roster->update([
                'status' => 'SUBMITTED',
            ]);
            
            // Refresh roster to get relationships
            $roster->refresh();
            
            // Notify DC Admins and Area Controllers
            $notificationService = app(NotificationService::class);
            $notificationService->notifyDutyRosterSubmitted($roster);
            $notificationService->notifyDutyRosterSubmittedToAreaController($roster);
            
            return redirect()->route('staff-officer.roster.show', $roster->id)
                ->with('success', 'Roster submitted successfully! It is now pending DC Admin and Area Controller approval.');
                
        } catch (\Exception $e) {
            Log::error('Failed to submit roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit roster: ' . $e->getMessage());
        }
    }

    // Area Controller Methods
    public function areaControllerIndex(Request $request)
    {
        // Get submitted rosters (status = SUBMITTED)
        $query = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments'])
            ->where('status', 'SUBMITTED')
            ->orderBy('created_at', 'desc');
        
        $rosters = $query->paginate(20)->withQueryString();
        
        return view('dashboards.area-controller.roster', compact('rosters'));
    }
    
    public function areaControllerShow($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);
        
        // Only show SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            abort(403, 'This roster is not pending approval');
        }
        
        return view('dashboards.area-controller.roster-show', compact('roster'));
    }
    
    public function areaControllerApprove(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can approve rosters');
        }
        
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        // Only allow approving SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be approved.');
        }
        
        try {
            $roster->status = 'APPROVED';
            $roster->approved_at = now();
            $roster->save();
            
            return redirect()->route('area-controller.roster')
                ->with('success', 'Roster approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve roster: ' . $e->getMessage());
        }
    }
    
    public function areaControllerReject(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can reject rosters');
        }
        
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        // Only allow rejecting SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        try {
            $roster->status = 'REJECTED';
            $roster->rejection_reason = $request->rejection_reason;
            $roster->save();
            
            return redirect()->route('area-controller.roster')
                ->with('success', 'Roster rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject roster: ' . $e->getMessage());
        }
    }

    // DC Admin Methods
    public function dcAdminIndex(Request $request)
    {
        $user = auth()->user();
        
        // Get DC Admin's command
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $dcAdminRole?->pivot->command_id ?? null;
        
        // Get submitted rosters for DC Admin's command
        $query = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments'])
            ->where('status', 'SUBMITTED')
            ->orderBy('created_at', 'desc');
        
        // Filter by command if DC Admin is assigned to a command
        if ($commandId) {
            $query->where('command_id', $commandId);
        }
        
        $rosters = $query->paginate(20)->withQueryString();
        
        return view('dashboards.dc-admin.roster', compact('rosters'));
    }
    
    public function dcAdminShow($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);
        
        // Only show SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            abort(403, 'This roster is not pending approval');
        }
        
        return view('dashboards.dc-admin.roster-show', compact('roster'));
    }
    
    public function dcAdminApprove(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can approve rosters');
        }
        
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        // Only allow approving SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be approved.');
        }
        
        try {
            $roster->status = 'APPROVED';
            $roster->approved_at = now();
            $roster->save();
            
            return redirect()->route('dc-admin.roster')
                ->with('success', 'Roster approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve roster: ' . $e->getMessage());
        }
    }
    
    public function dcAdminReject(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can reject rosters');
        }
        
        $roster = \App\Models\DutyRoster::findOrFail($id);
        
        // Only allow rejecting SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        try {
            $roster->status = 'REJECTED';
            $roster->rejection_reason = $request->rejection_reason;
            $roster->save();
            
            return redirect()->route('dc-admin.roster')
                ->with('success', 'Roster rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject roster: ' . $e->getMessage());
        }
    }
}



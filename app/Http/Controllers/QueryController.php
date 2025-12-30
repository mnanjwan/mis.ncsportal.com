<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Query;
use App\Models\Officer;
use App\Services\NotificationService;

class QueryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Staff Officer');
    }

    /**
     * Display list of queries for Staff Officer's command
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'Command not found for Staff Officer role.');
        }

        // Get officers in this command
        $officerIds = Officer::where('present_station', $commandId)
            ->pluck('id')
            ->toArray();

        // Get queries for officers in this command
        $query = Query::whereIn('officer_id', $officerIds)
            ->with(['officer', 'issuedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $queries = $query->paginate(20)->withQueryString();

        return view('dashboards.staff-officer.queries.index', compact('queries'));
    }

    /**
     * Show form to issue a new query
     */
    public function create()
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'Command not found for Staff Officer role.');
        }

        // Get officers in this command
        $officers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        return view('dashboards.staff-officer.queries.create', compact('officers'));
    }

    /**
     * Store a new query
     */
    public function store(Request $request)
    {
        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'reason' => 'required|string|min:10',
            'response_deadline' => 'required|date|after:now',
        ]);

        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->back()->with('error', 'Command not found for Staff Officer role.')->withInput();
        }

        // Verify officer belongs to Staff Officer's command
        $officer = Officer::where('id', $request->officer_id)
            ->where('present_station', $commandId)
            ->first();

        if (!$officer) {
            return redirect()->back()->with('error', 'Officer not found in your command.')->withInput();
        }

        try {
            DB::beginTransaction();

            $query = Query::create([
                'officer_id' => $request->officer_id,
                'issued_by_user_id' => $user->id,
                'reason' => $request->reason,
                'status' => 'PENDING_RESPONSE',
                'issued_at' => now(),
                'response_deadline' => $request->response_deadline,
            ]);

            // Send notification to officer
            if ($officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyQueryIssued($query, $officer);
            }

            DB::commit();

            Log::info('Query issued', [
                'query_id' => $query->id,
                'officer_id' => $officer->id,
                'issued_by' => $user->id,
            ]);

            return redirect()->route('staff-officer.queries.index')
                ->with('success', 'Query issued successfully. Officer has been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to issue query', [
                'error' => $e->getMessage(),
                'officer_id' => $request->officer_id,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to issue query. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show query details
     */
    public function show($id)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'Command not found for Staff Officer role.');
        }

        $query = Query::with(['officer', 'issuedBy'])
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->findOrFail($id);

        return view('dashboards.staff-officer.queries.show', compact('query'));
    }

    /**
     * Accept query (adds to officer's disciplinary record)
     */
    public function accept(Request $request, $id)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->back()->with('error', 'Command not found for Staff Officer role.');
        }

        $query = Query::with(['officer', 'issuedBy'])
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->findOrFail($id);

        // Verify query was issued by current user
        if ($query->issued_by_user_id !== $user->id) {
            return redirect()->back()->with('error', 'You can only accept queries that you issued.');
        }

        // Verify query is pending review
        if (!$query->isPendingReview()) {
            return redirect()->back()->with('error', 'Query is not pending review.');
        }

        try {
            DB::beginTransaction();

            $query->update([
                'status' => 'ACCEPTED',
                'reviewed_at' => now(),
            ]);

            // Send notification to officer
            if ($query->officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyQueryAccepted($query);
            }

            DB::commit();

            Log::info('Query accepted', [
                'query_id' => $query->id,
                'officer_id' => $query->officer_id,
            ]);

            return redirect()->route('staff-officer.queries.show', $query->id)
                ->with('success', 'Query accepted. It has been added to the officer\'s disciplinary record.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept query', [
                'error' => $e->getMessage(),
                'query_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Failed to accept query. Please try again.');
        }
    }

    /**
     * Reject query (does not add to disciplinary record)
     */
    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->back()->with('error', 'Command not found for Staff Officer role.');
        }

        $query = Query::with(['officer', 'issuedBy'])
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->findOrFail($id);

        // Verify query was issued by current user
        if ($query->issued_by_user_id !== $user->id) {
            return redirect()->back()->with('error', 'You can only reject queries that you issued.');
        }

        // Verify query is pending review
        if (!$query->isPendingReview()) {
            return redirect()->back()->with('error', 'Query is not pending review.');
        }

        try {
            DB::beginTransaction();

            $query->update([
                'status' => 'REJECTED',
                'reviewed_at' => now(),
            ]);

            // Send notification to officer
            if ($query->officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyQueryRejected($query);
            }

            DB::commit();

            Log::info('Query rejected', [
                'query_id' => $query->id,
                'officer_id' => $query->officer_id,
            ]);

            return redirect()->route('staff-officer.queries.show', $query->id)
                ->with('success', 'Query rejected. It will not be added to the officer\'s disciplinary record.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject query', [
                'error' => $e->getMessage(),
                'query_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Failed to reject query. Please try again.');
        }
    }
}

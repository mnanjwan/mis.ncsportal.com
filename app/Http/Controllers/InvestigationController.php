<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Investigation;
use App\Models\Officer;
use App\Services\NotificationService;

class InvestigationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->middleware('role:Investigation Unit');
        $this->notificationService = $notificationService;
    }

    /**
     * Display investigation dashboard
     */
    public function index(Request $request)
    {
        // Get statistics for dashboard
        $totalInvestigations = Investigation::count();
        $invitedCount = Investigation::where('status', 'INVITED')->count();
        $ongoingCount = Investigation::where('status', 'ONGOING_INVESTIGATION')->count();
        $interdictedCount = Investigation::where('status', 'INTERDICTED')->count();
        $suspendedCount = Investigation::where('status', 'SUSPENDED')->count();
        $dismissedCount = Investigation::where('status', 'DISMISSED')->count();
        $resolvedCount = Investigation::where('status', 'RESOLVED')->count();

        // Get recent investigations
        $recentInvestigations = Investigation::with(['officer', 'investigationOfficer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // If this is a filtered request (search or status), show list view
        if ($request->filled('status') || $request->filled('search')) {
            $query = Investigation::with(['officer', 'investigationOfficer'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by officer name or service number
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('officer', function($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                      ->orWhere('initials', 'like', "%{$search}%")
                      ->orWhere('surname', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $investigations = $query->paginate(20)->withQueryString();

            return view('dashboards.investigation.index', compact('investigations'));
        }

        // Otherwise show dashboard
        return view('dashboards.investigation.dashboard', compact(
            'totalInvestigations',
            'invitedCount',
            'ongoingCount',
            'interdictedCount',
            'suspendedCount',
            'dismissedCount',
            'resolvedCount',
            'recentInvestigations'
        ));
    }

    /**
     * Search officers system-wide
     */
    public function search(Request $request)
    {
        $query = Officer::where('is_active', true);

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

        // Zone filter
        if ($request->filled('zone_id')) {
            $query->whereHas('presentStation', function($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        // Command filter
        if ($request->filled('command_id')) {
            $query->where('present_station', $request->command_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'surname');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Map sort_by to actual column names
        $sortableColumns = [
            'service_number' => 'service_number',
            'name' => 'surname', // Sort by surname for name
            'rank' => 'substantive_rank',
            'command' => 'present_station',
            'zone' => 'present_station', // Sort by command, then we'll need to join for zone name
        ];

        $column = $sortableColumns[$sortBy] ?? 'surname';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

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
            // Add secondary sort for name
            if ($sortBy === 'name') {
                $query->orderBy('initials', $order);
            }
        }

        // Get all zones for filter dropdown
        $zones = \App\Models\Zone::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all commands for filter dropdown
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        $officers = $query->paginate(20)->withQueryString();

        return view('dashboards.investigation.search', compact('officers', 'zones', 'commands'));
    }

    /**
     * Show form to send investigation invitation
     */
    public function create($officerId)
    {
        $officer = Officer::findOrFail($officerId);
        
        return view('dashboards.investigation.create', compact('officer'));
    }

    /**
     * Send investigation invitation
     */
    public function store(Request $request)
    {
        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'invitation_message' => 'required|string|min:10',
        ]);

        $officer = Officer::findOrFail($request->officer_id);
        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Create investigation record
            $investigation = Investigation::create([
                'officer_id' => $officer->id,
                'investigation_officer_id' => $user->id,
                'invitation_message' => $request->invitation_message,
                'status' => 'INVITED',
                'invited_at' => now(),
            ]);

            // Send in-app notification to officer
            if ($officer->user) {
                $this->notificationService->notify(
                    $officer->user,
                    'investigation_invited',
                    'Investigation Invitation',
                    "You have been invited to an investigation hearing. Message: {$request->invitation_message}",
                    'investigation',
                    $investigation->id,
                    false // Don't send email via notify method, we'll send via job
                );
            }

            // Send email notification via job
            if ($officer->user && $officer->user->email) {
                try {
                    \App\Jobs\SendInvestigationInvitationMailJob::dispatch($investigation);
                    \Log::info('Investigation invitation email job dispatched', [
                        'investigation_id' => $investigation->id,
                        'officer_id' => $officer->id,
                        'email' => $officer->user->email,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to dispatch investigation invitation email job', [
                        'investigation_id' => $investigation->id,
                        'officer_id' => $officer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('investigation.index')
                ->with('success', 'Investigation invitation sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to send investigation invitation', [
                'error' => $e->getMessage(),
                'officer_id' => $officer->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to send investigation invitation. Please try again.');
        }
    }

    /**
     * Show investigation details
     */
    public function show($id)
    {
        $investigation = Investigation::with(['officer', 'investigationOfficer'])
            ->findOrFail($id);

        return view('dashboards.investigation.show', compact('investigation'));
    }

    /**
     * Show form to update investigation status
     */
    public function edit($id)
    {
        $investigation = Investigation::with(['officer'])
            ->findOrFail($id);

        return view('dashboards.investigation.edit', compact('investigation'));
    }

    /**
     * Update investigation status
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:ONGOING_INVESTIGATION,INTERDICTED,SUSPENDED,DISMISSED,RESOLVED',
            'notes' => 'nullable|string',
        ]);

        $investigation = Investigation::with(['officer'])->findOrFail($id);
        $officer = $investigation->officer;
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $oldStatus = $investigation->status;
            $newStatus = $request->status;

            // Update investigation record
            $updateData = [
                'status' => $newStatus,
                'notes' => $request->notes,
                'status_changed_at' => now(),
            ];
            
            // If resolving, also set resolved_at
            if ($newStatus === 'RESOLVED') {
                $updateData['resolved_at'] = now();
                $updateData['resolution_notes'] = $request->notes; // Use notes as resolution notes
            }
            
            $investigation->update($updateData);

            // Update officer record based on status
            $officerUpdates = [];
            
            if ($newStatus === 'ONGOING_INVESTIGATION') {
                $officerUpdates['ongoing_investigation'] = true;
            } elseif ($newStatus === 'INTERDICTED') {
                $officerUpdates['interdicted'] = true;
                $officerUpdates['ongoing_investigation'] = false; // Clear ongoing investigation if interdicted
            } elseif ($newStatus === 'SUSPENDED') {
                $officerUpdates['suspended'] = true;
                $officerUpdates['ongoing_investigation'] = false; // Clear ongoing investigation if suspended
            } elseif ($newStatus === 'DISMISSED') {
                $officerUpdates['dismissed'] = true;
                $officerUpdates['ongoing_investigation'] = false; // Clear ongoing investigation if dismissed
                $officerUpdates['interdicted'] = false; // Clear interdiction if dismissed
                $officerUpdates['suspended'] = false; // Clear suspension if dismissed
            } elseif ($newStatus === 'RESOLVED') {
                // Clear all investigation-related flags when resolved
                $officerUpdates['ongoing_investigation'] = false;
                $officerUpdates['interdicted'] = false;
                $officerUpdates['suspended'] = false;
                // Note: dismissed flag is NOT cleared on resolve - dismissal is permanent
            }

            // If changing from interdicted/suspended to ongoing investigation, clear those flags
            if ($oldStatus === 'INTERDICTED' && $newStatus === 'ONGOING_INVESTIGATION') {
                $officerUpdates['interdicted'] = false;
            } elseif ($oldStatus === 'SUSPENDED' && $newStatus === 'ONGOING_INVESTIGATION') {
                $officerUpdates['suspended'] = false;
            }

            if (!empty($officerUpdates)) {
                $officer->update($officerUpdates);
            }

            // Send notification to officer
            $statusMessages = [
                'ONGOING_INVESTIGATION' => 'You have been placed on ongoing investigation.',
                'INTERDICTED' => 'You have been interdicted.',
                'SUSPENDED' => 'You have been suspended.',
                'DISMISSED' => 'You have been dismissed from service as a result of this investigation.',
                'RESOLVED' => 'Your investigation has been resolved. You are now eligible for promotion again (if other criteria are met).',
            ];

            if ($officer->user) {
                // Create in-app notification
                $notification = $this->notificationService->notify(
                    $officer->user,
                    'investigation_status_changed',
                    'Investigation Status Updated',
                    $statusMessages[$newStatus] . ($request->notes ? " Notes: {$request->notes}" : ''),
                    'investigation',
                    $investigation->id,
                    false // Don't send email via notify method, we'll send via job
                );

                // Send email notification via job
                if ($officer->user->email) {
                    try {
                        \App\Jobs\SendInvestigationStatusUpdateMailJob::dispatch($notification);
                        \Log::info('Investigation status update email job dispatched', [
                            'investigation_id' => $investigation->id,
                            'notification_id' => $notification->id,
                            'officer_id' => $officer->id,
                            'email' => $officer->user->email,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to dispatch investigation status update email job', [
                            'investigation_id' => $investigation->id,
                            'notification_id' => $notification->id,
                            'officer_id' => $officer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('investigation.show', $investigation->id)
                ->with('success', 'Investigation status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update investigation status', [
                'error' => $e->getMessage(),
                'investigation_id' => $id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update investigation status. Please try again.');
        }
    }

    /**
     * Resolve investigation
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'nullable|string',
        ]);

        $investigation = Investigation::with(['officer'])->findOrFail($id);
        $officer = $investigation->officer;

        DB::beginTransaction();
        try {
            // Update investigation record
            $investigation->update([
                'status' => 'RESOLVED',
                'resolved_at' => now(),
                'resolution_notes' => $request->resolution_notes,
            ]);

            // Clear all investigation-related flags from officer
            $officer->update([
                'ongoing_investigation' => false,
                'interdicted' => false,
                'suspended' => false,
            ]);

            // Send notification to officer
            if ($officer->user) {
                // Create in-app notification
                $notification = $this->notificationService->notify(
                    $officer->user,
                    'investigation_resolved',
                    'Investigation Resolved',
                    'Your investigation has been resolved. You are now eligible for promotion again (if other criteria are met).',
                    'investigation',
                    $investigation->id,
                    false // Don't send email via notify method, we'll send via job
                );

                // Send email notification via job
                if ($officer->user->email) {
                    try {
                        \App\Jobs\SendInvestigationResolvedMailJob::dispatch($notification);
                        \Log::info('Investigation resolved email job dispatched', [
                            'investigation_id' => $investigation->id,
                            'notification_id' => $notification->id,
                            'officer_id' => $officer->id,
                            'email' => $officer->user->email,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to dispatch investigation resolved email job', [
                            'investigation_id' => $investigation->id,
                            'notification_id' => $notification->id,
                            'officer_id' => $officer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('investigation.show', $investigation->id)
                ->with('success', 'Investigation resolved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to resolve investigation', [
                'error' => $e->getMessage(),
                'investigation_id' => $id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to resolve investigation. Please try again.');
        }
    }
}

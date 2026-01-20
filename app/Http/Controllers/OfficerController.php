<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\OfficerQuarter;
use App\Models\OfficerCourse;
use App\Models\Query;
use App\Models\APERForm;
use App\Models\Institution;
use App\Models\Discipline;
use App\Models\Qualification;
use App\Services\NotificationService;
use App\Services\EducationMasterDataSync;
use App\Services\QuarterAddressFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $query->where(function ($q) use ($search) {
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

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $rank = $request->get('rank', ''); // Optional rank filter
        
        // Allow search if we have at least a query (2+ chars) or a rank filter
        if (strlen($query) < 2 && empty($rank)) {
            return response()->json([]);
        }
        
        $officersQuery = \App\Models\Officer::where('is_active', true);
        
        // If rank is provided, filter by rank first
        if (!empty($rank)) {
            // Use the same rank matching logic as in ManningRequestController
            $rankMapping = [
                'CGC' => ['CGC', 'Comptroller-General'],
                'DCG' => ['DCG', 'Deputy Comptroller-General'],
                'AC' => ['AC', 'Assistant Comptroller'],
                'CSC' => ['CSC', 'Chief Superintendent'],
                'SC' => ['SC', 'Superintendent'],
                'DSC' => ['DSC', 'Deputy Superintendent'],
                'ASC' => ['ASC', 'Assistant Superintendent'],
            ];
            
            $rankUpper = strtoupper(trim($rank));
            $rankVariations = $rankMapping[$rankUpper] ?? [$rankUpper];
            
            $officersQuery->where(function($q) use ($rankVariations, $rankUpper) {
                foreach ($rankVariations as $variation) {
                    $q->orWhereRaw('LOWER(TRIM(substantive_rank)) = ?', [strtolower(trim($variation))]);
                }
                // Also check for partial matches (e.g., "Superintendent" matching "SC" if SC is in the rank)
                $q->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%' . strtolower($rankUpper) . '%']);
            });
        }
        
        // Apply search query if provided (even if just 1 character when rank is also provided)
        if (strlen($query) >= 1) {
            $officersQuery->where(function($q) use ($query) {
                $q->where('service_number', 'like', "%{$query}%")
                  ->orWhere('initials', 'like', "%{$query}%")
                  ->orWhere('surname', 'like', "%{$query}%")
                  ->orWhere('substantive_rank', 'like', "%{$query}%");
            });
        }
        
        // For Zone Coordinators: filter officers to only show those in their zone (GL 07 and below)
        $user = auth()->user();
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(\App\Services\ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Filter officers to only show those currently in the zone and GL 07 and below
                $officersQuery->whereIn('present_station', $zoneCommandIds);
                $officersQuery->where(function($q) {
                    $q->where('salary_grade_level', 'GL05')
                      ->orWhere('salary_grade_level', 'GL06')
                      ->orWhere('salary_grade_level', 'GL07')
                      ->orWhere('salary_grade_level', '05')
                      ->orWhere('salary_grade_level', '06')
                      ->orWhere('salary_grade_level', '07')
                      ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7")
                      ->orWhereRaw("CAST(salary_grade_level AS UNSIGNED) <= 7");
                });
            } else {
                // No zone commands - return empty results
                $officersQuery->whereRaw('1 = 0');
            }
        }
        
        // Exclude officers already in the draft deployment
        // Get all officer IDs currently in draft deployments
        $officersInDraft = \App\Models\ManningDeploymentAssignment::whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->pluck('officer_id')
            ->unique();
        
        if ($officersInDraft->isNotEmpty()) {
            $officersQuery->whereNotIn('id', $officersInDraft);
        }
        
        $officers = $officersQuery->with('presentStation')
            ->limit(50) // Increased limit since we're filtering by rank
            ->get()
            ->map(function($officer) {
                return [
                    'id' => $officer->id,
                    'service_number' => $officer->service_number,
                    'initials' => $officer->initials,
                    'surname' => $officer->surname,
                    'substantive_rank' => $officer->substantive_rank,
                    'present_station_name' => $officer->presentStation->name ?? 'N/A',
                ];
            });
        
        return response()->json($officers);
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

        if (!$commandId) {
            return redirect()->back()->with('error', 'You are not assigned to a command.');
        }

        // Find the pending (undocumented) posting INTO this Staff Officer's command.
        // Officer may still be in the old command until documentation is completed.
        $posting = \App\Models\OfficerPosting::where('officer_id', $officer->id)
            ->where('command_id', $commandId)
            ->where('is_current', false)
            ->whereNull('documented_at')
            ->orderBy('posting_date', 'desc')
            ->first();

        if (!$posting) {
            return redirect()->back()->with('error', 'No pending posting found for this officer in your command, or officer is already documented.');
        }

        if (!$posting->released_at) {
            return redirect()->back()->with('error', 'Officer has not been released by the old command yet.');
        }

        // Finalize posting on documentation
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Mark any current posting as not current
            \App\Models\OfficerPosting::where('officer_id', $officer->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Mark this posting as current and documented
            $posting->update([
                'is_current' => true,
                'documented_at' => now(),
                'documented_by' => $user->id,
            ]);

            // Move officer to the new command
            $officer->update([
                'present_station' => $commandId,
                'date_posted_to_station' => $posting->posting_date ?? now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to document officer: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "Officer {$officer->service_number} has been documented successfully.");
    }

    // Release an officer from current command (Staff Officer only)
    public function release($id)
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

        if (!$commandId) {
            return redirect()->back()->with('error', 'You are not assigned to a command.');
        }

        $officer = \App\Models\Officer::findOrFail($id);

        // Only release officers currently in this Staff Officer's command
        if ((int)$officer->present_station !== (int)$commandId) {
            return redirect()->back()->with('error', 'You can only release officers currently in your command.');
        }

        $pendingPosting = \App\Models\OfficerPosting::where('officer_id', $officer->id)
            ->where('is_current', false)
            ->whereNull('documented_at')
            ->whereNull('released_at')
            ->orderBy('posting_date', 'desc')
            ->first();

        if (!$pendingPosting) {
            return redirect()->back()->with('error', 'No pending posting found to release, or officer is already released/documented.');
        }

        $pendingPosting->update([
            'released_at' => now(),
            'released_by' => $user->id,
        ]);

        return redirect()->back()->with('success', "Officer {$officer->service_number} has been released successfully.");
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
            $query->where(function ($q) use ($search) {
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
        $officer = \App\Models\Officer::with(['presentStation.zone', 'user', 'nextOfKin', 'documents', 'acceptedQueries.issuedBy'])
            ->findOrFail($id);

        // Load accepted queries for display
        $acceptedQueries = $officer->acceptedQueries()
            ->with('issuedBy')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        // Load history data
        // Only show completed transfers in history
        // New workflow: both release_letter_printed AND accepted_by_new_command must be true
        // Legacy workflow: documented_at must not be null (for postings before new workflow)
        $postingsHistory = $officer->postings()
            ->with('command')
            ->where(function($q) {
                // New workflow: completed transfers
                $q->where(function($subQ) {
                    $subQ->where('release_letter_printed', true)
                         ->where('accepted_by_new_command', true);
                })
                // Legacy workflow: documented postings (before new workflow fields existed)
                ->orWhere(function($subQ) {
                    $subQ->whereNull('release_letter_printed')
                         ->whereNotNull('documented_at');
                });
            })
            ->orderBy('posting_date', 'desc')
            ->get();

        // Load queries and automatically expire any that are overdue
        $queriesHistory = $officer->queries()
            ->with('issuedBy')
            ->orderBy('issued_at', 'desc')
            ->get();

        // Automatically expire overdue queries before displaying to HRD
        $expiredCount = 0;
        $notificationService = app(NotificationService::class);
        foreach ($queriesHistory as $query) {
            if ($query->isOverdue()) {
                try {
                    DB::beginTransaction();
                    $query->update([
                        'status' => 'DISAPPROVAL',
                        'reviewed_at' => now(),
                    ]);
                    DB::commit();
                    $query->refresh();
                    
                    // Send notification if not already sent
                    if ($query->officer && $query->officer->user) {
                        try {
                            $notificationService->notifyQueryExpired($query);
                        } catch (\Exception $e) {
                            // Log but don't fail if notification fails
                            Log::warning('Failed to send query expiration notification in HRD profile view', [
                                'query_id' => $query->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    
                    $expiredCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to expire query in HRD profile view', [
                        'query_id' => $query->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        if ($expiredCount > 0) {
            // Refresh the queries collection after expiration
            $queriesHistory = $officer->queries()
                ->with('issuedBy')
                ->orderBy('issued_at', 'desc')
                ->get();
        }

        $promotionsHistory = $officer->promotions()
            ->orderBy('promotion_date', 'desc')
            ->get();

        return view('dashboards.hrd.officer-show', compact('officer', 'acceptedQueries', 'postingsHistory', 'queriesHistory', 'promotionsHistory'));
    }

    public function edit($id)
    {
        $officer = \App\Models\Officer::with(['presentStation.zone', 'nextOfKin', 'user'])->findOrFail($id);

        // Load related data for dropdowns
        $commands = \App\Models\Command::where('is_active', true)->with('zone')->orderBy('name')->get();
        $zones = \App\Models\Zone::where('is_active', true)->orderBy('name')->get();

        // Nigerian states and LGAs (same as onboarding)
        $nigerianStates = [
            'Abia',
            'Adamawa',
            'Akwa Ibom',
            'Anambra',
            'Bauchi',
            'Bayelsa',
            'Benue',
            'Borno',
            'Cross River',
            'Delta',
            'Ebonyi',
            'Edo',
            'Ekiti',
            'Enugu',
            'FCT',
            'Gombe',
            'Imo',
            'Jigawa',
            'Kaduna',
            'Kano',
            'Katsina',
            'Kebbi',
            'Kogi',
            'Kwara',
            'Lagos',
            'Nasarawa',
            'Niger',
            'Ogun',
            'Ondo',
            'Osun',
            'Oyo',
            'Plateau',
            'Rivers',
            'Sokoto',
            'Taraba',
            'Yobe',
            'Zamfara'
        ];

        $geopoliticalZones = [
            'North Central',
            'North East',
            'North West',
            'South East',
            'South South',
            'South West'
        ];

        // Use standard rank abbreviations (same as manning level)
        $ranks = [
            'CGC',
            'DCG',
            'ACG',
            'CC',
            'DC',
            'AC',
            'CSC',
            'SC',
            'DSC',
            'ASC I',
            'ASC II',
            'IC',
            'AIC',
            'CA I',
            'CA II',
            'CA III',
        ];

        $gradeLevels = [
            'GL 01',
            'GL 02',
            'GL 03',
            'GL 04',
            'GL 05',
            'GL 06',
            'GL 07',
            'GL 08',
            'GL 09',
            'GL 10',
            'GL 11',
            'GL 12',
            'GL 13',
            'GL 14',
            'GL 15',
            'GL 16',
            'GL 17'
        ];

        // Prepare education data from officer
        $educationData = [];

        // First, try to get all education entries from additional_qualification JSON
        if ($officer->additional_qualification) {
            $allEducation = json_decode($officer->additional_qualification, true);
            if (is_array($allEducation) && count($allEducation) > 0) {
                // Check if first entry in JSON matches entry_qualification (new format with all entries)
                $firstEntryMatches = isset($allEducation[0]) &&
                    isset($allEducation[0]['qualification']) &&
                    $allEducation[0]['qualification'] === $officer->entry_qualification;

                if ($firstEntryMatches) {
                    // New format: All entries (including first) are in JSON with universities
                    $educationData = $allEducation;
                } else {
                    // Old format: JSON only has entries from index 1, need to prepend first entry
                    // Try to get university from first entry in JSON if it exists and matches
                    $firstEntryUniversity = '';
                    if (
                        isset($allEducation[0]) && isset($allEducation[0]['qualification']) &&
                        $allEducation[0]['qualification'] === $officer->entry_qualification
                    ) {
                        // First entry in JSON might be the same as our first entry, use its university
                        $firstEntryUniversity = $allEducation[0]['university'] ?? '';
                        // Remove it from the array since we're using it as the first entry
                        array_shift($allEducation);
                    }

                    $firstEntry = [
                        'university' => $firstEntryUniversity, // Try to get from JSON if available
                        'qualification' => $officer->entry_qualification,
                        'discipline' => $officer->discipline ?? ''
                    ];
                    $educationData = array_merge([$firstEntry], $allEducation);
                }
            }
        }

        // Fallback: If no JSON data, reconstruct from legacy fields only
        if (empty($educationData) && $officer->entry_qualification) {
            // First education entry from entry_qualification and discipline (no university available)
            $educationData[] = [
                'university' => '', // Not stored in legacy format
                'qualification' => $officer->entry_qualification,
                'discipline' => $officer->discipline ?? ''
            ];
        }

        $institutions = Institution::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $disciplines = Discipline::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $qualifications = Qualification::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return view('forms.officer.edit', compact('officer', 'commands', 'zones', 'nigerianStates', 'geopoliticalZones', 'ranks', 'gradeLevels', 'educationData', 'institutions', 'disciplines', 'qualifications'));
    }

    public function update(Request $request, $id)
    {
        $officer = \App\Models\Officer::findOrFail($id);

        // Store original values for comparison
        $originalRank = $officer->substantive_rank;
        $originalInterdicted = $officer->interdicted;
        $originalSuspended = $officer->suspended;
        $originalDismissed = $officer->dismissed;
        $originalIsActive = $officer->is_active;
        $originalPresentStation = $officer->present_station;
        $originalDatePosted = $officer->date_posted_to_station;
        $originalUnit = $officer->unit;

        $validated = $request->validate([
            'initials' => 'required|string|max:50',
            'surname' => 'required|string|max:255',
            'sex' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'date_of_first_appointment' => 'required|date',
            'date_of_present_appointment' => 'nullable|date',
            'substantive_rank' => 'required|string|max:255',
            'salary_grade_level' => 'required|string|max:10',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'geopolitical_zone' => 'required|string|max:255',
            'marital_status' => 'required|string|max:50',
            'present_station' => 'required|exists:commands,id',
            'date_posted_to_station' => 'nullable|date',
            'residential_address' => 'required|string',
            'permanent_home_address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'unit' => 'nullable|string|max:255',
            'education' => 'required|array|min:1',
            'education.*.university' => 'required|string|max:255',
            'education.*.qualification' => 'required|string|max:255',
            'education.*.discipline' => 'nullable|string|max:255',
            'interdicted' => 'nullable|boolean',
            'suspended' => 'nullable|boolean',
            'quartered' => 'nullable|boolean',
            'dismissed' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Process education data
        $education = $validated['education'];

        // Upsert into shared master lists for future selection
        app(EducationMasterDataSync::class)->syncFromEducationArray($education);

        $validated['entry_qualification'] = isset($education[0]) ? $education[0]['qualification'] : null;
        $validated['discipline'] = isset($education[0]) ? ($education[0]['discipline'] ?? null) : null;
        $validated['additional_qualification'] = count($education) > 0 ? json_encode($education) : null; // Store ALL entries including first one
        unset($validated['education']);

        // Convert checkbox values
        $validated['interdicted'] = $request->has('interdicted');
        $validated['suspended'] = $request->has('suspended');
        $validated['quartered'] = $request->has('quartered');
        $validated['dismissed'] = $request->has('dismissed');
        $validated['is_active'] = $request->has('is_active') ? true : ($request->has('is_active') === false ? false : $officer->is_active);

        // Update officer
        $officer->update($validated);

        // Refresh to get updated values
        $officer->refresh();

        // Send notifications for changes
        $notificationService = app(\App\Services\NotificationService::class);

        // Rank change notification
        if ($originalRank !== $officer->substantive_rank) {
            $notificationService->notifyRankChanged($officer, $originalRank, $officer->substantive_rank);
        }

        // Interdiction status change notification
        if ($originalInterdicted !== $officer->interdicted) {
            $notificationService->notifyInterdictionStatusChanged($officer, $officer->interdicted);
        }

        // Suspension status change notification
        if ($originalSuspended !== $officer->suspended) {
            $notificationService->notifySuspensionStatusChanged($officer, $officer->suspended);
        }

        // Dismissal notification
        if (!$originalDismissed && $officer->dismissed) {
            $notificationService->notifyOfficerDismissed($officer);
        }

        // Active status change notification
        if ($originalIsActive !== $officer->is_active) {
            $notificationService->notifyActiveStatusChanged($officer, $officer->is_active);
        }

        // Command/Station change notification
        if ($originalPresentStation !== $officer->present_station) {
            $oldCommand = \App\Models\Command::find($originalPresentStation);
            $newCommand = \App\Models\Command::find($officer->present_station);
            $notificationService->notifyCommandChanged($officer, $oldCommand, $newCommand);
        }

        // Date posted change notification
        if ($originalDatePosted != $officer->date_posted_to_station) {
            $notificationService->notifyDatePostedChanged($officer, $originalDatePosted, $officer->date_posted_to_station);
        }

        // Unit change notification
        if ($originalUnit !== $officer->unit) {
            $notificationService->notifyUnitChanged($officer, $originalUnit ?? '', $officer->unit ?? '');
        }

        return redirect()->route('hrd.officers.show', $officer->id)
            ->with('success', 'Officer information updated successfully.');
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
                'pendingAllocations' => collect([]),
                'recentCourses' => collect([]),
                'upcomingCourses' => collect([]),
                'pendingQueries' => collect([]),
                'currentRosterTitle' => null,
                'rosterRole' => null,
                'pendingAperAssignments' => collect([]),
            ]);
        }

        // Eager-load relationships used by the dashboard view (avoids lazy-loading surprises)
        $officer->loadMissing([
            'presentStation.zone',
        ]);

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
            ->sum('number_of_days');
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

        // 6. Pending Quarter Allocations
        // Load pending quarter allocations for this officer
        // Show ALL pending allocations regardless of is_current or age - officer needs to see all pending actions
        $pendingAllocations = OfficerQuarter::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->with(['quarter:id,quarter_number,quarter_type,command_id', 'allocatedBy:id,email', 'allocatedBy.officer:id,user_id,initials,surname'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 7. Recent Course Nominations
        $recentCourses = OfficerCourse::where('officer_id', $officer->id)
            ->orderBy('start_date', 'desc')
            ->take(5)
            ->get();

        $upcomingCourses = OfficerCourse::where('officer_id', $officer->id)
            ->where('is_completed', false)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get();

        // 8. Pending Queries (queries that need response)
        // Note: Expired queries are handled by the scheduled cron job (queries:check-expired)
        $pendingQueries = Query::where('officer_id', $officer->id)
            ->where('status', 'PENDING_RESPONSE')
            ->with(['issuedBy'])
            ->orderBy('issued_at', 'desc')
            ->get();

        // 9. Current Roster Assignment and Role
        $currentRosterTitle = null;
        $rosterRole = null;
        $dutyRosterService = app(\App\Services\DutyRosterService::class);
        $year = date('Y');
        $commandId = $officer->present_station;

        // Get officer's role in roster (OIC/2IC) using the service method which handles date filtering properly
        $rosterRole = $dutyRosterService->getOfficerRoleInRoster($officer->id, $commandId, $year);

        if ($rosterRole) {
            // If officer is OIC/2IC, get the roster unit
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-12-31";

            $rosterAsOIC = \App\Models\DutyRoster::where('command_id', $commandId)
                ->whereIn('status', ['APPROVED', 'SUBMITTED'])
                ->where(function ($query) use ($officer) {
                    $query->where('oic_officer_id', $officer->id)
                        ->orWhere('second_in_command_officer_id', $officer->id);
                })
                ->where(function ($query) use ($startDate, $endDate) {
                    // If both dates are NULL, treat as valid (no date filtering)
                    $query->where(function ($nullQuery) {
                        $nullQuery->whereNull('roster_period_start')
                            ->whereNull('roster_period_end');
                    })
                        // Otherwise, check date overlap
                        ->orWhere(function ($dateQuery) use ($startDate, $endDate) {
                        $dateQuery->whereNotNull('roster_period_start')
                            ->whereNotNull('roster_period_end')
                            ->where(function ($overlapQuery) use ($startDate, $endDate) {
                                $overlapQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                    ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                    ->orWhere(function ($spanQuery) use ($startDate, $endDate) {
                                        $spanQuery->where('roster_period_start', '<=', $startDate)
                                            ->where('roster_period_end', '>=', $endDate);
                                    });
                            });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($rosterAsOIC) {
                $currentRosterTitle = $rosterAsOIC->unit;
            }
        } else {
            // If not OIC/2IC, check if they're assigned as a subordinate
            $currentRosterAssignment = \App\Models\RosterAssignment::where('officer_id', $officer->id)
                ->whereHas('roster', function ($query) {
                    $query->whereIn('status', ['APPROVED', 'SUBMITTED']);
                })
                ->with(['roster:id,unit,command_id,status'])
                ->latest('duty_date')
                ->first();

            $currentRosterTitle = $currentRosterAssignment?->roster?->unit ?? null;
        }

        // 10. Pending APER Form Assignments
        // Get forms where user is assigned as Reporting Officer or Countersigning Officer
        $pendingAperAssignments = collect();
        
        // Reporting Officer assignments (status is REPORTING_OFFICER)
        $reportingOfficerForms = APERForm::where('reporting_officer_id', $user->id)
            ->where('status', 'REPORTING_OFFICER')
            ->with(['officer', 'timeline'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($form) {
                $form->assignment_type = 'Reporting Officer';
                $form->assignment_route = 'officer.aper-forms.access';
                $form->assignment_route_param = $form->officer_id;
                return $form;
            });
        
        // Countersigning Officer assignments (status is COUNTERSIGNING_OFFICER)
        $countersigningOfficerForms = APERForm::where('countersigning_officer_id', $user->id)
            ->where('status', 'COUNTERSIGNING_OFFICER')
            ->with(['officer', 'timeline', 'reportingOfficer'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($form) {
                $form->assignment_type = 'Countersigning Officer';
                $form->assignment_route = 'officer.aper-forms.countersigning';
                $form->assignment_route_param = $form->id;
                return $form;
            });
        
        $pendingAperAssignments = $reportingOfficerForms->concat($countersigningOfficerForms)
            ->sortByDesc('updated_at')
            ->take(5);

        return view('dashboards.officer.dashboard', compact(
            'officer',
            'emolumentStatus',
            'leaveBalance',
            'passStatus',
            'recentApplications',
            'pendingQueries',
            'activeTimeline',
            'pendingAllocations',
            'recentCourses',
            'upcomingCourses',
            'currentRosterTitle',
            'rosterRole',
            'pendingAperAssignments'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        // Load all necessary relationships
        $officer->load([
            'presentStation.zone',
            'nextOfKin',
            'user'
        ]);

        // Check if officer has completed onboarding
        // An officer is considered onboarded if they have essential fields filled
        $isOnboarded = $officer->date_of_birth &&
            $officer->phone_number &&
            $officer->date_of_first_appointment &&
            $officer->nextOfKin()->where('is_primary', true)->exists();

        return view('dashboards.officer.profile', compact('officer', 'isOnboarded'));
    }

    public function updateProfilePicture(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return response()->json(['message' => 'Officer record not found.'], 404);
        }

        // Check if officer has completed onboarding
        $isOnboarded = $officer->date_of_birth &&
            $officer->phone_number &&
            $officer->date_of_first_appointment &&
            $officer->nextOfKin()->where('is_primary', true)->exists();

        if (!$isOnboarded) {
            return response()->json(['message' => 'You can only change your profile picture after completing onboarding.'], 403);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Delete old profile picture if exists
            if ($officer->profile_picture_url) {
                $oldPath = storage_path('app/public/' . $officer->profile_picture_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $officer->update([
                'profile_picture_url' => $path,
                'profile_picture_updated_at' => now(),
            ]);

            // Refresh to get updated model
            $officer->refresh();

            return response()->json([
                'message' => 'Profile picture updated successfully.',
                'profile_picture_url' => $officer->getProfilePictureUrlFull()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update profile picture: ' . $e->getMessage()], 500);
        }
    }

    public function settings()
    {
        $user = auth()->user();
        return view('dashboards.officer.settings', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password_confirmation.required' => 'Please confirm your new password.',
        ]);

        // Get fresh user instance from database to ensure we have latest password
        $user = \App\Models\User::find(auth()->id());

        if (!$user) {
            return redirect()->back()
                ->withErrors(['current_password' => 'User not found.'])
                ->withInput($request->except('current_password', 'new_password', 'new_password_confirmation'));
        }

        // Verify current password - trim whitespace and check
        $currentPassword = trim($request->current_password);

        if (empty($currentPassword)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is required.'])
                ->withInput($request->except('current_password', 'new_password', 'new_password_confirmation'));
        }

        // Check password - use the password attribute directly
        if (!\Illuminate\Support\Facades\Hash::check($currentPassword, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect. Please check and try again.'])
                ->withInput($request->except('current_password', 'new_password', 'new_password_confirmation'));
        }

        // Update password
        $user->password = \Hash::make($request->new_password);
        $user->save();

        // Send notification (in-app)
        $notificationService = app(\App\Services\NotificationService::class);
        $notification = $notificationService->notify(
            $user,
            'password_changed',
            'Password Changed Successfully',
            'Your password has been changed successfully. If you did not make this change, please contact support immediately.',
            null,
            null,
            false // Don't queue email, we'll send it synchronously below
        );

        // Send email immediately (synchronously) for password changes since it's critical
        try {
            if ($user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\NotificationMail($user, $notification)
                );
                \Log::info('Password change email sent synchronously', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                \Log::warning('Cannot send password change email: user has no email', [
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send password change email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the password change if email fails
        }

        return redirect()->route('officer.settings')
            ->with('success', 'Password changed successfully. A notification has been sent to your email.');
    }

    public function contactDetails()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        return view('dashboards.officer.contact-details', compact('officer'));
    }

    public function updateContactDetails(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'residential_address' => 'required|string|max:500',
            'permanent_home_address' => 'required|string|max:500',
        ]);

        // While quartered:
        // - residential_address is the "quartered address" and is auto-synced from the accepted quarter allocation
        // - permanent_home_address remains editable
        // - phone_number remains editable
        if ($officer->quartered) {
            $normalizeAddress = static function ($value): string {
                $v = (string) $value;
                $v = str_replace(["\r\n", "\r"], "\n", $v);
                return trim($v);
            };

            $incomingResidential = $normalizeAddress($validated['residential_address'] ?? '');
            $currentResidential = $normalizeAddress($officer->residential_address ?? '');

            // If we can determine the accepted current quarter, always force residential_address to it.
            $currentAcceptedAllocation = OfficerQuarter::where('officer_id', $officer->id)
                ->where('is_current', true)
                ->where('status', 'ACCEPTED')
                ->with('quarter')
                ->first();

            $expectedQuarteredAddress = QuarterAddressFormatter::format($currentAcceptedAllocation?->quarter);
            if ($expectedQuarteredAddress !== '') {
                $validated['residential_address'] = $expectedQuarteredAddress;
            } elseif ($incomingResidential !== $currentResidential) {
                // Fallback: if we can't resolve a quarter address, still prevent changing residential while quartered.
                return redirect()->back()
                    ->withErrors([
                        'residential_address' => 'Your residential address is locked while you are quartered.',
                    ])
                    ->withInput($request->except(['residential_address']));
            }
        }

        $oldValues = [
            'phone_number' => $officer->phone_number,
            'residential_address' => $officer->residential_address,
            'permanent_home_address' => $officer->permanent_home_address,
        ];

        $officer->update($validated);
        $officer->refresh();

        $newValues = [
            'phone_number' => $officer->phone_number,
            'residential_address' => $officer->residential_address,
            'permanent_home_address' => $officer->permanent_home_address,
        ];

        $auditLog = new AuditLog([
            'user_id' => $user->id,
            'action' => 'officer_contact_updated',
            'entity_type' => 'Officer',
            'entity_id' => $officer->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
        $auditLog->created_at = now();
        $auditLog->save();

        // Notify Staff Officer(s) assigned to the officer's command (in-app + email)
        $commandId = $officer->present_station;
        if (!empty($commandId)) {
            $staffOfficers = \App\Models\User::query()
                ->where('is_active', true)
                ->whereHas('roles', function ($query) use ($commandId) {
                    $query->where('name', 'Staff Officer')
                        ->where('user_roles.is_active', true)
                        ->where('user_roles.command_id', $commandId);
                })
                ->get();

            if ($staffOfficers->isNotEmpty()) {
                $officerName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                $serviceNumber = $officer->service_number ?? 'N/A';

                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyMany(
                    $staffOfficers,
                    'officer_contact_updated',
                    'Officer Contact Details Updated',
                    "Officer {$officerName} ({$serviceNumber}) updated their phone number and/or address details.",
                    'officer',
                    $officer->id,
                    true
                );
            }
        }

        return redirect()
            ->route('officer.settings.contact-details')
            ->with('success', 'Contact details updated successfully.');
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

    public function courseNominations()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        $query = OfficerCourse::where('officer_id', $officer->id)
            ->with('nominatedBy.officer');

        // Filter by status
        if ($status = request('status')) {
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'pending') {
                $query->where('is_completed', false);
            }
        }

        // Filter by year
        if ($year = request('year')) {
            $query->whereYear('start_date', $year);
        }

        // Sorting
        $sortBy = request('sort_by', 'start_date');
        $sortOrder = request('sort_order', 'desc');

        $sortableColumns = [
            'course_name' => 'course_name',
            'course_type' => 'course_type',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'completion_date' => 'completion_date',
            'status' => 'is_completed',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'start_date';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($column, $order);

        $courses = $query->paginate(20)->withQueryString();

        // Get unique years for filter
        $years = OfficerCourse::where('officer_id', $officer->id)
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

        return view('dashboards.officer.course-nominations', compact('courses', 'years'));
    }
}



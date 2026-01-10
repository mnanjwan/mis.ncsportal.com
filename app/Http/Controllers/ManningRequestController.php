<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;
use App\Models\Officer;
use App\Models\MovementOrder;
use App\Models\OfficerPosting;
use App\Models\Command;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PostingWorkflowService;
use App\Services\RankComparisonService;
use App\Services\ZonalPostingValidationService;

class ManningRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Staff Officer')->except([
            'index',
            'show',
            'hrdIndex',
            'hrdShow',
            'hrdPrint',
            'hrdPrintSelected',
            'hrdMatch',
            'hrdMatchAll',
            'hrdViewDraft',
            'hrdGenerateOrder',
            'hrdDraftIndex',
            'hrdDraftRemoveOfficer',
            'hrdDraftSwapOfficer',
            'hrdDraftUpdateDestination',
            'hrdDraftPublish',
            'hrdDraftPrint',
            'hrdPublishedIndex',
            'hrdAddToDraft',
            'zoneCoordinatorIndex',
            'zoneCoordinatorShow',
            'zoneCoordinatorMatchAll',
            'areaControllerIndex',
            'areaControllerShow',
            'areaControllerApprove',
            'areaControllerReject',
            'dcAdminIndex',
            'dcAdminShow',
            'dcAdminApprove',
            'dcAdminReject'
        ]);
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

        // Get manning requests for this command
        $query = ManningRequest::where('command_id', $commandId)
            ->with(['requestedBy', 'approvedBy', 'items.matchedOfficer'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $requests = $query->paginate(20)->withQueryString();

        // Calculate approved officers count by rank across all approved requests
        // This shows how many officers HRD/Zone Coordinator has matched for each rank vs how many were requested
        // Note: When HRD/Zone Coordinator matches multiple officers for one item, they create additional items
        // So we count items with matched_officer_id for approved count
        $allItems = ManningRequestItem::whereHas('manningRequest', function ($q) use ($commandId) {
            $q->where('command_id', $commandId)
                ->where('status', 'APPROVED');
        })
            ->with('manningRequest')
            ->get();

        // Group by rank and type, then calculate requested vs approved
        $approvedOfficersByRank = $allItems->groupBy('rank')->map(function ($items, $rank) {
            $requested = $items->sum('quantity_needed');
            $approved = $items->whereNotNull('matched_officer_id')->count(); // Count matched officers
            
            // Get types for this rank (should be consistent, but showing both if mixed)
            $types = $items->pluck('manningRequest.type')->unique()->values()->toArray();
            $typeLabel = count($types) === 1 
                ? ($types[0] === 'ZONE' ? 'Zone' : 'General') 
                : 'Mixed';
            
            return (object) [
                'rank' => $rank,
                'requested_count' => $requested,
                'approved_count' => $approved,
                'type' => $typeLabel
            ];
        })->values()->sortBy('rank');

        return view('dashboards.staff-officer.manning-level', compact('requests', 'command', 'approvedOfficersByRank'));
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

        // Use standard rank abbreviations for Request Items dropdown
        // (HRD matching will handle mapping to database rank variations)
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

        // Get unique qualifications
        $qualifications = Officer::whereNotNull('entry_qualification')
            ->orWhereNotNull('additional_qualification')
            ->get()
            ->flatMap(function ($officer) {
                $quals = [];
                if ($officer->entry_qualification) {
                    $quals[] = $officer->entry_qualification;
                }
                if ($officer->additional_qualification) {
                    $quals[] = $officer->additional_qualification;
                }
                return $quals;
            })
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        return view('forms.manning-level.create', compact('command', 'ranks', 'qualifications'));
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

        // Validate items array exists
        if (!$request->has('items') || !is_array($request->items) || count($request->items) === 0) {
            return redirect()->back()
                ->with('error', 'Please add at least one request item.')
                ->withInput();
        }

        // Filter out empty items
        $validItems = array_filter($request->items, function ($item) {
            return !empty($item['rank']) && !empty($item['quantity_needed']);
        });

        if (count($validItems) === 0) {
            return redirect()->back()
                ->with('error', 'Please add at least one request item with rank and quantity.')
                ->withInput();
        }

        $request->validate([
            'type' => 'required|in:GENERAL,ZONE',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.rank' => 'required|string|max:100',
            'items.*.quantity_needed' => 'required|integer|min:1',
            'items.*.sex_requirement' => 'nullable|in:ANY,M,F',
            'items.*.qualification_requirement' => 'nullable|string|max:255',
            'items.*.qualification_custom' => 'nullable|string|max:255',
        ]);
        
        // Validate Zone type requests - only GL 7 and below ranks allowed
        if ($request->type === 'ZONE') {
            $zoneRanks = ['IC', 'AIC', 'CA I', 'CA II', 'CA III']; // GL 7 and below ranks
            $invalidRanks = [];
            
            foreach ($validItems as $item) {
                if (!in_array($item['rank'], $zoneRanks)) {
                    $invalidRanks[] = $item['rank'];
                }
            }
            
            if (!empty($invalidRanks)) {
                return redirect()->back()
                    ->with('error', 'Zone Manning Level requests can only include ranks GL 7 and below (IC, AIC, CA I, CA II, CA III). Invalid ranks: ' . implode(', ', array_unique($invalidRanks)))
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Create manning request (status: DRAFT, will be SUBMITTED when sent to Area Controller)
            $manningRequest = ManningRequest::create([
                'command_id' => $commandId,
                'type' => $request->type,
                'requested_by' => $user->id,
                'status' => 'DRAFT',
                'notes' => $request->notes,
                'submitted_at' => null, // Will be set when submitted to Area Controller
            ]);

            // Create request items (only valid items)
            foreach ($validItems as $item) {
                // Use custom qualification if provided, otherwise use selected qualification
                $qualification = null;
                if (!empty($item['qualification_custom'])) {
                    $qualification = $item['qualification_custom'];
                } elseif (!empty($item['qualification_requirement'])) {
                    $qualification = $item['qualification_requirement'];
                }

                ManningRequestItem::create([
                    'manning_request_id' => $manningRequest->id,
                    'rank' => $item['rank'],
                    'quantity_needed' => (int) $item['quantity_needed'],
                    'sex_requirement' => $item['sex_requirement'] ?? 'ANY',
                    'qualification_requirement' => $qualification,
                ]);
            }

            DB::commit();

            return redirect()->route('staff-officer.manning-level')
                ->with('success', 'Manning level request created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create manning level request: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Get the manning request with items and matched officers
        $request = ManningRequest::with(['command', 'requestedBy', 'approvedBy', 'items.matchedOfficer'])->findOrFail($id);

        // Verify access - only show requests from Staff Officer's command
        if (!$commandId || $request->command_id != $commandId) {
            abort(403, 'You can only view manning requests for your assigned command');
        }

        // Calculate approved counts by rank for this specific request
        // Group items by rank and count requested vs approved
        $approvedCountsByRank = $request->items->groupBy('rank')->map(function ($items, $rank) {
            $requested = $items->sum('quantity_needed');
            $approved = $items->whereNotNull('matched_officer_id')->count(); // Count matched officers
            return [
                'rank' => $rank,
                'requested' => $requested,
                'approved' => $approved
            ];
        })->values();

        return view('dashboards.staff-officer.manning-level-show', compact('request', 'approvedCountsByRank'));
    }

    public function submit($id)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Get the manning request
        $request = ManningRequest::findOrFail($id);

        // Verify access
        if (!$commandId || $request->command_id != $commandId) {
            abort(403, 'You can only submit manning requests for your assigned command');
        }

        // Only allow submitting DRAFT requests
        if ($request->status !== 'DRAFT') {
            return redirect()->route('staff-officer.manning-level.show', $id)
                ->with('error', 'Only DRAFT requests can be submitted.');
        }

        try {
            $request->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            // Refresh to load relationships
            $request->refresh();

            // Notify Area Controllers about submitted request
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestSubmitted($request);

            return redirect()->route('staff-officer.manning-level.show', $id)
                ->with('success', 'Manning request submitted successfully! It is now pending Area Controller approval.');

        } catch (\Exception $e) {
            Log::error('Failed to submit manning request: ' . $e->getMessage());
            return redirect()->route('staff-officer.manning-level.show', $id)
                ->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Get the manning request
        $request = ManningRequest::with(['command', 'items'])->findOrFail($id);

        // Verify access
        if (!$commandId || $request->command_id != $commandId) {
            abort(403, 'You can only edit manning requests for your assigned command');
        }

        // Only allow editing DRAFT requests
        if ($request->status !== 'DRAFT') {
            return redirect()->route('staff-officer.manning-level.show', $id)
                ->with('error', 'Only DRAFT requests can be edited.');
        }

        // Get ranks and qualifications for dropdowns (abbreviations for display)
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

        // Map existing item ranks to abbreviations if they're stored as full names
        $rankMappingToAbbr = [
            'Comptroller General of Customs (CGC) GL18' => 'CGC',
            'Comptroller General' => 'CGC',
            'Deputy Comptroller General of Customs (DCG) GL17' => 'DCG',
            'Deputy Comptroller General' => 'DCG',
            'Assistant Comptroller General (ACG) of Customs GL 16' => 'ACG',
            'Assistant Comptroller General' => 'ACG',
            'Comptroller of Customs (CC) GL15' => 'CC',
            'Comptroller' => 'CC',
            'Deputy Comptroller of Customs (DC) GL14' => 'DC',
            'Deputy Comptroller' => 'DC',
            'Assistant Comptroller of Customs (AC) GL13' => 'AC',
            'Assistant Comptroller' => 'AC',
            'Chief Superintendent of Customs (CSC) GL12' => 'CSC',
            'Chief Superintendent' => 'CSC',
            'Superintendent of Customs (SC) GL11' => 'SC',
            'Superintendent' => 'SC',
            'Deputy Superintendent of Customs (DSC) GL10' => 'DSC',
            'Deputy Superintendent' => 'DSC',
            'Assistant Superintendent of Customs Grade I (ASC I) GL 09' => 'ASC I',
            'Assistant Superintendent Grade I' => 'ASC I',
            'Assistant Superintendent of Customs Grade II (ASC II) GL 08' => 'ASC II',
            'Assistant Superintendent Grade II' => 'ASC II',
            'Assistant Superintendent' => 'ASC I', // Default to ASC I if ambiguous
            'Inspector of Customs (IC) GL07' => 'IC',
            'Inspector' => 'IC',
            'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
            'Assistant Inspector' => 'AIC',
            'Customs Assistant I (CA I) GL05' => 'CA I',
            'Customs Assistant I' => 'CA I',
            'Customs Assistant II (CA II) GL04' => 'CA II',
            'Customs Assistant II' => 'CA II',
            'Customs Assistant III (CA III) GL03' => 'CA III',
            'Customs Assistant III' => 'CA III',
            'Customs Assistant' => 'CA I', // Default to CA I if ambiguous
        ];

        // Convert existing item ranks to abbreviations
        foreach ($request->items as $item) {
            if (isset($rankMappingToAbbr[$item->rank])) {
                $item->rank = $rankMappingToAbbr[$item->rank];
            } elseif (in_array($item->rank, $ranks)) {
                // Already an abbreviation, keep it
                continue;
            } else {
                // Try partial matching
                foreach ($rankMappingToAbbr as $fullName => $abbr) {
                    if (stripos($item->rank, $fullName) !== false || stripos($fullName, $item->rank) !== false) {
                        $item->rank = $abbr;
                        break;
                    }
                }
            }
        }

        $qualifications = [
            'B.Sc',
            'B.A',
            'B.Eng',
            'M.Sc',
            'M.A',
            'M.Eng',
            'Ph.D',
            'HND',
            'ND',
            'NCE',
        ];

        $command = $request->command;

        return view('forms.manning-level.edit', compact('request', 'ranks', 'qualifications', 'command'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Get the manning request
        $manningRequest = ManningRequest::findOrFail($id);

        // Verify access
        if (!$commandId || $manningRequest->command_id != $commandId) {
            abort(403, 'You can only update manning requests for your assigned command');
        }

        // Only allow updating DRAFT requests
        if ($manningRequest->status !== 'DRAFT') {
            return redirect()->route('staff-officer.manning-level.show', $id)
                ->with('error', 'Only DRAFT requests can be updated.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.rank' => 'required|string|max:100',
            'items.*.quantity_needed' => 'required|integer|min:1',
            'items.*.sex_requirement' => 'nullable|in:ANY,M,F',
            'items.*.qualification_requirement' => 'nullable|string|max:255',
        ]);
        
        // Validate Zone type requests - only GL 7 and below ranks allowed
        if ($manningRequest->type === 'ZONE') {
            $zoneRanks = ['IC', 'AIC', 'CA I', 'CA II', 'CA III']; // GL 7 and below ranks
            
            // Filter out empty items
            $validItems = array_filter($request->items, function ($item) {
                return !empty($item['rank']) && !empty($item['quantity_needed']);
            });
            
            $invalidRanks = [];
            foreach ($validItems as $item) {
                if (!in_array($item['rank'], $zoneRanks)) {
                    $invalidRanks[] = $item['rank'];
                }
            }
            
            if (!empty($invalidRanks)) {
                return redirect()->back()
                    ->with('error', 'Zone Manning Level requests can only include ranks GL 7 and below (IC, AIC, CA I, CA II, CA III). Invalid ranks: ' . implode(', ', array_unique($invalidRanks)))
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Update request notes
            $manningRequest->update([
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $manningRequest->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $qualification = $item['qualification_custom'] ?? $item['qualification_requirement'] ?? null;
                ManningRequestItem::create([
                    'manning_request_id' => $manningRequest->id,
                    'rank' => $item['rank'],
                    'quantity_needed' => $item['quantity_needed'],
                    'sex_requirement' => $item['sex_requirement'] ?? 'ANY',
                    'qualification_requirement' => $qualification,
                ]);
            }

            DB::commit();

            return redirect()->route('staff-officer.manning-level.show', $manningRequest->id)
                ->with('success', 'Manning level request updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update manning level request: ' . $e->getMessage())
                ->withInput();
        }
    }

    // HRD Methods
    public function hrdIndex(Request $request)
    {
        // Determine which statuses to include based on the tab
        $tab = $request->get('tab', 'pending');
        $statuses = ['APPROVED'];

        // For published tab, also include FULFILLED status
        if ($tab === 'published') {
            $statuses = ['APPROVED', 'FULFILLED'];
        }

        $query = ManningRequest::with(['command.zone', 'requestedBy', 'approvedBy', 'items'])
            ->whereIn('status', $statuses)
            ->where('type', 'GENERAL'); // HRD only processes GENERAL type requests

        // Get all item IDs that are in draft deployments
        $itemIdsInDraft = ManningDeploymentAssignment::whereHas('deployment', function ($q) {
            $q->where('status', 'DRAFT');
        })
            ->whereNotNull('manning_request_item_id')
            ->pluck('manning_request_item_id')
            ->unique();

        // Get request IDs that have items in draft
        $requestIdsInDraft = collect();
        if ($itemIdsInDraft->isNotEmpty()) {
            $requestIdsInDraft = ManningRequestItem::whereIn('id', $itemIdsInDraft)
                ->pluck('manning_request_id')
                ->unique();
        }

        // Get request IDs that are fully published (all items have matched_officer_id set)
        // A request is published when all its items have matched_officer_id set
        // Include both APPROVED and FULFILLED statuses (FULFILLED means it was published)
        // Only GENERAL type requests for HRD
        $requestIdsPublished = DB::table('manning_request_items')
            ->join('manning_requests', 'manning_request_items.manning_request_id', '=', 'manning_requests.id')
            ->whereIn('manning_requests.status', ['APPROVED', 'FULFILLED'])
            ->where('manning_requests.type', 'GENERAL') // HRD only processes GENERAL type
            ->select('manning_request_items.manning_request_id')
            ->groupBy('manning_request_items.manning_request_id')
            ->havingRaw('COUNT(*) = SUM(CASE WHEN manning_request_items.matched_officer_id IS NOT NULL THEN 1 ELSE 0 END)')
            ->havingRaw('COUNT(*) > 0')
            ->pluck('manning_request_id');

        // Filter by tab if provided (tab was already determined above for status filtering)
        if ($tab === 'in_draft') {
            // Show only requests with items in draft
            if ($requestIdsInDraft->isNotEmpty()) {
                $query->whereIn('manning_requests.id', $requestIdsInDraft);
            } else {
                // No requests in draft, return empty result
                $query->whereRaw('1 = 0');
            }
        } elseif ($tab === 'published') {
            // Show only fully published requests
            if ($requestIdsPublished->isNotEmpty()) {
                $query->whereIn('manning_requests.id', $requestIdsPublished);
            } else {
                // No published requests, return empty result
                $query->whereRaw('1 = 0');
            }
        } else {
            // Default: pending (not in draft and not published)
            $excludeIds = $requestIdsInDraft->merge($requestIdsPublished)->unique();
            if ($excludeIds->isNotEmpty()) {
                $query->whereNotIn('manning_requests.id', $excludeIds);
            }
        }

        // Sorting - Default to latest requests first (created_at desc)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $sortableColumns = [
            'command' => function ($query, $order) {
                $query->leftJoin('commands', 'manning_requests.command_id', '=', 'commands.id')
                    ->orderBy('commands.name', $order);
            },
            'status' => 'status',
            'approved_at' => 'approved_at',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $requests = $query->select('manning_requests.*')->paginate(20)->withQueryString();

        // Mark which requests have items in draft and which are published for display
        $requests->getCollection()->transform(function ($manningRequest) use ($requestIdsInDraft, $requestIdsPublished) {
            $manningRequest->has_items_in_draft = $requestIdsInDraft->contains($manningRequest->id);
            $manningRequest->is_published = $requestIdsPublished->contains($manningRequest->id);
            return $manningRequest;
        });

        // Calculate counts for each tab - only GENERAL type for HRD
        $allApprovedRequestIds = ManningRequest::where('status', 'APPROVED')
            ->where('type', 'GENERAL') // HRD only processes GENERAL type
            ->pluck('id');
        $pendingCount = 0;
        $inDraftCount = $requestIdsInDraft->count();
        $publishedCount = $requestIdsPublished->count();

        // Calculate pending count (not in draft and not published)
        $excludeIds = $requestIdsInDraft->merge($requestIdsPublished)->unique();
        if ($excludeIds->isNotEmpty()) {
            $pendingCount = $allApprovedRequestIds->diff($excludeIds)->count();
        } else {
            $pendingCount = $allApprovedRequestIds->count();
        }

        return view('dashboards.hrd.manning-requests', compact('requests', 'requestIdsInDraft', 'requestIdsPublished', 'pendingCount', 'inDraftCount', 'publishedCount'));
    }

    public function hrdShow($id)
    {
        $request = ManningRequest::with(['command', 'requestedBy', 'approvedBy', 'items'])
            ->findOrFail($id);

        // HRD can only view GENERAL type requests
        if ($request->type !== 'GENERAL') {
            abort(403, 'HRD can only access General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        // Check which items have officers in draft deployments
        $itemIds = $request->items->pluck('id');
        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function ($q) {
                $q->where('status', 'DRAFT');
            })
            ->pluck('manning_request_item_id')
            ->unique();

        return view('dashboards.hrd.manning-request-show', compact('request', 'itemsInDraft'));
    }

    public function hrdPrint($id)
    {
        $request = ManningRequest::with(['command.zone', 'requestedBy', 'approvedBy', 'items.matchedOfficer'])
            ->findOrFail($id);

        // HRD can only print GENERAL type requests
        if ($request->type !== 'GENERAL') {
            abort(403, 'HRD can only print General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        // Get all officers that were posted for this manning request
        // First try to get from published deployment assignments
        $assignments = ManningDeploymentAssignment::where('manning_request_id', $id)
            ->whereHas('deployment', function ($q) {
                $q->where('status', 'PUBLISHED');
            })
            ->with(['officer', 'fromCommand', 'toCommand'])
            ->get();

        // If no assignments found, get from items with matched_officer_id
        $postedOfficers = [];
        $serialNumber = 1;

        if ($assignments->count() > 0) {
            // Use assignments if available
            foreach ($assignments as $assignment) {
                if ($assignment->officer) {
                    $officer = $assignment->officer;
                    $postedOfficers[] = [
                        'serial_number' => $serialNumber++,
                        'service_number' => $officer->service_number ?? 'N/A',
                        'rank' => $officer->substantive_rank ?? 'N/A',
                        'initials' => $officer->initials ?? '',
                        'surname' => $officer->surname ?? '',
                        'current_posting' => $assignment->fromCommand->name ?? 'N/A',
                        'new_posting' => $assignment->toCommand->name ?? 'N/A',
                    ];
                }
            }
        } else {
            // Fallback: Get from items with matched_officer_id and their posting history
            foreach ($request->items as $item) {
                if ($item->matchedOfficer) {
                    $officer = $item->matchedOfficer;
                    // Get the officer's posting history to find where they were posted from
                    $previousPosting = OfficerPosting::where('officer_id', $officer->id)
                        ->where('is_current', false)
                        ->with('command')
                        ->orderBy('posting_date', 'desc')
                        ->first();

                    // Current posting = where they came from (previous posting)
                    // New posting = where they went to (request's command, which is their current posting)
                    $currentPosting = $previousPosting && $previousPosting->command
                        ? $previousPosting->command->name
                        : 'N/A';
                    $newPosting = $request->command->name ?? 'N/A';

                    $postedOfficers[] = [
                        'serial_number' => $serialNumber++,
                        'service_number' => $officer->service_number ?? 'N/A',
                        'rank' => $officer->substantive_rank ?? 'N/A',
                        'initials' => $officer->initials ?? '',
                        'surname' => $officer->surname ?? '',
                        'current_posting' => $currentPosting,
                        'new_posting' => $newPosting,
                    ];
                }
            }
        }

        // Sort by rank (highest to lowest) to match movement order format
        $rankOrder = [
            'CGC' => 1,
            'DCG' => 2,
            'ACG' => 3,
            'CC' => 4,
            'DC' => 5,
            'AC' => 6,
            'CSC' => 7,
            'SC' => 8,
            'DSC' => 9,
            'ASC I' => 10,
            'ASC II' => 11,
            'IC' => 12,
            'AIC' => 13,
            'CA I' => 14,
            'CA II' => 15,
            'CA III' => 16,
        ];

        usort($postedOfficers, function ($a, $b) use ($rankOrder) {
            $rankA = $rankOrder[strtoupper($a['rank'])] ?? 999;
            $rankB = $rankOrder[strtoupper($b['rank'])] ?? 999;
            if ($rankA === $rankB) {
                return strcmp($a['surname'], $b['surname']);
            }
            return $rankA <=> $rankB;
        });

        // Re-number after sorting
        foreach ($postedOfficers as $index => &$officer) {
            $officer['serial_number'] = $index + 1;
        }

        return view('dashboards.hrd.manning-request-print', compact('request', 'postedOfficers'));
    }

    public function hrdPrintSelected(Request $request)
    {
        $ids = $request->get('ids');

        if (!$ids) {
            abort(400, 'No request IDs provided');
        }

        // Parse comma-separated IDs
        $requestIds = explode(',', $ids);
        $requestIds = array_filter(array_map('trim', $requestIds));

        if (empty($requestIds)) {
            abort(400, 'Invalid request IDs');
        }

        // Get all selected requests with their relationships - only GENERAL type for HRD
        $requests = ManningRequest::with(['command.zone', 'requestedBy', 'approvedBy', 'items.matchedOfficer'])
            ->whereIn('id', $requestIds)
            ->where('type', 'GENERAL') // HRD can only print GENERAL type requests
            ->get();

        // Group requests by command
        $requestsByCommand = $requests->groupBy('command_id');

        // For each command, get all posted officers
        $commandsData = [];

        foreach ($requestsByCommand as $commandId => $commandRequests) {
            $command = $commandRequests->first()->command;
            $allPostedOfficers = [];
            $serialNumber = 1;

            foreach ($commandRequests as $manningRequest) {
                // Get all officers that were posted for this manning request
                $assignments = ManningDeploymentAssignment::where('manning_request_id', $manningRequest->id)
                    ->whereHas('deployment', function ($q) {
                        $q->where('status', 'PUBLISHED');
                    })
                    ->with(['officer', 'fromCommand', 'toCommand'])
                    ->get();

                if ($assignments->count() > 0) {
                    // Use assignments if available
                    foreach ($assignments as $assignment) {
                        if ($assignment->officer) {
                            $officer = $assignment->officer;
                            $allPostedOfficers[] = [
                                'serial_number' => $serialNumber++,
                                'service_number' => $officer->service_number ?? 'N/A',
                                'rank' => $officer->substantive_rank ?? 'N/A',
                                'initials' => $officer->initials ?? '',
                                'surname' => $officer->surname ?? '',
                                'current_posting' => $assignment->fromCommand->name ?? 'N/A',
                                'new_posting' => $assignment->toCommand->name ?? 'N/A',
                                'request_id' => $manningRequest->id,
                            ];
                        }
                    }
                } else {
                    // Fallback: Get from items with matched_officer_id
                    foreach ($manningRequest->items as $item) {
                        if ($item->matchedOfficer) {
                            $officer = $item->matchedOfficer;
                            $previousPosting = OfficerPosting::where('officer_id', $officer->id)
                                ->where('is_current', false)
                                ->with('command')
                                ->orderBy('posting_date', 'desc')
                                ->first();

                            $currentPosting = $previousPosting && $previousPosting->command
                                ? $previousPosting->command->name
                                : 'N/A';
                            $newPosting = $manningRequest->command->name ?? 'N/A';

                            $allPostedOfficers[] = [
                                'serial_number' => $serialNumber++,
                                'service_number' => $officer->service_number ?? 'N/A',
                                'rank' => $officer->substantive_rank ?? 'N/A',
                                'initials' => $officer->initials ?? '',
                                'surname' => $officer->surname ?? '',
                                'current_posting' => $currentPosting,
                                'new_posting' => $newPosting,
                                'request_id' => $manningRequest->id,
                            ];
                        }
                    }
                }
            }

            // Sort by rank
            $rankOrder = [
                'CGC' => 1,
                'DCG' => 2,
                'ACG' => 3,
                'CC' => 4,
                'DC' => 5,
                'AC' => 6,
                'CSC' => 7,
                'SC' => 8,
                'DSC' => 9,
                'ASC I' => 10,
                'ASC II' => 11,
                'IC' => 12,
                'AIC' => 13,
                'CA I' => 14,
                'CA II' => 15,
                'CA III' => 16,
            ];

            usort($allPostedOfficers, function ($a, $b) use ($rankOrder) {
                $rankA = $rankOrder[strtoupper($a['rank'])] ?? 999;
                $rankB = $rankOrder[strtoupper($b['rank'])] ?? 999;
                if ($rankA === $rankB) {
                    return strcmp($a['surname'], $b['surname']);
                }
                return $rankA <=> $rankB;
            });

            // Re-number after sorting
            foreach ($allPostedOfficers as $index => &$officer) {
                $officer['serial_number'] = $index + 1;
            }

            $commandsData[] = [
                'command' => $command,
                'officers' => $allPostedOfficers,
                'requests' => $commandRequests,
            ];
        }

        return view('dashboards.hrd.manning-requests-print-selected', compact('commandsData'));
    }

    // Zone Coordinator Methods
    public function zoneCoordinatorIndex(Request $request)
    {
        $user = auth()->user();
        
        // Check if user is Zone Coordinator
        if (!$user->hasRole('Zone Coordinator')) {
            abort(403, 'Only Zone Coordinators can access this page');
        }

        // Get Zone Coordinator's zone
        $validationService = app(ZonalPostingValidationService::class);
        $zoneCommandIds = $validationService->getZoneCommandIds($user);

        if (empty($zoneCommandIds)) {
            // No zone commands, return empty paginated result
            $requests = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                20,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            $requestIdsInDraft = collect();
            $requestIdsPublished = collect();
            $pendingCount = 0;
            $inDraftCount = 0;
            $publishedCount = 0;
            return view('dashboards.hrd.manning-requests', compact('requests', 'requestIdsInDraft', 'requestIdsPublished', 'pendingCount', 'inDraftCount', 'publishedCount'))->with('routePrefix', 'zone-coordinator');
        }

        // Determine which statuses to include based on the tab
        $tab = $request->get('tab', 'pending');
        $statuses = ['APPROVED'];

        // For published tab, also include FULFILLED status
        if ($tab === 'published') {
            $statuses = ['APPROVED', 'FULFILLED'];
        }

        $query = ManningRequest::with(['command.zone', 'requestedBy', 'approvedBy', 'items'])
            ->whereIn('status', $statuses)
            ->where('type', 'ZONE') // Zone Coordinators only process ZONE type requests
            ->whereIn('command_id', $zoneCommandIds); // Only requests from Zone Coordinator's zone

        // Get all item IDs that are in draft deployments (only Zone Coordinator drafts)
        $zoneCoordinatorUserIds = User::whereHas('roles', function($q) {
            $q->where('name', 'Zone Coordinator');
        })->pluck('id')->toArray();

        // Get all draft assignments created by Zone Coordinators
        $itemIdsInDraft = ManningDeploymentAssignment::whereHas('deployment', function ($q) use ($zoneCoordinatorUserIds) {
            $q->where('status', 'DRAFT');
            if (!empty($zoneCoordinatorUserIds)) {
                $q->whereIn('created_by', $zoneCoordinatorUserIds); // Only Zone Coordinator drafts
            } else {
                $q->whereRaw('1 = 0'); // No zone coordinators, no drafts
            }
        })
            ->whereNotNull('manning_request_item_id')
            ->pluck('manning_request_item_id')
            ->unique();

        // Get request IDs that have items in draft, but only for ZONE type requests in Zone Coordinator's zone
        $requestIdsInDraft = collect();
        if ($itemIdsInDraft->isNotEmpty()) {
            $requestIdsInDraft = ManningRequestItem::whereIn('id', $itemIdsInDraft)
                ->whereHas('manningRequest', function($q) use ($zoneCommandIds) {
                    $q->where('type', 'ZONE')
                      ->whereIn('command_id', $zoneCommandIds);
                })
                ->pluck('manning_request_id')
                ->unique();
        }

        // Get request IDs that are fully published (all items have matched_officer_id set)
        // Only ZONE type requests from Zone Coordinator's zone
        $requestIdsPublished = DB::table('manning_request_items')
            ->join('manning_requests', 'manning_request_items.manning_request_id', '=', 'manning_requests.id')
            ->whereIn('manning_requests.status', ['APPROVED', 'FULFILLED'])
            ->where('manning_requests.type', 'ZONE') // Zone Coordinators only process ZONE type
            ->whereIn('manning_requests.command_id', $zoneCommandIds) // Only from Zone Coordinator's zone
            ->select('manning_request_items.manning_request_id')
            ->groupBy('manning_request_items.manning_request_id')
            ->havingRaw('COUNT(*) = SUM(CASE WHEN manning_request_items.matched_officer_id IS NOT NULL THEN 1 ELSE 0 END)')
            ->havingRaw('COUNT(*) > 0')
            ->pluck('manning_request_id');

        // Filter by tab if provided
        if ($tab === 'in_draft') {
            // Show only requests with items in draft
            if ($requestIdsInDraft->isNotEmpty()) {
                $query->whereIn('manning_requests.id', $requestIdsInDraft);
            } else {
                // No requests in draft, return empty result
                $query->whereRaw('1 = 0');
            }
        } elseif ($tab === 'published') {
            // Show only fully published requests
            if ($requestIdsPublished->isNotEmpty()) {
                $query->whereIn('manning_requests.id', $requestIdsPublished);
            } else {
                // No published requests, return empty result
                $query->whereRaw('1 = 0');
            }
        } else {
            // Default: pending (not in draft and not published)
            $excludeIds = $requestIdsInDraft->merge($requestIdsPublished)->unique();
            if ($excludeIds->isNotEmpty()) {
                $query->whereNotIn('manning_requests.id', $excludeIds);
            }
        }

        // Sorting - Default to latest requests first (created_at desc)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $sortableColumns = [
            'command' => function ($query, $order) {
                $query->leftJoin('commands', 'manning_requests.command_id', '=', 'commands.id')
                    ->orderBy('commands.name', $order);
            },
            'status' => 'status',
            'approved_at' => 'approved_at',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $requests = $query->select('manning_requests.*')->paginate(20)->withQueryString();

        // Mark which requests have items in draft and which are published for display
        $requests->getCollection()->transform(function ($manningRequest) use ($requestIdsInDraft, $requestIdsPublished) {
            $manningRequest->has_items_in_draft = $requestIdsInDraft->contains($manningRequest->id);
            $manningRequest->is_published = $requestIdsPublished->contains($manningRequest->id);
            return $manningRequest;
        });

        // Calculate counts for each tab - only ZONE type for Zone Coordinators
        $allApprovedRequestIds = ManningRequest::where('status', 'APPROVED')
            ->where('type', 'ZONE') // Zone Coordinators only process ZONE type
            ->whereIn('command_id', $zoneCommandIds) // Only from Zone Coordinator's zone
            ->pluck('id');
        $pendingCount = 0;
        $inDraftCount = $requestIdsInDraft->count();
        $publishedCount = $requestIdsPublished->count();

        // Calculate pending count (not in draft and not published)
        $excludeIds = $requestIdsInDraft->merge($requestIdsPublished)->unique();
        if ($excludeIds->isNotEmpty()) {
            $pendingCount = $allApprovedRequestIds->diff($excludeIds)->count();
        } else {
            $pendingCount = $allApprovedRequestIds->count();
        }

        return view('dashboards.hrd.manning-requests', compact('requests', 'requestIdsInDraft', 'requestIdsPublished', 'pendingCount', 'inDraftCount', 'publishedCount'))->with('routePrefix', 'zone-coordinator');
    }

    public function zoneCoordinatorShow($id)
    {
        $user = auth()->user();
        
        // Check if user is Zone Coordinator
        if (!$user->hasRole('Zone Coordinator')) {
            abort(403, 'Only Zone Coordinators can access this page');
        }

        // Get Zone Coordinator's zone
        $validationService = app(ZonalPostingValidationService::class);
        $zoneCommandIds = $validationService->getZoneCommandIds($user);

        $request = ManningRequest::with(['command', 'requestedBy', 'approvedBy', 'items'])
            ->findOrFail($id);

        // Zone Coordinators can only view ZONE type requests
        if ($request->type !== 'ZONE') {
            abort(403, 'Zone Coordinators can only access Zone Manning Level requests. General requests are handled by HRD.');
        }

        // Verify request is from Zone Coordinator's zone
        if (empty($zoneCommandIds) || !in_array($request->command_id, $zoneCommandIds)) {
            abort(403, 'You can only view manning requests for commands in your zone.');
        }

        // Check which items have officers in draft deployments (only Zone Coordinator drafts)
        $zoneCoordinatorUserIds = User::whereHas('roles', function($q) {
            $q->where('name', 'Zone Coordinator');
        })->pluck('id')->toArray();

        $itemIds = $request->items->pluck('id');
        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function ($q) use ($zoneCoordinatorUserIds) {
                $q->where('status', 'DRAFT')
                  ->whereIn('created_by', $zoneCoordinatorUserIds); // Only Zone Coordinator drafts
            })
            ->pluck('manning_request_item_id')
            ->unique();

        return view('dashboards.hrd.manning-request-show', compact('request', 'itemsInDraft'))->with('routePrefix', 'zone-coordinator');
    }

    public function zoneCoordinatorMatchAll(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user is Zone Coordinator
        if (!$user->hasRole('Zone Coordinator')) {
            abort(403, 'Only Zone Coordinators can access this page');
        }

        $manningRequest = ManningRequest::with('items')->findOrFail($id);

        // Zone Coordinators can only match officers for ZONE type requests
        if ($manningRequest->type !== 'ZONE') {
            abort(403, 'Zone Coordinators can only match officers for Zone Manning Level requests.');
        }

        // Get Zone Coordinator's zone
        $validationService = app(ZonalPostingValidationService::class);
        $zoneCommandIds = $validationService->getZoneCommandIds($user);

        if (empty($zoneCommandIds)) {
            return redirect()->route('zone-coordinator.manning-requests.show', $id)
                ->with('error', 'You do not have any commands assigned to your zone.');
        }

        // Verify request is from Zone Coordinator's zone
        if (!in_array($manningRequest->command_id, $zoneCommandIds)) {
            abort(403, 'You can only match officers for manning requests in your zone.');
        }

        // Get all pending items (not published, not in draft)
        $itemIds = $manningRequest->items->pluck('id');
        $zoneCoordinatorUserIds = User::whereHas('roles', function($q) {
            $q->where('name', 'Zone Coordinator');
        })->pluck('id')->toArray();

        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function ($q) use ($zoneCoordinatorUserIds) {
                $q->where('status', 'DRAFT');
                if (!empty($zoneCoordinatorUserIds)) {
                    $q->whereIn('created_by', $zoneCoordinatorUserIds); // Only Zone Coordinator drafts
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->pluck('manning_request_item_id')
            ->unique();

        $publishedItemIds = $manningRequest->items->whereNotNull('matched_officer_id')->pluck('id');
        $pendingItems = $manningRequest->items->whereNotIn('id', $itemsInDraft)->whereNotIn('id', $publishedItemIds);

        if ($pendingItems->isEmpty()) {
            return redirect()->route('zone-coordinator.manning-requests.show', $id)
                ->with('info', 'All ranks have already been matched or are in draft.');
        }

        try {
            DB::beginTransaction();

            // Get or create SHARED active draft deployment for Zone Coordinators
            $deployment = ManningDeployment::draft()
                ->whereIn('created_by', $zoneCoordinatorUserIds) // Only Zone Coordinator drafts
                ->latest()
                ->first();

            if (!$deployment) {
                // No active draft exists - create a new shared draft for Zone Coordinators
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();

                $newNumber = $lastDeployment ? ((int) substr($lastDeployment->deployment_number, -3)) + 1 : 1;
                $deploymentNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $deployment = ManningDeployment::create([
                    'deployment_number' => $deploymentNumber,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                ]);
            }

            $destinationCommand = $manningRequest->command;
            $totalOfficersAdded = 0;
            $ranksMatched = [];

            // Match each pending item
            foreach ($pendingItems as $item) {
                // First, get all potential officers from Zone Coordinator's zone
                $potentialOfficers = Officer::where('is_active', true)
                    ->where('is_deceased', false)
                    ->where('interdicted', false)
                    ->where('suspended', false)
                    ->where('dismissed', false)
                    ->whereNotNull('substantive_rank')
                    ->whereNotNull('present_station')
                    ->whereIn('present_station', $zoneCommandIds) // Only officers in Zone Coordinator's zone
                    ->get();

                // Exclude officers from requesting command
                if ($manningRequest->command_id) {
                    $potentialOfficers = $potentialOfficers->reject(function($officer) use ($manningRequest) {
                        return $officer->present_station == $manningRequest->command_id;
                    });
                }

                // Filter by GL 7 and below only (for Zone Coordinators)
                $filteredOfficerIds = [];
                foreach ($potentialOfficers as $officer) {
                    if ($validationService->isOfficerGL07OrBelow($officer->id)) {
                        $filteredOfficerIds[] = $officer->id;
                    }
                }
                
                // If no officers match GL 7 and below, skip this item
                if (empty($filteredOfficerIds)) {
                    continue;
                }

                // Build query with filtered officer IDs
                $query = Officer::whereIn('id', $filteredOfficerIds)
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->where('interdicted', false)
                    ->where('suspended', false)
                    ->where('dismissed', false)
                    ->whereNotNull('substantive_rank')
                    ->whereNotNull('present_station');

                // Match rank using the same logic as hrdMatchAll
                if (!empty($item->rank)) {
                    $requestedRank = trim($item->rank);

                    $rankMappingToAbbr = [
                        'Inspector of Customs (IC) GL07' => 'IC',
                        'Inspector' => 'IC',
                        'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
                        'Assistant Inspector' => 'AIC',
                        'Customs Assistant I (CA I) GL05' => 'CA I',
                        'Customs Assistant I' => 'CA I',
                        'Customs Assistant II (CA II) GL04' => 'CA II',
                        'Customs Assistant II' => 'CA II',
                        'Customs Assistant III (CA III) GL03' => 'CA III',
                        'Customs Assistant III' => 'CA III',
                        'Customs Assistant' => 'CA I',
                    ];

                    $ranksToMatch = [strtolower(trim($requestedRank))];

                    $foundAbbr = null;
                    foreach ($rankMappingToAbbr as $fullName => $abbr) {
                        $fullNameLower = strtolower(trim($fullName));
                        $abbrLower = strtolower(trim($abbr));
                        $requestedLower = strtolower(trim($requestedRank));

                        if ($requestedLower === $abbrLower) {
                            $foundAbbr = $abbr;
                            foreach ($rankMappingToAbbr as $fn => $a) {
                                if (strtolower(trim($a)) === $abbrLower) {
                                    $ranksToMatch[] = strtolower(trim($fn));
                                }
                            }
                            break;
                        }

                        if ($requestedLower === $fullNameLower) {
                            $foundAbbr = $abbr;
                            $ranksToMatch[] = $abbrLower;
                            foreach ($rankMappingToAbbr as $fn => $a) {
                                if (strtolower(trim($a)) === strtolower(trim($abbr))) {
                                    $ranksToMatch[] = strtolower(trim($fn));
                                }
                            }
                            break;
                        }
                    }

                    $ranksToMatch = array_unique($ranksToMatch);

                    $query->where(function ($q) use ($ranksToMatch, $requestedRank) {
                        $requestedRankLower = strtolower(trim($requestedRank));

                        foreach ($ranksToMatch as $rankToMatch) {
                            $q->orWhereRaw('LOWER(TRIM(substantive_rank)) = ?', [$rankToMatch]);
                        }

                        if (strlen($requestedRank) <= 10) {
                            $q->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%(' . $requestedRankLower . ')%']);
                        }
                    });
                }

                // Filter by sex requirement
                if ($item->sex_requirement !== 'ANY') {
                    $query->where('sex', $item->sex_requirement);
                }

                // Exclude already matched officers
                $alreadyMatched = ManningRequestItem::where('manning_request_id', $id)
                    ->whereNotNull('matched_officer_id')
                    ->pluck('matched_officer_id');

                if ($alreadyMatched->isNotEmpty()) {
                    $query->whereNotIn('id', $alreadyMatched);
                }

                // Exclude officers already in Zone Coordinator drafts
                $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function ($q) use ($zoneCoordinatorUserIds) {
                    $q->where('status', 'DRAFT');
                    if (!empty($zoneCoordinatorUserIds)) {
                        $q->whereIn('created_by', $zoneCoordinatorUserIds);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                    ->pluck('officer_id');

                if ($officersInDraft->isNotEmpty()) {
                    $query->whereNotIn('id', $officersInDraft);
                }

                // Get matched officers - only from Zone Coordinator's zone
                $limit = max($item->quantity_needed * 5, 50);
                $matchedOfficers = $query->with(['presentStation'])
                    ->orderBy('present_station')
                    ->take($limit)
                    ->get();

                // Add officers to draft
                $quantityNeeded = $item->quantity_needed;
                $selectedOfficers = $matchedOfficers->take($quantityNeeded);
                $officersAddedForRank = 0;

                foreach ($selectedOfficers as $officer) {
                    $fromCommand = $officer->presentStation;

                    $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                        ->where('officer_id', $officer->id)
                        ->first();

                    if (!$existing) {
                        ManningDeploymentAssignment::create([
                            'manning_deployment_id' => $deployment->id,
                            'manning_request_id' => $manningRequest->id,
                            'manning_request_item_id' => $item->id,
                            'officer_id' => $officer->id,
                            'from_command_id' => $fromCommand?->id,
                            'to_command_id' => $destinationCommand->id,
                            'rank' => $officer->substantive_rank,
                        ]);
                        $officersAddedForRank++;
                        $totalOfficersAdded++;
                    }
                }

                if ($officersAddedForRank > 0) {
                    $ranksMatched[] = $item->rank . ' (' . $officersAddedForRank . ' officer' . ($officersAddedForRank > 1 ? 's' : '') . ')';
                }
            }

            DB::commit();

            if ($totalOfficersAdded > 0) {
                $message = "Successfully matched {$totalOfficersAdded} officer(s) for " . count($ranksMatched) . " rank(s): " . implode(', ', $ranksMatched);
                return redirect()->route('zone-coordinator.manning-requests.show', $id)
                    ->with('success', $message);
            } else {
                return redirect()->route('zone-coordinator.manning-requests.show', $id)
                    ->with('info', 'No matching officers found in your zone for the requested ranks.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to match all ranks (Zone Coordinator): ' . $e->getMessage(), [
                'exception' => $e,
                'manning_request_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('zone-coordinator.manning-requests.show', $id)
                ->with('error', 'Failed to match all ranks. Please try again.');
        }
    }

    public function hrdMatch(Request $request, $id)
    {
        $manningRequest = ManningRequest::with('items')->findOrFail($id);

        // HRD can only match officers for GENERAL type requests
        if ($manningRequest->type !== 'GENERAL') {
            abort(403, 'HRD can only match officers for General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        $itemId = $request->input('item_id');

        $item = ManningRequestItem::findOrFail($itemId);

        // Build query for matching officers
        // IMPORTANT: This is GLOBAL matching - searches ALL commands EXCEPT the requesting command
        // The requesting command only states their needs; HRD matches from other commands
        // Officers from the requesting command are EXCLUDED from results
        $query = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->where('interdicted', false)
            ->where('suspended', false)
            ->where('dismissed', false)
            ->whereNotNull('substantive_rank');

        // Search ALL commands EXCEPT the requesting command
        // Officers from the requesting command will be excluded below

        // Match rank - handle both abbreviation and full name formats
        // Manning requests store abbreviations (e.g., "SC", "ASC II"), but officers table
        // may have full names (e.g., "Superintendent of Customs (SC) GL11" or "Superintendent")
        // CRITICAL: "ASC II" must NOT match "Superintendent" - they are different ranks!
        if (!empty($item->rank)) {
            $requestedRank = trim($item->rank);

            // Use the same rank mapping as RankComparisonService for consistency
            $rankMappingToAbbr = [
                'Comptroller General of Customs (CGC) GL18' => 'CGC',
                'Comptroller General' => 'CGC',
                'Deputy Comptroller General of Customs (DCG) GL17' => 'DCG',
                'Deputy Comptroller General' => 'DCG',
                'Assistant Comptroller General (ACG) of Customs GL 16' => 'ACG',
                'Assistant Comptroller General' => 'ACG',
                'Comptroller of Customs (CC) GL15' => 'CC',
                'Comptroller' => 'CC',
                'Deputy Comptroller of Customs (DC) GL14' => 'DC',
                'Deputy Comptroller' => 'DC',
                'Assistant Comptroller of Customs (AC) GL13' => 'AC',
                'Assistant Comptroller' => 'AC',
                'Chief Superintendent of Customs (CSC) GL12' => 'CSC',
                'Chief Superintendent' => 'CSC',
                'Superintendent of Customs (SC) GL11' => 'SC',
                'Superintendent' => 'SC',
                'Deputy Superintendent of Customs (DSC) GL10' => 'DSC',
                'Deputy Superintendent' => 'DSC',
                'Assistant Superintendent of Customs Grade I (ASC I) GL 09' => 'ASC I',
                'Assistant Superintendent Grade I' => 'ASC I',
                'Assistant Superintendent of Customs Grade II (ASC II) GL 08' => 'ASC II',
                'Assistant Superintendent Grade II' => 'ASC II',
                'Assistant Superintendent' => 'ASC I', // Default to ASC I if ambiguous
                'Inspector of Customs (IC) GL07' => 'IC',
                'Inspector' => 'IC',
                'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
                'Assistant Inspector' => 'AIC',
                'Customs Assistant I (CA I) GL05' => 'CA I',
                'Customs Assistant I' => 'CA I',
                'Customs Assistant II (CA II) GL04' => 'CA II',
                'Customs Assistant II' => 'CA II',
                'Customs Assistant III (CA III) GL03' => 'CA III',
                'Customs Assistant III' => 'CA III',
                'Customs Assistant' => 'CA I', // Default to CA I if ambiguous
            ];

            // Build list of exact rank strings to match
            // Start with the requested rank itself
            $ranksToMatch = [strtolower(trim($requestedRank))];

            // If requested rank is an abbreviation, find all full name variations
            // If requested rank is a full name, find the abbreviation
            $foundAbbr = null;
            foreach ($rankMappingToAbbr as $fullName => $abbr) {
                $fullNameLower = strtolower(trim($fullName));
                $abbrLower = strtolower(trim($abbr));
                $requestedLower = strtolower(trim($requestedRank));

                // If requested matches abbreviation, add all full name variations
                if ($requestedLower === $abbrLower) {
                    $foundAbbr = $abbr;
                    // Find all full names that map to this abbreviation
                    foreach ($rankMappingToAbbr as $fn => $a) {
                        if (strtolower(trim($a)) === $abbrLower) {
                            $ranksToMatch[] = strtolower(trim($fn));
                        }
                    }
                    break;
                }

                // If requested matches a full name, add the abbreviation
                if ($requestedLower === $fullNameLower) {
                    $foundAbbr = $abbr;
                    $ranksToMatch[] = $abbrLower;
                    // Also add other full name variations for this abbreviation
                    foreach ($rankMappingToAbbr as $fn => $a) {
                        if (strtolower(trim($a)) === strtolower(trim($abbr))) {
                            $ranksToMatch[] = strtolower(trim($fn));
                        }
                    }
                    break;
                }
            }

            // Remove duplicates
            $ranksToMatch = array_unique($ranksToMatch);

            // Match using EXACT matches only (case-insensitive, trimmed)
            // This prevents "ASC II" from matching "Superintendent"
            $query->where(function ($q) use ($ranksToMatch, $requestedRank) {
                $requestedRankLower = strtolower(trim($requestedRank));

                // 1. Exact match for each rank variation
                foreach ($ranksToMatch as $rankToMatch) {
                    $q->orWhereRaw('LOWER(TRIM(substantive_rank)) = ?', [$rankToMatch]);
                }

                // 2. Match abbreviation in parentheses (e.g., "Superintendent of Customs (SC) GL11")
                // Only if requested rank is short (likely an abbreviation)
                if (strlen($requestedRank) <= 10) {
                    $q->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%(' . $requestedRankLower . ')%']);
                }
            });
        }

        // Ensure officer has a current command (but don't restrict which command)
        // This allows officers from ANY command to be matched
        $query->whereNotNull('present_station');

        // EXCLUDE officers from the requesting command
        // The requesting command is asking for officers, so we should not match officers from their own command
        if ($manningRequest->command_id) {
            $query->where('present_station', '!=', $manningRequest->command_id);
        }

        // Filter by sex requirement
        if ($item->sex_requirement !== 'ANY') {
            $query->where('sex', $item->sex_requirement);
        }

        // IMPORTANT: Global matching from all OTHER commands (excluding requesting command)
        // The requesting command only states their needs; HRD matches from other commands
        // Qualification is shown but NOT used as a filter - HRD can see all officers with the rank
        // and decide which ones to select based on all criteria

        // Exclude officers already matched to this request
        $alreadyMatched = ManningRequestItem::where('manning_request_id', $id)
            ->whereNotNull('matched_officer_id')
            ->pluck('matched_officer_id');

        if ($alreadyMatched->isNotEmpty()) {
            $query->whereNotIn('id', $alreadyMatched);
        }

        // Exclude officers already in draft deployments (global check - across all commands)
        $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function ($q) {
            $q->where('status', 'DRAFT');
        })
            ->pluck('officer_id');

        if ($officersInDraft->isNotEmpty()) {
            $query->whereNotIn('id', $officersInDraft);
        }

        // Get matched officers - search ALL commands EXCEPT requesting command
        // Increase limit to show more results from across all other commands
        // Results are from all OTHER commands - global matching excluding requesting command
        $limit = max($item->quantity_needed * 5, 50); // Increased limit to show more officers from all commands

        // Get total count before limiting (for display)
        $totalCount = $query->count();

        $matchedOfficers = $query->with(['presentStation.zone'])
            ->orderBy('present_station') // Group by command for easier review
            ->take($limit)
            ->get();

        // Get all unique commands that have matching officers (for debugging)
        $allMatchingCommandIds = $matchedOfficers->pluck('present_station')
            ->filter()
            ->unique()
            ->values();

        // Mark which officers match the qualification requirement (for display purposes only)
        $qualificationRequirement = $item->qualification_requirement ?? null;
        if ($qualificationRequirement) {
            $qualification = strtolower(trim($qualificationRequirement));
            $matchedOfficers = $matchedOfficers->map(function ($officer) use ($qualification) {
                $entryMatch = $officer->entry_qualification &&
                    stripos(strtolower($officer->entry_qualification), $qualification) !== false;
                $additionalMatch = $officer->additional_qualification &&
                    stripos(strtolower($officer->additional_qualification), $qualification) !== false;
                $officer->qualification_matches = $entryMatch || $additionalMatch;
                return $officer;
            });
        }

        // Log for debugging - verify global matching
        $requestingCommandId = $manningRequest->command_id;
        $requestingCommandName = $manningRequest->command->name ?? 'N/A';
        $uniqueCommandsInResults = $matchedOfficers->pluck('presentStation')->unique('id')->map(function ($cmd) {
            return $cmd ? ['id' => $cmd->id, 'name' => $cmd->name] : null;
        })->filter()->values();

        // Get sample ranks from matched officers to verify rank matching worked
        $sampleRanks = $matchedOfficers->take(5)->pluck('substantive_rank')->toArray();

        Log::info('HRD Manning Match - Global Search (ALL COMMANDS)', [
            'manning_request_id' => $id,
            'requesting_command_id' => $requestingCommandId,
            'requesting_command_name' => $requestingCommandName,
            'item_id' => $itemId,
            'requested_rank' => $item->rank,
            'rank_matching_note' => 'Using flexible rank matching (abbreviation and full name formats)',
            'sex_requirement' => $item->sex_requirement,
            'qualification_preference' => $item->qualification_requirement,
            'total_matching_officers_in_all_commands' => $totalCount,
            'returned_count' => $matchedOfficers->count(),
            'unique_commands_in_results' => $uniqueCommandsInResults->count(),
            'commands_represented' => $uniqueCommandsInResults->toArray(),
            'all_matching_command_ids' => $allMatchingCommandIds->toArray(),
            'sample_ranks_found' => $sampleRanks,
            'note' => 'Searching ALL commands EXCEPT requesting command. Officers from requesting command are excluded. Rank matching handles both abbreviations and full names.',
        ]);

        // AUTO-MATCH: Automatically add matched officers to draft deployment
        // This is an automated system - officers are automatically matched and added to draft
        try {
            DB::beginTransaction();

            // Get or create SHARED active draft deployment
            // IMPORTANT: All manning requests use the SAME shared draft
            // Officers from all requests are added to this single draft
            // Only create a new draft if no draft exists (e.g., after publishing)
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();

            if (!$deployment) {
                // No active draft exists - create a new shared draft
                // This will be used by ALL manning requests until it's published
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();

                $newNumber = $lastDeployment ? ((int) substr($lastDeployment->deployment_number, -3)) + 1 : 1;
                $deploymentNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $deployment = ManningDeployment::create([
                    'deployment_number' => $deploymentNumber,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                ]);
            }

            $destinationCommand = $manningRequest->command;
            $quantityNeeded = $item->quantity_needed;
            $officersAdded = 0;

            // Automatically select and add officers to draft
            // Take the first N officers that match (where N = quantity_needed)
            $selectedOfficers = $matchedOfficers->take($quantityNeeded);

            foreach ($selectedOfficers as $officer) {
                $fromCommand = $officer->presentStation;

                // Check if officer is already in this deployment
                $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                    ->where('officer_id', $officer->id)
                    ->first();

                if (!$existing) {
                    ManningDeploymentAssignment::create([
                        'manning_deployment_id' => $deployment->id,
                        'manning_request_id' => $manningRequest->id,
                        'manning_request_item_id' => $item->id,
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommand?->id,
                        'to_command_id' => $destinationCommand->id,
                        'rank' => $officer->substantive_rank,
                    ]);
                    $officersAdded++;
                }
            }

            DB::commit();

            if ($officersAdded > 0) {
                return redirect()->route('hrd.manning-requests.show', $id)
                    ->with('success', "{$officersAdded} officer(s) automatically matched and added to draft deployment.");
            } else {
                return redirect()->route('hrd.manning-requests.show', $id)
                    ->with('info', 'No new officers added. All matching officers may already be in the draft.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to auto-match and add to draft: ' . $e->getMessage(), [
                'exception' => $e,
                'manning_request_id' => $id,
                'item_id' => $itemId,
            ]);

            // If auto-add fails, show the manual selection view as fallback
            return view('dashboards.hrd.manning-request-matches', compact('manningRequest', 'item', 'matchedOfficers', 'totalCount', 'qualificationRequirement'))
                ->with('error', 'Auto-matching failed. Please select officers manually.');
        }
    }

    public function hrdMatchAll(Request $request, $id)
    {
        $manningRequest = ManningRequest::with('items')->findOrFail($id);

        // HRD can only match officers for GENERAL type requests
        if ($manningRequest->type !== 'GENERAL') {
            abort(403, 'HRD can only match officers for General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        // Get all pending items (not published, not in draft)
        $itemIds = $manningRequest->items->pluck('id');
        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function ($q) {
                $q->where('status', 'DRAFT');
            })
            ->pluck('manning_request_item_id')
            ->unique();

        $publishedItemIds = $manningRequest->items->whereNotNull('matched_officer_id')->pluck('id');
        $pendingItems = $manningRequest->items->whereNotIn('id', $itemsInDraft)->whereNotIn('id', $publishedItemIds);

        if ($pendingItems->isEmpty()) {
            return redirect()->route('hrd.manning-requests.show', $id)
                ->with('info', 'All ranks have already been matched or are in draft.');
        }

        try {
            DB::beginTransaction();

            // Get or create SHARED active draft deployment
            // IMPORTANT: All manning requests use the SAME shared draft
            // Officers from all requests are added to this single draft
            // Only create a new draft if no draft exists (e.g., after publishing)
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();

            if (!$deployment) {
                // No active draft exists - create a new shared draft
                // This will be used by ALL manning requests until it's published
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();

                $newNumber = $lastDeployment ? ((int) substr($lastDeployment->deployment_number, -3)) + 1 : 1;
                $deploymentNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $deployment = ManningDeployment::create([
                    'deployment_number' => $deploymentNumber,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                ]);
            }

            $destinationCommand = $manningRequest->command;
            $totalOfficersAdded = 0;
            $ranksMatched = [];

            // Match each pending item
            foreach ($pendingItems as $item) {
                // Build query for matching officers (same logic as hrdMatch)
                $query = Officer::where('is_active', true)
                    ->where('is_deceased', false)
                    ->where('interdicted', false)
                    ->where('suspended', false)
                    ->where('dismissed', false)
                    ->whereNotNull('substantive_rank')
                    ->whereNotNull('present_station');

                // Exclude officers from requesting command
                if ($manningRequest->command_id) {
                    $query->where('present_station', '!=', $manningRequest->command_id);
                }

                // Match rank using the same logic as hrdMatch
                if (!empty($item->rank)) {
                    $requestedRank = trim($item->rank);

                    $rankMappingToAbbr = [
                        'Comptroller General of Customs (CGC) GL18' => 'CGC',
                        'Comptroller General' => 'CGC',
                        'Deputy Comptroller General of Customs (DCG) GL17' => 'DCG',
                        'Deputy Comptroller General' => 'DCG',
                        'Assistant Comptroller General (ACG) of Customs GL 16' => 'ACG',
                        'Assistant Comptroller General' => 'ACG',
                        'Comptroller of Customs (CC) GL15' => 'CC',
                        'Comptroller' => 'CC',
                        'Deputy Comptroller of Customs (DC) GL14' => 'DC',
                        'Deputy Comptroller' => 'DC',
                        'Assistant Comptroller of Customs (AC) GL13' => 'AC',
                        'Assistant Comptroller' => 'AC',
                        'Chief Superintendent of Customs (CSC) GL12' => 'CSC',
                        'Chief Superintendent' => 'CSC',
                        'Superintendent of Customs (SC) GL11' => 'SC',
                        'Superintendent' => 'SC',
                        'Deputy Superintendent of Customs (DSC) GL10' => 'DSC',
                        'Deputy Superintendent' => 'DSC',
                        'Assistant Superintendent of Customs Grade I (ASC I) GL 09' => 'ASC I',
                        'Assistant Superintendent Grade I' => 'ASC I',
                        'Assistant Superintendent of Customs Grade II (ASC II) GL 08' => 'ASC II',
                        'Assistant Superintendent Grade II' => 'ASC II',
                        'Assistant Superintendent' => 'ASC I',
                        'Inspector of Customs (IC) GL07' => 'IC',
                        'Inspector' => 'IC',
                        'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
                        'Assistant Inspector' => 'AIC',
                        'Customs Assistant I (CA I) GL05' => 'CA I',
                        'Customs Assistant I' => 'CA I',
                        'Customs Assistant II (CA II) GL04' => 'CA II',
                        'Customs Assistant II' => 'CA II',
                        'Customs Assistant III (CA III) GL03' => 'CA III',
                        'Customs Assistant III' => 'CA III',
                        'Customs Assistant' => 'CA I',
                    ];

                    $ranksToMatch = [strtolower(trim($requestedRank))];

                    $foundAbbr = null;
                    foreach ($rankMappingToAbbr as $fullName => $abbr) {
                        $fullNameLower = strtolower(trim($fullName));
                        $abbrLower = strtolower(trim($abbr));
                        $requestedLower = strtolower(trim($requestedRank));

                        if ($requestedLower === $abbrLower) {
                            $foundAbbr = $abbr;
                            foreach ($rankMappingToAbbr as $fn => $a) {
                                if (strtolower(trim($a)) === $abbrLower) {
                                    $ranksToMatch[] = strtolower(trim($fn));
                                }
                            }
                            break;
                        }

                        if ($requestedLower === $fullNameLower) {
                            $foundAbbr = $abbr;
                            $ranksToMatch[] = $abbrLower;
                            foreach ($rankMappingToAbbr as $fn => $a) {
                                if (strtolower(trim($a)) === strtolower(trim($abbr))) {
                                    $ranksToMatch[] = strtolower(trim($fn));
                                }
                            }
                            break;
                        }
                    }

                    $ranksToMatch = array_unique($ranksToMatch);

                    $query->where(function ($q) use ($ranksToMatch, $requestedRank) {
                        $requestedRankLower = strtolower(trim($requestedRank));

                        foreach ($ranksToMatch as $rankToMatch) {
                            $q->orWhereRaw('LOWER(TRIM(substantive_rank)) = ?', [$rankToMatch]);
                        }

                        if (strlen($requestedRank) <= 10) {
                            $q->orWhereRaw('LOWER(substantive_rank) LIKE ?', ['%(' . $requestedRankLower . ')%']);
                        }
                    });
                }

                // Filter by sex requirement
                if ($item->sex_requirement !== 'ANY') {
                    $query->where('sex', $item->sex_requirement);
                }

                // Exclude already matched officers
                $alreadyMatched = ManningRequestItem::where('manning_request_id', $id)
                    ->whereNotNull('matched_officer_id')
                    ->pluck('matched_officer_id');

                if ($alreadyMatched->isNotEmpty()) {
                    $query->whereNotIn('id', $alreadyMatched);
                }

                // Exclude officers already in draft
                $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function ($q) {
                    $q->where('status', 'DRAFT');
                })
                    ->pluck('officer_id');

                if ($officersInDraft->isNotEmpty()) {
                    $query->whereNotIn('id', $officersInDraft);
                }

                // Get matched officers
                $limit = max($item->quantity_needed * 5, 50);
                $matchedOfficers = $query->with(['presentStation'])
                    ->orderBy('present_station')
                    ->take($limit)
                    ->get();

                // Add officers to draft
                $quantityNeeded = $item->quantity_needed;
                $selectedOfficers = $matchedOfficers->take($quantityNeeded);
                $officersAddedForRank = 0;

                foreach ($selectedOfficers as $officer) {
                    $fromCommand = $officer->presentStation;

                    $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                        ->where('officer_id', $officer->id)
                        ->first();

                    if (!$existing) {
                        ManningDeploymentAssignment::create([
                            'manning_deployment_id' => $deployment->id,
                            'manning_request_id' => $manningRequest->id,
                            'manning_request_item_id' => $item->id,
                            'officer_id' => $officer->id,
                            'from_command_id' => $fromCommand?->id,
                            'to_command_id' => $destinationCommand->id,
                            'rank' => $officer->substantive_rank,
                        ]);
                        $officersAddedForRank++;
                        $totalOfficersAdded++;
                    }
                }

                if ($officersAddedForRank > 0) {
                    $ranksMatched[] = $item->rank . ' (' . $officersAddedForRank . ' officer' . ($officersAddedForRank > 1 ? 's' : '') . ')';
                }
            }

            DB::commit();

            $message = "Successfully matched {$totalOfficersAdded} officer(s) for " . count($ranksMatched) . " rank(s): " . implode(', ', $ranksMatched);
            return redirect()->route('hrd.manning-requests.show', $id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to match all ranks: ' . $e->getMessage(), [
                'exception' => $e,
                'manning_request_id' => $id,
            ]);

            return redirect()->route('hrd.manning-requests.show', $id)
                ->with('error', 'Failed to match all ranks. Please try again.');
        }
    }

    public function hrdViewDraft($id)
    {
        $manningRequest = ManningRequest::with(['command', 'items'])->findOrFail($id);

        // HRD can only view drafts for GENERAL type requests
        if ($manningRequest->type !== 'GENERAL') {
            abort(403, 'HRD can only view drafts for General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        // Get active draft deployment
        $activeDraft = ManningDeployment::draft()
            ->latest()
            ->first();

        if (!$activeDraft) {
            return redirect()->route('hrd.manning-requests.show', $id)
                ->with('error', 'No active draft deployment found.');
        }

        // Get all item IDs from this manning request
        $itemIds = $manningRequest->items->pluck('id');

        // Get assignments from this manning request only
        $filteredAssignments = $activeDraft->assignments()
            ->whereIn('manning_request_id', [$manningRequest->id])
            ->with(['officer.presentStation.zone', 'fromCommand', 'toCommand', 'manningRequestItem'])
            ->get();

        // Group filtered assignments by command (same structure as main draft page)
        $assignmentsByCommand = $filteredAssignments->groupBy('to_command_id');

        // Get manning levels summary for filtered assignments only
        $manningLevels = [];
        foreach ($filteredAssignments as $assignment) {
            $commandId = $assignment->to_command_id;
            $commandName = $assignment->toCommand->name ?? 'Unknown';
            if (!isset($manningLevels[$commandId])) {
                $manningLevels[$commandId] = [
                    'command_id' => $commandId,
                    'command_name' => $commandName,
                    'officers' => [],
                    'by_rank' => [],
                ];
            }
            $manningLevels[$commandId]['officers'][] = $assignment->officer;
            $rank = $assignment->officer->substantive_rank ?? 'Unknown';
            if (!isset($manningLevels[$commandId]['by_rank'][$rank])) {
                $manningLevels[$commandId]['by_rank'][$rank] = 0;
            }
            $manningLevels[$commandId]['by_rank'][$rank]++;
        }

        // Use the same view as draft deployment but with filtered data
        return view('dashboards.hrd.manning-deployment-draft', compact('activeDraft', 'assignmentsByCommand', 'manningLevels', 'manningRequest'));
    }

    public function hrdGenerateOrder(Request $request, $id)
    {
        \Log::info('Generate order called', ['request_id' => $id, 'input' => $request->all()]);

        $manningRequest = ManningRequest::with('items')->findOrFail($id);

        // HRD can only generate orders for GENERAL type requests
        if ($manningRequest->type !== 'GENERAL') {
            abort(403, 'HRD can only generate orders for General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        $validated = $request->validate([
            'selected_officers' => 'required|array|min:1',
            'selected_officers.*' => 'exists:officers,id',
            'item_id' => 'required|exists:manning_request_items,id',
        ]);

        try {
            $item = ManningRequestItem::findOrFail($validated['item_id']);
            $selectedOfficers = Officer::whereIn('id', $validated['selected_officers'])->get();

            // Generate unique movement order number
            $datePrefix = 'MO-' . date('Y') . '-' . date('md') . '-';
            $lastOrder = MovementOrder::where('order_number', 'LIKE', $datePrefix . '%')
                ->orderBy('order_number', 'desc')
                ->first();

            if ($lastOrder) {
                // Extract the last number from the order number
                $lastNumber = (int) substr($lastOrder->order_number, -3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            // Ensure uniqueness (in case of race condition)
            $counter = 0;
            while (MovementOrder::where('order_number', $orderNumber)->exists() && $counter < 100) {
                $newNumber++;
                $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                $counter++;
            }

            $movementOrder = MovementOrder::create([
                'order_number' => $orderNumber,
                'manning_request_id' => $id,
                'criteria_months_at_station' => null, // Not applicable for manning request-based orders
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
            ]);

            // Get destination command from manning request
            $destinationCommand = $manningRequest->command;

            // Update matched officers in request items and post officers to new command
            foreach ($selectedOfficers as $index => $officer) {
                if ($index < $item->quantity_needed) {
                    // Update the main item if first officer
                    if ($index === 0) {
                        $item->update(['matched_officer_id' => $officer->id]);
                    } else {
                        // Create additional items for other officers
                        ManningRequestItem::create([
                            'manning_request_id' => $id,
                            'rank' => $item->rank,
                            'quantity_needed' => 1,
                            'sex_requirement' => $item->sex_requirement,
                            'qualification_requirement' => $item->qualification_requirement,
                            'matched_officer_id' => $officer->id,
                        ]);
                    }

                    // Post officer to destination command
                    if ($destinationCommand) {
                        $fromCommand = $officer->presentStation;

                        // Create new posting record (pending - not yet documented)
                        OfficerPosting::create([
                            'officer_id' => $officer->id,
                            'command_id' => $destinationCommand->id,
                            'movement_order_id' => $movementOrder->id,
                            'posting_date' => now(),
                            'is_current' => false, // becomes current when Staff Officer documents arrival
                            'documented_by' => null, // Will be set when Staff Officer documents
                            'documented_at' => null, // Explicitly set to null - will be set when Staff Officer documents
                        ]);

                        // Log the posting
                        Log::info("Movement Order {$orderNumber}: Pending posting created for Officer {$officer->id} ({$officer->service_number}) from " .
                            ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$destinationCommand->name}");

                        // Notify officer about posting
                        try {
                            $notificationService = app(NotificationService::class);
                            $notificationService->notifyOfficerPosted($officer, $fromCommand, $destinationCommand, now());
                        } catch (\Exception $e) {
                            Log::warning("Failed to send posting notification: " . $e->getMessage());
                        }
                    }
                }
            }

            // Mark request as fulfilled if all items are matched
            $unmatchedItems = ManningRequestItem::where('manning_request_id', $id)
                ->whereNull('matched_officer_id')
                ->count();

            if ($unmatchedItems === 0) {
                $manningRequest->update([
                    'status' => 'FULFILLED',
                    'fulfilled_at' => now(),
                ]);

                // Refresh to load relationships
                $manningRequest->refresh();

                // Notify Staff Officer about fulfillment
                $notificationService = app(NotificationService::class);
                $notificationService->notifyManningRequestFulfilled($manningRequest);
            }

            return redirect()->route('hrd.manning-requests.show', $id)
                ->with('success', "Movement order {$orderNumber} created successfully with " . count($selectedOfficers) . " officer(s)!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to generate movement order: ' . $e->getMessage(), [
                'exception' => $e,
                'request_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to generate movement order: ' . $e->getMessage());
        }
    }

    // HRD Draft Deployment Methods
    public function hrdAddToDraft(Request $request, $id)
    {
        $manningRequest = ManningRequest::with('items')->findOrFail($id);

        // HRD can only add to draft for GENERAL type requests
        if ($manningRequest->type !== 'GENERAL') {
            abort(403, 'HRD can only add officers to draft for General Manning Level requests. Zone requests are handled by Zone Coordinator.');
        }

        $validated = $request->validate([
            'selected_officers' => 'required|array|min:1',
            'selected_officers.*' => 'exists:officers,id',
            'item_id' => 'required|exists:manning_request_items,id',
        ]);

        try {
            DB::beginTransaction();

            $item = ManningRequestItem::findOrFail($validated['item_id']);
            $selectedOfficers = Officer::whereIn('id', $validated['selected_officers'])->get();
            $destinationCommand = $manningRequest->command;

            // Get or create SHARED active draft deployment
            // IMPORTANT: All manning requests use the SAME shared draft
            // Officers from all requests are added to this single draft
            // Only create a new draft if no draft exists (e.g., after publishing)
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();

            if (!$deployment) {
                // No active draft exists - create a new shared draft
                // This will be used by ALL manning requests until it's published
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();

                $newNumber = $lastDeployment ? ((int) substr($lastDeployment->deployment_number, -3)) + 1 : 1;
                $deploymentNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $deployment = ManningDeployment::create([
                    'deployment_number' => $deploymentNumber,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                ]);
            }

            // Add officers to draft
            foreach ($selectedOfficers as $officer) {
                $fromCommand = $officer->presentStation;

                // Check if officer is already in this deployment
                $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                    ->where('officer_id', $officer->id)
                    ->first();

                if (!$existing) {
                    ManningDeploymentAssignment::create([
                        'manning_deployment_id' => $deployment->id,
                        'manning_request_id' => $manningRequest->id,
                        'manning_request_item_id' => $item->id,
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommand?->id,
                        'to_command_id' => $destinationCommand->id,
                        'rank' => $officer->substantive_rank,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('hrd.manning-deployments.draft')
                ->with('success', count($selectedOfficers) . ' officer(s) added to draft deployment.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add officers to draft: ' . $e->getMessage(), [
                'exception' => $e,
                'request_id' => $id,
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add officers to draft: ' . $e->getMessage());
        }
    }

    public function hrdDraftIndex()
    {
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Build query for deployments
        $deploymentsQuery = ManningDeployment::draft()
            ->with(['createdBy', 'assignments.officer', 'assignments.toCommand', 'assignments.fromCommand', 'assignments.manningRequest']);
        
        // Get Zone Coordinator user IDs for filtering
        $zoneCoordinatorUserIds = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Zone Coordinator');
        })->pluck('id')->toArray();
        
        // Filter for Zone Coordinators - show deployments with assignments in their zone
        // This includes both ZONE type manning requests AND movement orders
        $validationService = null;
        $zoneCommandIds = [];
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Show deployments that have assignments:
                // 1. Linked to ZONE type manning requests from their zone, OR
                // 2. With to_command_id or from_command_id in their zone (movement orders without manning request)
                $deploymentsQuery->whereHas('assignments', function($q) use ($zoneCommandIds) {
                    $q->where(function($subQ) use ($zoneCommandIds) {
                        // Assignments linked to ZONE manning requests from their zone
                        $subQ->whereHas('manningRequest', function($mrQ) use ($zoneCommandIds) {
                            $mrQ->where('type', 'ZONE')
                                ->whereIn('command_id', $zoneCommandIds);
                        })
                        // OR assignments to/from zone commands (movement orders without manning requests)
                        ->orWhere(function($movQ) use ($zoneCommandIds) {
                            $movQ->whereNull('manning_request_id')
                                 ->where(function($cmdQ) use ($zoneCommandIds) {
                                     $cmdQ->whereIn('to_command_id', $zoneCommandIds)
                                          ->orWhereIn('from_command_id', $zoneCommandIds);
                                 });
                        });
                    });
                })
                // STRICT: Exclude deployments with ANY GENERAL type assignments
                ->whereDoesntHave('assignments', function($q) {
                    $q->whereHas('manningRequest', function($mrQ) {
                        $mrQ->where('type', 'GENERAL');
                    });
                })
                // STRICT: Only show deployments created by Zone Coordinators
                // This ensures complete separation - deployments created by Zone Coordinators are only visible to Zone Coordinators
                // Use all zone coordinator IDs to ensure ONE shared draft for all Zone Coordinators
                ->whereIn('created_by', $zoneCoordinatorUserIds);
            } else {
                $deploymentsQuery->whereRaw('1 = 0'); // No results if no zone commands
            }
        } else if ($isHRD && !$isZoneCoordinator) {
            // HRD: Show ALL deployments created by HRD users (not Zone Coordinators)
            // We rely on assignment-level filtering to show only HRD assignments
            // This ensures ONE shared draft for all HRD users
            $deploymentsQuery->whereNotIn('created_by', $zoneCoordinatorUserIds);
        }
        
        $deployments = $deploymentsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active draft (most recent) - shared across all HRD officers, filtered for zone coordinators
        $activeDraft = $deployments->first();

        // Get all zone command IDs for HRD filtering (commands that belong to any zone)
        $allZoneCommandIds = [];
        if ($isHRD && !$isZoneCoordinator) {
            $allZoneCommandIds = \App\Models\Command::whereNotNull('zone_id')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }
        
        // Group assignments by command for display
        $assignmentsByCommand = [];
        if ($activeDraft) {
            $allAssignments = $activeDraft->assignments()
                ->with(['officer', 'toCommand', 'fromCommand', 'manningRequest'])
                ->get();
            
            // Filter assignments for Zone Coordinators - only show assignments in their zone
            if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD && !empty($zoneCommandIds)) {
                $allAssignments = $allAssignments->filter(function($assignment) use ($zoneCommandIds) {
                    // Include if:
                    // 1. Linked to ZONE type manning request from their zone, OR
                    // 2. To/from command is in their zone (movement orders)
                    $isFromZoneManningRequest = $assignment->manningRequest && 
                                                $assignment->manningRequest->type === 'ZONE' && 
                                                in_array($assignment->manningRequest->command_id, $zoneCommandIds);
                    $isFromZoneCommand = in_array($assignment->to_command_id, $zoneCommandIds) || 
                                        (isset($assignment->from_command_id) && in_array($assignment->from_command_id, $zoneCommandIds));
                    
                    return $isFromZoneManningRequest || $isFromZoneCommand;
                })->values();
            }
            // Filter assignments for HRD - exclude only ZONE type manning requests
            // Note: We don't exclude based on command zone because HRD can post to zone commands for GENERAL requests
            // The deployment-level filtering by created_by already ensures we only see HRD deployments
            else if ($isHRD && !$isZoneCoordinator) {
                $allAssignments = $allAssignments->filter(function($assignment) {
                    // Exclude only if linked to ZONE type manning request
                    // Since deployment is already filtered by created_by (HRD only), 
                    // assignments without manning_request_id or with GENERAL type are HRD assignments
                    if ($assignment->manningRequest) {
                        return $assignment->manningRequest->type !== 'ZONE';
                    }
                    // Include assignments without manning requests (movement orders from HRD)
                    return true;
                })->values();
            }
            
            // Group filtered assignments by command
            $assignmentsByCommand = $allAssignments->groupBy('to_command_id');
        }

        // Get manning levels summary (filtered for zone coordinators and HRD)
        $manningLevels = [];
        if ($activeDraft) {
            $allAssignmentsForLevels = $activeDraft->assignments()->with(['toCommand', 'officer', 'manningRequest'])->get();
            
            // Filter for zone coordinators if needed
            if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD && !empty($zoneCommandIds)) {
                $allAssignmentsForLevels = $allAssignmentsForLevels->filter(function($assignment) use ($zoneCommandIds) {
                    $isFromZoneManningRequest = $assignment->manningRequest && 
                                                $assignment->manningRequest->type === 'ZONE' && 
                                                in_array($assignment->manningRequest->command_id, $zoneCommandIds);
                    $isFromZoneCommand = in_array($assignment->to_command_id, $zoneCommandIds) || 
                                        (isset($assignment->from_command_id) && in_array($assignment->from_command_id, $zoneCommandIds));
                    return $isFromZoneManningRequest || $isFromZoneCommand;
                })->values();
            }
            // Filter for HRD - exclude only ZONE type manning requests
            // Note: We don't exclude based on command zone because HRD can post to zone commands for GENERAL requests
            else if ($isHRD && !$isZoneCoordinator) {
                $allAssignmentsForLevels = $allAssignmentsForLevels->filter(function($assignment) {
                    // Exclude only if linked to ZONE type manning request
                    if ($assignment->manningRequest) {
                        return $assignment->manningRequest->type !== 'ZONE';
                    }
                    // Include assignments without manning requests (movement orders from HRD)
                    return true;
                })->values();
            }
            
            foreach ($allAssignmentsForLevels as $assignment) {
                if (!$assignment->officer) {
                    continue;
                }
                $commandId = $assignment->to_command_id;
                $commandName = $assignment->toCommand->name ?? 'Unknown';
                if (!isset($manningLevels[$commandId])) {
                    $manningLevels[$commandId] = [
                        'command_id' => $commandId,
                        'command_name' => $commandName,
                        'officers' => [],
                        'by_rank' => [],
                    ];
                }
                $manningLevels[$commandId]['officers'][] = $assignment->officer;
                $rank = $assignment->officer->substantive_rank ?? 'Unknown';
                if (!isset($manningLevels[$commandId]['by_rank'][$rank])) {
                    $manningLevels[$commandId]['by_rank'][$rank] = 0;
                }
                $manningLevels[$commandId]['by_rank'][$rank]++;
            }
        }

        // Get commands for "To Command" select
        // For Zone Coordinators: only show commands in their zone
        // For HRD: show all active commands
        $commandsQuery = \App\Models\Command::where('is_active', true);
        
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD && !empty($zoneCommandIds)) {
            $commandsQuery->whereIn('id', $zoneCommandIds);
        }
        // For HRD: show all active commands (no additional filtering)
        
        $commands = $commandsQuery->orderBy('name')->get();
        
        // Determine route prefix for view
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';

        return view('dashboards.hrd.manning-deployment-draft', compact('activeDraft', 'assignmentsByCommand', 'manningLevels', 'commands', 'routePrefix'));
    }

    public function hrdDraftRemoveOfficer($deploymentId, $assignmentId)
    {
        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);

            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only remove officers from draft deployments.');
            }

            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->firstOrFail();

            $assignment->delete();

            return redirect()->back()
                ->with('success', 'Officer removed from draft deployment.');

        } catch (\Exception $e) {
            Log::error('Failed to remove officer from draft: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to remove officer: ' . $e->getMessage());
        }
    }

    public function hrdDraftSwapOfficer(Request $request, $deploymentId, $assignmentId)
    {
        $validated = $request->validate([
            'new_officer_id' => 'required|exists:officers,id',
        ]);

        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);

            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only swap officers in draft deployments.');
            }

            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->firstOrFail();

            $newOfficer = Officer::findOrFail($validated['new_officer_id']);

            // Check if new officer is already in this deployment
            $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                ->where('officer_id', $newOfficer->id)
                ->where('id', '!=', $assignmentId)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->with('error', 'New officer is already in this deployment.');
            }

            $assignment->update([
                'officer_id' => $newOfficer->id,
                'from_command_id' => $newOfficer->presentStation?->id,
                'rank' => $newOfficer->substantive_rank,
            ]);

            return redirect()->back()
                ->with('success', 'Officer swapped successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to swap officer: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to swap officer: ' . $e->getMessage());
        }
    }

    public function hrdDraftUpdateDestination($deploymentId, $assignmentId, Request $request)
    {
        // Log the request for debugging
        Log::debug('hrdDraftUpdateDestination called', [
            'deployment_id' => $deploymentId,
            'assignment_id' => $assignmentId,
            'to_command_id' => $request->to_command_id,
            'method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'user_id' => auth()->id(),
            'user_roles' => auth()->user()->roles->pluck('name')->toArray(),
        ]);

        $request->validate([
            'to_command_id' => 'required|exists:commands,id',
        ]);

        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);

            if ($deployment->status !== 'DRAFT') {
                $errorMessage = 'Can only update destination command in draft deployments.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 400);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->firstOrFail();

            // Convert to_command_id to integer for comparison
            $toCommandId = (int) $request->to_command_id;
            $fromCommandId = $assignment->from_command_id ? (int) $assignment->from_command_id : null;

            // Validate that destination is different from from_command for Command Duration assignments
            if (is_null($assignment->manning_request_id) && $toCommandId == $fromCommandId) {
                $errorMessage = 'Destination command must be different from current command.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 422);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            $assignment->update([
                'to_command_id' => $toCommandId,
            ]);

            // Return JSON response for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Destination command updated successfully.',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Destination command updated successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $errorMessage = 'Assignment or deployment not found.';
            Log::error('Failed to update destination command - not found: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 404);
            }
            return redirect()->back()->with('error', $errorMessage);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = 'Validation failed: ' . $e->getMessage();
            Log::error('Failed to update destination command - validation: ' . $errorMessage);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $errorMessage = 'Failed to update destination command: ' . $e->getMessage();
            Log::error('Failed to update destination command: ' . $e->getMessage(), [
                'exception' => $e,
                'deployment_id' => $deploymentId,
                'assignment_id' => $assignmentId,
                'to_command_id' => $request->to_command_id,
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update destination command. Please try again.',
                ], 500);
            }
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    public function hrdDraftPublish($id, Request $request)
    {
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        try {
            DB::beginTransaction();

            $deployment = ManningDeployment::with('assignments.officer', 'assignments.toCommand', 'assignments.fromCommand')
                ->findOrFail($id);

            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only publish draft deployments.');
            }

            // Filter assignments by manning request if provided
            // If not provided, publish ALL assignments (both Manning Request and Command Duration)
            $manningRequestId = $request->get('manning_request_id');
            $assignmentsToPublish = collect($deployment->assignments);

            if ($manningRequestId) {
                // Filter to only this manning request
                $assignmentsToPublish = $assignmentsToPublish->where('manning_request_id', $manningRequestId);

                if ($assignmentsToPublish->isEmpty()) {
                    return redirect()->back()
                        ->with('error', 'No assignments found for the specified manning request.');
                }
            }

            // Validate all assignments have destination commands
            $assignmentsWithoutDestination = $assignmentsToPublish->filter(function ($assignment) {
                return empty($assignment->to_command_id);
            });

            if ($assignmentsWithoutDestination->isNotEmpty()) {
                $count = $assignmentsWithoutDestination->count();
                return redirect()->back()
                    ->with('error', "Cannot publish deployment. {$count} officer(s) do not have a destination command selected. Please select destination commands for all officers before publishing.");
            }

            // For Command Duration assignments, validate destination is different from from_command
            $commandDurationAssignments = $assignmentsToPublish->filter(function ($assignment) {
                return is_null($assignment->manning_request_id);
            });

            $invalidCommandDuration = $commandDurationAssignments->filter(function ($assignment) {
                return $assignment->to_command_id == $assignment->from_command_id;
            });

            if ($invalidCommandDuration->isNotEmpty()) {
                $count = $invalidCommandDuration->count();
                return redirect()->back()
                    ->with('error', "Cannot publish deployment. {$count} officer(s) from Command Duration have the same destination as their current command. Please select different destination commands.");
            }

            // Generate unique movement order number
            $datePrefix = 'MO-' . date('Y') . '-' . date('md') . '-';
            $lastOrder = MovementOrder::where('order_number', 'LIKE', $datePrefix . '%')
                ->orderBy('order_number', 'desc')
                ->first();

            $newNumber = $lastOrder ? ((int) substr($lastOrder->order_number, -3)) + 1 : 1;
            $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            // Ensure uniqueness
            $counter = 0;
            while (MovementOrder::where('order_number', $orderNumber)->exists() && $counter < 100) {
                $newNumber++;
                $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                $counter++;
            }

            $movementOrder = MovementOrder::create([
                'order_number' => $orderNumber,
                'manning_request_id' => $manningRequestId, // Link to specific request if filtering
                'criteria_months_at_station' => null,
                'status' => 'PUBLISHED', // Mark as published immediately
                'created_by' => auth()->id(),
            ]);

            // STEP 1: Notify Staff Officers
            // - FROM commands: Notify about pending release letters
            // - TO commands: Notify about pending arrivals
            $notificationService = app(NotificationService::class);
            foreach ($assignmentsToPublish as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;

                // Notify FROM command Staff Officers about pending release letter
                if ($fromCommand) {
                    try {
                        $notificationService->notifyCommandOfficerRelease($officer, $fromCommand, $toCommand, $movementOrder);
                    } catch (\Exception $e) {
                        Log::warning("Failed to send release letter notification: " . $e->getMessage());
                    }
                }

                // Notify TO command Staff Officers about pending arrival
                if ($toCommand) {
                    try {
                        $notificationService->notifyStaffOfficerPendingArrival($officer, $fromCommand, $toCommand, $movementOrder);
                    } catch (\Exception $e) {
                        Log::warning("Failed to send pending arrival notification: " . $e->getMessage());
                    }
                }
            }

            // STEP 2: Create posting records (pending - awaiting release letter and acceptance)
            // DO NOT notify officers yet - they will be notified when release letter is printed
            foreach ($assignmentsToPublish as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;

                // Create new posting record (pending - awaiting release letter and acceptance)
                OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'movement_order_id' => $movementOrder->id,
                    'posting_date' => now(),
                    'is_current' => false, // becomes current only after acceptance
                    'documented_by' => null, // Will be set when new command accepts
                    'documented_at' => null, // Set when new command accepts
                    'release_letter_printed' => false, // Will be set when old command prints release letter
                    'release_letter_printed_at' => null,
                    'release_letter_printed_by' => null,
                    'accepted_by_new_command' => false, // Will be set when new command accepts
                    'accepted_at' => null,
                    'accepted_by' => null,
                ]);

                // Update manning request items if linked
                if ($assignment->manning_request_item_id) {
                    $item = ManningRequestItem::find($assignment->manning_request_item_id);
                    if ($item && !$item->matched_officer_id) {
                        $item->update(['matched_officer_id' => $officer->id]);
                    }
                }

                // DO NOT notify officer yet - notification happens when release letter is printed
            }

            // Update all MovementOrders with DRAFT status that are linked to this deployment
            // MovementOrders are linked through ManningDeploymentAssignment notes like "From Movement Order: MO-2026-0105-001"
            $movementOrderNumbers = [];
            foreach ($assignmentsToPublish as $assignment) {
                if ($assignment->notes && preg_match('/From Movement Order:\s*([A-Z0-9-]+)/i', $assignment->notes, $matches)) {
                    $movementOrderNumbers[] = $matches[1];
                }
            }
            
            // Also find MovementOrders with DRAFT status that have OfficerPosting records for these officers
            // (for officers that might have been added from other sources)
            $officerIds = $assignmentsToPublish->pluck('officer_id')->unique();
            
            $draftMovementOrders = collect();
            
            // Find MovementOrders by order number from assignment notes
            if (!empty($movementOrderNumbers)) {
                $ordersByNumber = MovementOrder::where('status', 'DRAFT')
                    ->whereIn('order_number', array_unique($movementOrderNumbers))
                    ->get();
                $draftMovementOrders = $draftMovementOrders->merge($ordersByNumber);
            }
            
            // Find MovementOrders by officer postings
            $ordersByPostings = MovementOrder::where('status', 'DRAFT')
                ->whereHas('postings', function($q) use ($officerIds) {
                    $q->whereIn('officer_id', $officerIds);
                })
                ->get();
            $draftMovementOrders = $draftMovementOrders->merge($ordersByPostings);
            
            // Remove duplicates
            $draftMovementOrders = $draftMovementOrders->unique('id');

            $updatedMovementOrderCount = 0;
            if ($draftMovementOrders->isNotEmpty()) {
                foreach ($draftMovementOrders as $draftOrder) {
                    $draftOrder->update([
                        'status' => 'PUBLISHED',
                    ]);
                    $updatedMovementOrderCount++;
                }
                
                Log::info('Manning Deployment - Publish: Updated existing MovementOrders to PUBLISHED', [
                    'deployment_id' => $deployment->id,
                    'updated_movement_orders_count' => $updatedMovementOrderCount,
                    'movement_order_ids' => $draftMovementOrders->pluck('id')->toArray(),
                    'movement_order_numbers' => $draftMovementOrders->pluck('order_number')->toArray(),
                    'found_by_notes' => !empty($movementOrderNumbers),
                    'found_by_postings' => $ordersByPostings->isNotEmpty(),
                    'routePrefix' => $routePrefix,
                ]);
            }

            // Check if deployment has any remaining assignments before removing published ones
            $totalAssignments = $deployment->assignments()->count();
            $assignmentsToPublishCount = $assignmentsToPublish->count();

            // Only mark deployment as published if all assignments were published
            if ($totalAssignments == $assignmentsToPublishCount) {
                // If all assignments are being published, keep them on the deployment
                // so they can be displayed and printed
                $deployment->update([
                    'status' => 'PUBLISHED',
                    'published_by' => auth()->id(),
                    'published_at' => now(),
                ]);
            } else {
                // Only remove published assignments if there are remaining assignments in draft
                $assignmentIds = $assignmentsToPublish->pluck('id');
                ManningDeploymentAssignment::whereIn('id', $assignmentIds)->delete();
            }

            // Check and update manning request statuses
            $requestIds = $assignmentsToPublish
                ->whereNotNull('manning_request_id')
                ->pluck('manning_request_id')
                ->unique();

            foreach ($requestIds as $requestId) {
                $manningRequest = ManningRequest::find($requestId);
                if ($manningRequest) {
                    $unmatchedItems = ManningRequestItem::where('manning_request_id', $requestId)
                        ->whereNull('matched_officer_id')
                        ->count();

                    if ($unmatchedItems === 0) {
                        $manningRequest->update([
                            'status' => 'FULFILLED',
                            'fulfilled_at' => now(),
                        ]);

                        $notificationService = app(NotificationService::class);
                        $notificationService->notifyManningRequestFulfilled($manningRequest);
                    }
                }
            }

            DB::commit();

            // Build success message
            $successMessage = "Movement Order {$orderNumber} published successfully! ";
            $manningRequestCount = $assignmentsToPublish->whereNotNull('manning_request_id')->count();
            $commandDurationCount = $assignmentsToPublish->whereNull('manning_request_id')->count();
            
            // Add message about updated MovementOrders if any
            if ($updatedMovementOrderCount > 0) {
                $successMessage .= " {$updatedMovementOrderCount} existing MovementOrder(s) with DRAFT status updated to PUBLISHED. ";
            }
            
            if ($manningRequestId) {
                $manningRequest = ManningRequest::find($manningRequestId);
                $successMessage .= "Published {$assignmentsToPublishCount} officer(s) from Manning Request #{$manningRequestId}";
                if ($totalAssignments == $assignmentsToPublishCount) {
                    $successMessage .= ". Deployment {$deployment->deployment_number} is now fully published.";
                } else {
                    $remainingCount = $totalAssignments - $assignmentsToPublishCount;
                    $successMessage .= ". {$remainingCount} officer(s) remain in draft.";
                }
            } else {
                $parts = [];
                if ($manningRequestCount > 0) {
                    $parts[] = "{$manningRequestCount} from Manning Requests";
                }
                if ($commandDurationCount > 0) {
                    $parts[] = "{$commandDurationCount} from Command Duration";
                }
                $successMessage .= "Published {$assignmentsToPublishCount} officer(s)";
                if (!empty($parts)) {
                    $successMessage .= " (" . implode(', ', $parts) . ")";
                }
                if ($totalAssignments == $assignmentsToPublishCount) {
                    $successMessage .= ". Deployment {$deployment->deployment_number} is now fully published.";
                } else {
                    $remainingCount = $totalAssignments - $assignmentsToPublishCount;
                    $successMessage .= ". {$remainingCount} officer(s) remain in draft.";
                }
            }
            
            $successMessage .= " Staff Officers have been notified about pending release letters and arrivals.";

            // Always redirect to Published Deployments page after publishing (using route prefix)
            return redirect()->route($routePrefix . '.manning-deployments.published')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish deployment: ' . $e->getMessage(), [
                'exception' => $e,
                'deployment_id' => $id,
            ]);
            return redirect()->back()
                ->with('error', 'Failed to publish deployment: ' . $e->getMessage());
        }
    }

    public function hrdDraftPrint($id, Request $request)
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        $fullUrl = request()->fullUrl();
        
        // Check if this is a Zone Coordinator route
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0) ||
                                   (strpos($fullUrl, '/zone-coordinator/') !== false);
        
        // If user is Zone Coordinator and not HRD, default to zone-coordinator route
        if ($isZoneCoordinator && !$isHRD) {
            $isZoneCoordinatorRoute = true;
        }
        
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        Log::info('Print: Method called', [
            'user_id' => $user->id,
            'deployment_id' => $id,
            'path' => $path,
            'route_name' => $routeName,
            'full_url' => $fullUrl,
            'isZoneCoordinator' => $isZoneCoordinator,
            'isHRD' => $isHRD,
            'isZoneCoordinatorRoute' => $isZoneCoordinatorRoute,
            'routePrefix' => $routePrefix,
        ]);
        
        $deployment = ManningDeployment::with([
            'assignments.officer',
            'assignments.toCommand',
            'assignments.fromCommand'
        ])->findOrFail($id);

        // Authorization check - match the same logic as published index
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            // Zone Coordinator: Verify deployment has assignments in their zone (same logic as published index)
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (empty($zoneCommandIds)) {
                Log::warning('Print - Zone Coordinator: No zone commands found', ['user_id' => $user->id]);
                abort(403, 'You do not have access to this deployment.');
            }
            
            // Check if deployment has assignments in their zone (matching published index logic)
            $hasZoneAssignments = $deployment->assignments()
                ->where(function($q) use ($zoneCommandIds) {
                    // Assignments linked to ZONE manning requests from their zone
                    $q->whereHas('manningRequest', function($mrQ) use ($zoneCommandIds) {
                        $mrQ->where('type', 'ZONE')
                            ->whereIn('command_id', $zoneCommandIds);
                    })
                    // OR assignments to/from zone commands (movement orders without manning requests)
                    ->orWhere(function($movQ) use ($zoneCommandIds) {
                        $movQ->whereNull('manning_request_id')
                             ->where(function($cmdQ) use ($zoneCommandIds) {
                                 $cmdQ->whereIn('to_command_id', $zoneCommandIds)
                                      ->orWhereIn('from_command_id', $zoneCommandIds);
                             });
                    });
                })
                ->exists();
            
            // Check if deployment has ANY GENERAL type assignments (should be excluded)
            $hasGeneralAssignments = $deployment->assignments()
                ->whereHas('manningRequest', function($mrQ) {
                    $mrQ->where('type', 'GENERAL');
                })
                ->exists();
            
            Log::info('Print - Zone Coordinator: Authorization check', [
                'user_id' => $user->id,
                'deployment_id' => $deployment->id,
                'zone_command_ids' => $zoneCommandIds,
                'has_zone_assignments' => $hasZoneAssignments,
                'has_general_assignments' => $hasGeneralAssignments,
            ]);
            
            if (!$hasZoneAssignments || $hasGeneralAssignments) {
                abort(403, 'You do not have access to this deployment.');
            }
        } else {
            // HRD: Verify deployment was created by them
            if ($deployment->created_by != $user->id) {
                Log::warning('Print - HRD: Deployment not created by user', [
                    'user_id' => $user->id,
                    'deployment_id' => $deployment->id,
                    'deployment_created_by' => $deployment->created_by,
                ]);
                abort(403, 'You do not have access to this deployment. You can only print deployments you created.');
            }
        }
        
        Log::info('Print: Access granted', [
            'user_id' => $user->id,
            'deployment_id' => $deployment->id,
            'isZoneCoordinatorRoute' => $isZoneCoordinatorRoute,
            'routePrefix' => $routePrefix,
        ]);

        // Get assignments query
        $assignmentsQuery = $deployment->assignments()
            ->with(['officer', 'toCommand', 'fromCommand', 'manningRequestItem']);

        // For Zone Coordinators, filter assignments to only show those in their zone
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                $assignmentsQuery->where(function($q) use ($zoneCommandIds) {
                    $q->whereHas('manningRequest', function($mrQ) use ($zoneCommandIds) {
                        $mrQ->where('type', 'ZONE')
                            ->whereIn('command_id', $zoneCommandIds);
                    })
                    ->orWhere(function($movQ) use ($zoneCommandIds) {
                        $movQ->whereNull('manning_request_id')
                             ->where(function($cmdQ) use ($zoneCommandIds) {
                                 $cmdQ->whereIn('to_command_id', $zoneCommandIds)
                                      ->orWhereIn('from_command_id', $zoneCommandIds);
                             });
                    });
                });
            }
        }

        // Filter by manning request if provided
        $manningRequestId = $request->get('manning_request_id');
        $manningRequest = null;
        if ($manningRequestId) {
            $manningRequest = ManningRequest::with('command')->find($manningRequestId);
            if ($manningRequest) {
                // Filter assignments that belong to this manning request
                $assignmentsQuery->where('manning_request_id', $manningRequestId);
            }
        }

        // Get all assignments sorted by rank
        $assignments = $assignmentsQuery->get()
            ->sortBy(function ($assignment) {
                // Sort by rank (you may need to adjust this based on your rank hierarchy)
                $rankOrder = [
                    'CGC' => 1,
                    'DCG' => 2,
                    'ACG' => 3,
                    'CC' => 4,
                    'DC' => 5,
                    'AC' => 6,
                    'CSC' => 7,
                    'SC' => 8,
                    'DSC' => 9,
                    'ASC I' => 10,
                    'ASC II' => 11,
                    'IC' => 12,
                    'AIC' => 13,
                    'CA I' => 14,
                    'CA II' => 15,
                    'CA III' => 16,
                ];
                $rank = $assignment->officer->substantive_rank ?? '';
                return $rankOrder[$rank] ?? 999;
            })
            ->values();

        return view('dashboards.hrd.manning-deployment-print', compact('deployment', 'assignments', 'manningRequest'));
    }

    public function hrdPublishedIndex()
    {
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Build query for published deployments
        $deploymentsQuery = ManningDeployment::published()
            ->with(['createdBy', 'publishedBy', 'assignments.officer', 'assignments.toCommand']);
        
        // Filter for Zone Coordinators - show deployments with assignments in their zone
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Show deployments that have assignments:
                // 1. Linked to ZONE type manning requests from their zone, OR
                // 2. With to_command_id or from_command_id in their zone (movement orders without manning request)
                $deploymentsQuery->whereHas('assignments', function($q) use ($zoneCommandIds) {
                    $q->where(function($subQ) use ($zoneCommandIds) {
                        // Assignments linked to ZONE manning requests from their zone
                        $subQ->whereHas('manningRequest', function($mrQ) use ($zoneCommandIds) {
                            $mrQ->where('type', 'ZONE')
                                ->whereIn('command_id', $zoneCommandIds);
                        })
                        // OR assignments to/from zone commands (movement orders without manning requests)
                        ->orWhere(function($movQ) use ($zoneCommandIds) {
                            $movQ->whereNull('manning_request_id')
                                 ->where(function($cmdQ) use ($zoneCommandIds) {
                                     $cmdQ->whereIn('to_command_id', $zoneCommandIds)
                                          ->orWhereIn('from_command_id', $zoneCommandIds);
                                 });
                        });
                    });
                })
                // STRICT: Exclude deployments with ANY GENERAL type assignments
                ->whereDoesntHave('assignments', function($q) {
                    $q->whereHas('manningRequest', function($mrQ) {
                        $mrQ->where('type', 'GENERAL');
                    });
                });
                
                Log::info('Published Deployments - Zone Coordinator: Filtering by zone commands', [
                    'user_id' => $user->id,
                    'zone_command_ids' => $zoneCommandIds,
                ]);
            } else {
                // No zone commands - return empty result
                $deploymentsQuery->whereRaw('1 = 0'); // Return no results
            }
        } else {
            // HRD: Only show deployments created by the current HRD user ("his own")
            // This ensures HRD users only see their own published deployments
            $deploymentsQuery->where('created_by', $user->id);
            
            Log::info('Published Deployments - HRD: Filtering by current user deployments only', [
                'user_id' => $user->id,
                'created_by_filter' => $user->id,
            ]);
        }
        
        $deployments = $deploymentsQuery
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';

        return view('dashboards.hrd.manning-deployments-published', compact('deployments', 'routePrefix'));
    }

    // Area Controller Methods
    public function areaControllerIndex(Request $request)
    {
        // Get submitted manning requests (status = SUBMITTED) for Area Controller approval
        // Area Controller oversees multiple units - no command restrictions
        $query = ManningRequest::with(['command.zone', 'requestedBy', 'items'])
            ->where('status', 'SUBMITTED');

        // Order by submitted_at if available, otherwise by created_at
        $query->orderByRaw('COALESCE(submitted_at, created_at) DESC');

        $requests = $query->paginate(20)->withQueryString();

        // Debug: Log the count for troubleshooting
        \Log::info('Area Controller Manning Requests', [
            'total_count' => $requests->total(),
            'current_count' => $requests->count(),
            'status_filter' => 'SUBMITTED'
        ]);

        return view('dashboards.area-controller.manning-level', compact('requests'));
    }

    public function areaControllerShow($id)
    {
        $request = ManningRequest::with(['command.zone', 'requestedBy', 'items'])->findOrFail($id);

        // Only show SUBMITTED requests
        if ($request->status !== 'SUBMITTED') {
            abort(403, 'This request is not pending approval');
        }

        return view('dashboards.area-controller.manning-level-show', compact('request'));
    }

    public function areaControllerApprove(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can approve manning requests');
        }

        $manningRequest = ManningRequest::findOrFail($id);

        // Only allow approving SUBMITTED requests
        if ($manningRequest->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED requests can be approved.');
        }

        try {
            // Get Area Controller's officer record for approved_by
            $officer = $user->officer;

            $manningRequest->status = 'APPROVED';
            $manningRequest->approved_at = now();
            if ($officer) {
                $manningRequest->approved_by = $officer->id;
            }
            $manningRequest->save();

            // Refresh to load relationships
            $manningRequest->refresh();

            // Notify Staff Officer about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestApproved($manningRequest);
            
            // Notify appropriate roles based on request type
            if ($manningRequest->type === 'GENERAL') {
                // GENERAL type: Notify HRD for officer matching
                $notificationService->notifyManningRequestApprovedToHrd($manningRequest);
            } elseif ($manningRequest->type === 'ZONE') {
                // ZONE type: Notify Zone Coordinators for processing via Movement Orders
                $notificationService->notifyManningRequestApprovedToZoneCoordinators($manningRequest);
            }

            return redirect()->route('area-controller.manning-level')
                ->with('success', 'Manning request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function areaControllerReject(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can reject manning requests');
        }

        $manningRequest = ManningRequest::findOrFail($id);

        // Only allow rejecting SUBMITTED requests
        if ($manningRequest->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED requests can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $manningRequest->status = 'REJECTED';
            $manningRequest->rejection_reason = $request->rejection_reason;
            $manningRequest->save();

            // Notify Staff Officer about rejection
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestRejected($manningRequest, $request->rejection_reason);

            return redirect()->route('area-controller.manning-level')
                ->with('success', 'Manning request rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject request: ' . $e->getMessage());
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

        // Get submitted manning requests (status = SUBMITTED) for DC Admin's command
        $query = ManningRequest::with(['command.zone', 'requestedBy', 'items'])
            ->where('status', 'SUBMITTED');

        // Filter by command if DC Admin is assigned to a command
        if ($commandId) {
            $query->where('command_id', $commandId);
        }

        // Order by submitted_at if available, otherwise by created_at
        $query->orderByRaw('COALESCE(submitted_at, created_at) DESC');

        $requests = $query->paginate(20)->withQueryString();

        return view('dashboards.dc-admin.manning-level', compact('requests', 'commandId'));
    }

    public function dcAdminShow($id)
    {
        $user = auth()->user();

        // Get DC Admin's command
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $dcAdminRole?->pivot->command_id ?? null;

        $request = ManningRequest::with(['command.zone', 'requestedBy', 'items'])->findOrFail($id);

        // Only show SUBMITTED requests
        if ($request->status !== 'SUBMITTED') {
            abort(403, 'This request is not pending approval');
        }

        // Check command access if DC Admin is assigned to a command
        if ($commandId && $request->command_id != $commandId) {
            abort(403, 'You can only approve requests for your assigned command');
        }

        return view('dashboards.dc-admin.manning-level-show', compact('request'));
    }

    public function dcAdminApprove(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can approve manning requests');
        }

        // Get DC Admin's command
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $dcAdminRole?->pivot->command_id ?? null;

        $manningRequest = ManningRequest::findOrFail($id);

        // Check command access if DC Admin is assigned to a command
        if ($commandId && $manningRequest->command_id != $commandId) {
            abort(403, 'You can only approve requests for your assigned command');
        }

        // Only allow approving SUBMITTED requests
        if ($manningRequest->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED requests can be approved.');
        }

        try {
            // Get DC Admin's officer record for approved_by
            $officer = $user->officer;

            $manningRequest->status = 'APPROVED';
            $manningRequest->approved_at = now();
            if ($officer) {
                $manningRequest->approved_by = $officer->id;
            }
            $manningRequest->save();

            // Refresh to load relationships
            $manningRequest->refresh();

            // Notify Staff Officer about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestApproved($manningRequest);
            
            // Notify appropriate roles based on request type
            if ($manningRequest->type === 'GENERAL') {
                // GENERAL type: Notify HRD for officer matching
                $notificationService->notifyManningRequestApprovedToHrd($manningRequest);
            } elseif ($manningRequest->type === 'ZONE') {
                // ZONE type: Notify Zone Coordinators for processing via Movement Orders
                $notificationService->notifyManningRequestApprovedToZoneCoordinators($manningRequest);
            }

            return redirect()->route('dc-admin.manning-level')
                ->with('success', 'Manning request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function dcAdminReject(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can reject manning requests');
        }

        // Get DC Admin's command
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $dcAdminRole?->pivot->command_id ?? null;

        $manningRequest = ManningRequest::findOrFail($id);

        // Check command access if DC Admin is assigned to a command
        if ($commandId && $manningRequest->command_id != $commandId) {
            abort(403, 'You can only reject requests for your assigned command');
        }

        // Only allow rejecting SUBMITTED requests
        if ($manningRequest->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED requests can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $manningRequest->status = 'REJECTED';
            $manningRequest->rejection_reason = $request->rejection_reason;
            $manningRequest->save();

            // Notify Staff Officer about rejection
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestRejected($manningRequest, $request->rejection_reason);

            return redirect()->route('dc-admin.manning-level')
                ->with('success', 'Manning request rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject manning request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }
}



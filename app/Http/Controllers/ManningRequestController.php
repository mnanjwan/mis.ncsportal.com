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
use App\Services\NotificationService;
use App\Services\PostingWorkflowService;
use App\Services\RankComparisonService;

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
            'hrdMatch', 
            'hrdMatchAll',
            'hrdViewDraft', 
            'hrdGenerateOrder',
            'hrdDraftIndex',
            'hrdDraftAddOfficer',
            'hrdDraftRemoveOfficer',
            'hrdDraftSwapOfficer',
            'hrdDraftPublish',
            'hrdDraftPrint',
            'hrdPublishedIndex',
            'hrdAddToDraft',
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
        
        $requests = $query->paginate(20)->withQueryString();
        
        // Calculate approved officers count by rank across all approved requests
        // This shows how many officers HRD has matched for each rank vs how many were requested
        // Note: When HRD matches multiple officers for one item, they create additional items
        // So we count items with matched_officer_id for approved count
        $allItems = ManningRequestItem::whereHas('manningRequest', function($q) use ($commandId) {
                $q->where('command_id', $commandId)
                  ->where('status', 'APPROVED');
            })
            ->get();
        
        // Group by rank and calculate requested vs approved
        $approvedOfficersByRank = $allItems->groupBy('rank')->map(function($items, $rank) {
            $requested = $items->sum('quantity_needed');
            $approved = $items->whereNotNull('matched_officer_id')->count(); // Count matched officers
            return (object)[
                'rank' => $rank,
                'requested_count' => $requested,
                'approved_count' => $approved
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
            ->flatMap(function($officer) {
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
        $validItems = array_filter($request->items, function($item) {
            return !empty($item['rank']) && !empty($item['quantity_needed']);
        });
        
        if (count($validItems) === 0) {
            return redirect()->back()
                ->with('error', 'Please add at least one request item with rank and quantity.')
                ->withInput();
        }
        
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.rank' => 'required|string|max:100',
            'items.*.quantity_needed' => 'required|integer|min:1',
            'items.*.sex_requirement' => 'nullable|in:ANY,M,F',
            'items.*.qualification_requirement' => 'nullable|string|max:255',
            'items.*.qualification_custom' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create manning request (status: DRAFT, will be SUBMITTED when sent to Area Controller)
            $manningRequest = ManningRequest::create([
                'command_id' => $commandId,
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
                    'quantity_needed' => (int)$item['quantity_needed'],
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
        $approvedCountsByRank = $request->items->groupBy('rank')->map(function($items, $rank) {
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
        $query = ManningRequest::with(['command.zone', 'requestedBy', 'approvedBy', 'items'])
            ->where('status', 'APPROVED');

        // EXCLUDE requests that have items already in draft deployments
        // Once items are in draft, they leave the dashboard until draft is published
        $itemIdsInDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->whereNotNull('manning_request_item_id')
            ->pluck('manning_request_item_id')
            ->unique();
        
        if ($itemIdsInDraft->isNotEmpty()) {
            // Get request IDs that have items in draft
            $requestIdsInDraft = ManningRequestItem::whereIn('id', $itemIdsInDraft)
                ->pluck('manning_request_id')
                ->unique();
            
            // Exclude those requests from the dashboard
            $query->whereNotIn('id', $requestIdsInDraft);
        }

        // Sorting - Default to latest requests first (created_at desc)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'command' => function($query, $order) {
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
        
        return view('dashboards.hrd.manning-requests', compact('requests'));
    }

    public function hrdShow($id)
    {
        $request = ManningRequest::with(['command', 'requestedBy', 'approvedBy', 'items'])
            ->findOrFail($id);
        
        // Check which items have officers in draft deployments
        $itemIds = $request->items->pluck('id');
        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->pluck('manning_request_item_id')
            ->unique();
        
        return view('dashboards.hrd.manning-request-show', compact('request', 'itemsInDraft'));
    }

    public function hrdMatch(Request $request, $id)
    {
        $manningRequest = ManningRequest::with('items')->findOrFail($id);
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
            $query->where(function($q) use ($ranksToMatch, $requestedRank) {
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
        $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) {
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
            $matchedOfficers = $matchedOfficers->map(function($officer) use ($qualification) {
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
        $uniqueCommandsInResults = $matchedOfficers->pluck('presentStation')->unique('id')->map(function($cmd) {
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
            
            // Get or create shared active draft deployment (not per-user)
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();
            
            if (!$deployment) {
                // Generate deployment number
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();
                
                $newNumber = $lastDeployment ? ((int)substr($lastDeployment->deployment_number, -3)) + 1 : 1;
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
        
        // Get all pending items (not published, not in draft)
        $itemIds = $manningRequest->items->pluck('id');
        $itemsInDraft = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function($q) {
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
            
            // Get or create shared active draft deployment
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();
            
            if (!$deployment) {
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();
                
                $newNumber = $lastDeployment ? ((int)substr($lastDeployment->deployment_number, -3)) + 1 : 1;
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
                    
                    $query->where(function($q) use ($ranksToMatch, $requestedRank) {
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
                $officersInDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) {
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
        
        // Get active draft deployment
        $activeDraft = ManningDeployment::draft()
            ->latest()
            ->first();
        
        // Get all items from this request that are in draft deployments
        $itemIds = $manningRequest->items->pluck('id');
        $assignments = ManningDeploymentAssignment::whereIn('manning_request_item_id', $itemIds)
            ->whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->with([
                'officer.presentStation.zone',
                'fromCommand',
                'toCommand',
                'manningRequestItem',
                'deployment'
            ])
            ->get();
        
        // Group assignments by item/rank
        $assignmentsByItem = $assignments->groupBy('manning_request_item_id');
        
        return view('dashboards.hrd.manning-request-draft', compact('manningRequest', 'assignments', 'assignmentsByItem', 'activeDraft'));
    }

    public function hrdGenerateOrder(Request $request, $id)
    {
        \Log::info('Generate order called', ['request_id' => $id, 'input' => $request->all()]);
        
        $manningRequest = ManningRequest::with('items')->findOrFail($id);
        
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
                $lastNumber = (int)substr($lastOrder->order_number, -3);
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
                        
                        // Mark previous posting as not current
                        OfficerPosting::where('officer_id', $officer->id)
                            ->where('is_current', true)
                            ->update(['is_current' => false]);
                        
                        // Create new posting record (not yet documented)
                        OfficerPosting::create([
                            'officer_id' => $officer->id,
                            'command_id' => $destinationCommand->id,
                            'movement_order_id' => $movementOrder->id,
                            'posting_date' => now(),
                            'is_current' => true,
                            'documented_by' => null, // Will be set when Staff Officer documents
                            'documented_at' => null, // Explicitly set to null - will be set when Staff Officer documents
                        ]);
                        
                        // Update officer's present_station (this automatically updates nominal roll)
                        $officer->update([
                            'present_station' => $destinationCommand->id,
                            'date_posted_to_station' => now(),
                        ]);
                        
                        // Log the posting
                        Log::info("Movement Order {$orderNumber}: Officer {$officer->id} ({$officer->service_number}) posted from " . 
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
            
            // Get or create shared active draft deployment (not per-user)
            // All HRD officers work with the same shared draft
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();
            
            if (!$deployment) {
                // Generate deployment number
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();
                
                $newNumber = $lastDeployment ? ((int)substr($lastDeployment->deployment_number, -3)) + 1 : 1;
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
        // Get shared active draft (not per-user - all HRD officers see the same draft)
        $deployments = ManningDeployment::draft()
            ->with(['createdBy', 'assignments.officer', 'assignments.toCommand', 'assignments.fromCommand'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get active draft (most recent) - shared across all HRD officers
        $activeDraft = $deployments->first();
        
        // Group assignments by command for display
        $assignmentsByCommand = [];
        if ($activeDraft) {
            $assignmentsByCommand = $activeDraft->getOfficersByCommand();
        }
        
        // Get manning levels summary
        $manningLevels = $activeDraft ? $activeDraft->getManningLevels() : [];
        
        return view('dashboards.hrd.manning-deployment-draft', compact('activeDraft', 'assignmentsByCommand', 'manningLevels'));
    }

    public function hrdDraftAddOfficer(Request $request)
    {
        $validated = $request->validate([
            'deployment_id' => 'required|exists:manning_deployments,id',
            'officer_id' => 'required|exists:officers,id',
            'to_command_id' => 'required|exists:commands,id',
            'manning_request_id' => 'nullable|exists:manning_requests,id',
            'manning_request_item_id' => 'nullable|exists:manning_request_items,id',
        ]);
        
        try {
            $deployment = ManningDeployment::findOrFail($validated['deployment_id']);
            
            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only add officers to draft deployments.');
            }
            
            $officer = Officer::where('is_active', true)
                ->where('is_deceased', false)
                ->where('interdicted', false)
                ->where('suspended', false)
                ->where('dismissed', false)
                ->findOrFail($validated['officer_id']);
            
            $toCommand = Command::findOrFail($validated['to_command_id']);
            $fromCommand = $officer->presentStation;
            
            // Check if officer is already in this deployment
            $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                ->where('officer_id', $officer->id)
                ->first();
            
            if ($existing) {
                return redirect()->back()
                    ->with('error', 'Officer is already in this deployment.');
            }
            
            // Check if officer is already in any draft deployment
            $inOtherDraft = ManningDeploymentAssignment::whereHas('deployment', function($q) use ($deployment) {
                    $q->where('status', 'DRAFT')
                      ->where('id', '!=', $deployment->id);
                })
                ->where('officer_id', $officer->id)
                ->exists();
            
            if ($inOtherDraft) {
                return redirect()->back()
                    ->with('error', 'Officer is already assigned to another draft deployment.');
            }
            
            ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $validated['manning_request_id'] ?? null,
                'manning_request_item_id' => $validated['manning_request_item_id'] ?? null,
                'officer_id' => $officer->id,
                'from_command_id' => $fromCommand?->id,
                'to_command_id' => $toCommand->id,
                'rank' => $officer->substantive_rank,
            ]);
            
            return redirect()->back()
                ->with('success', 'Officer added to draft deployment.');
                
        } catch (\Exception $e) {
            Log::error('Failed to add officer to draft: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to add officer: ' . $e->getMessage());
        }
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

    public function hrdDraftPublish($id)
    {
        try {
            DB::beginTransaction();
            
            $deployment = ManningDeployment::with('assignments.officer', 'assignments.toCommand', 'assignments.fromCommand')
                ->findOrFail($id);
            
            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only publish draft deployments.');
            }
            
            // Generate unique movement order number
            $datePrefix = 'MO-' . date('Y') . '-' . date('md') . '-';
            $lastOrder = MovementOrder::where('order_number', 'LIKE', $datePrefix . '%')
                ->orderBy('order_number', 'desc')
                ->first();
            
            $newNumber = $lastOrder ? ((int)substr($lastOrder->order_number, -3)) + 1 : 1;
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
                'manning_request_id' => null, // Deployment may span multiple requests
                'criteria_months_at_station' => null,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
            ]);
            
            // STEP 1: Send release letters to FROM commands BEFORE posting
            // Release letters notify the command that officer is being released
            // This must happen BEFORE documentation/posting
            $notificationService = app(NotificationService::class);
            foreach ($deployment->assignments as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;
                
                if ($fromCommand) {
                    // Notify FROM command about officer release
                    // This is authorized by Area Comptroller or DC Admin through Staff Officer
                    try {
                        $notificationService->notifyCommandOfficerRelease($officer, $fromCommand, $toCommand, $movementOrder);
                    } catch (\Exception $e) {
                        Log::warning("Failed to send release letter notification: " . $e->getMessage());
                    }
                }
            }
            
            // STEP 2: Post all officers and create movement order entries
            foreach ($deployment->assignments as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;
                
                // Mark previous posting as not current
                OfficerPosting::where('officer_id', $officer->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
                
                // Create new posting record (not yet documented - documentation happens after release letter)
                OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'movement_order_id' => $movementOrder->id,
                    'posting_date' => now(),
                    'is_current' => true,
                    'documented_by' => null, // Will be set when Staff Officer documents after release letter
                    'documented_at' => null, // Documentation happens after release letter is processed
                ]);
                
                // Update officer's present_station
                $officer->update([
                    'present_station' => $toCommand->id,
                    'date_posted_to_station' => now(),
                ]);
                
                // Update manning request items if linked
                if ($assignment->manning_request_item_id) {
                    $item = ManningRequestItem::find($assignment->manning_request_item_id);
                    if ($item && !$item->matched_officer_id) {
                        $item->update(['matched_officer_id' => $officer->id]);
                    }
                }
                
                // Notify officer about posting (after release letter)
                try {
                    $notificationService->notifyOfficerPosted($officer, $fromCommand, $toCommand, now());
                } catch (\Exception $e) {
                    Log::warning("Failed to send posting notification: " . $e->getMessage());
                }
            }
            
            // Mark deployment as published
            $deployment->update([
                'status' => 'PUBLISHED',
                'published_by' => auth()->id(),
                'published_at' => now(),
            ]);
            
            // Check and update manning request statuses
            $requestIds = $deployment->assignments()
                ->whereNotNull('manning_request_id')
                ->distinct()
                ->pluck('manning_request_id');
            
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
            
            return redirect()->route('hrd.manning-deployments.published')
                ->with('success', "Deployment {$deployment->deployment_number} published successfully! Movement Order {$orderNumber} created.");
                
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

    public function hrdDraftPrint($id)
    {
        $deployment = ManningDeployment::with([
            'assignments.officer',
            'assignments.toCommand',
            'assignments.fromCommand'
        ])->findOrFail($id);
        
        // Get all assignments sorted by rank
        $assignments = $deployment->assignments()
            ->with(['officer', 'toCommand', 'fromCommand'])
            ->get()
            ->sortBy(function($assignment) {
                // Sort by rank (you may need to adjust this based on your rank hierarchy)
                $rankOrder = [
                    'CGC' => 1, 'DCG' => 2, 'ACG' => 3, 'CC' => 4, 'DC' => 5,
                    'AC' => 6, 'CSC' => 7, 'SC' => 8, 'DSC' => 9,
                    'ASC I' => 10, 'ASC II' => 11, 'IC' => 12, 'AIC' => 13,
                    'CA I' => 14, 'CA II' => 15, 'CA III' => 16,
                ];
                $rank = $assignment->officer->substantive_rank ?? '';
                return $rankOrder[$rank] ?? 999;
            })
            ->values();
        
        return view('dashboards.hrd.manning-deployment-print', compact('deployment', 'assignments'));
    }

    public function hrdPublishedIndex()
    {
        $deployments = ManningDeployment::published()
            ->with(['createdBy', 'publishedBy', 'assignments.officer', 'assignments.toCommand'])
            ->orderBy('published_at', 'desc')
            ->paginate(20);
        
        return view('dashboards.hrd.manning-deployments-published', compact('deployments'));
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
            
            // Notify Staff Officer and HRD about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestApproved($manningRequest);
            $notificationService->notifyManningRequestApprovedToHrd($manningRequest);
            
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
            
            // Notify Staff Officer and HRD about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestApproved($manningRequest);
            $notificationService->notifyManningRequestApprovedToHrd($manningRequest);
            
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



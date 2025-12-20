<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\Officer;
use App\Models\MovementOrder;
use App\Services\NotificationService;

class ManningRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Staff Officer')->except(['index', 'show', 'hrdIndex', 'hrdShow', 'hrdMatch', 'hrdGenerateOrder']);
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
            ->with(['requestedBy', 'approvedBy', 'items'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $requests = $query->paginate(20)->withQueryString();
        
        return view('dashboards.staff-officer.manning-level', compact('requests', 'command'));
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
        
        // Get all unique ranks from officers
        $ranks = Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->orderBy('substantive_rank')
            ->pluck('substantive_rank')
            ->filter()
            ->values()
            ->toArray();
        
        // If no ranks in database, use common ranks
        if (empty($ranks)) {
            $ranks = [
                'Assistant Superintendent',
                'Deputy Superintendent',
                'Superintendent',
                'Chief Superintendent',
                'Assistant Comptroller',
                'Deputy Comptroller',
                'Comptroller',
                'Assistant Comptroller General',
                'Deputy Comptroller General',
            ];
        }
        
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
        
        // Get the manning request
        $request = ManningRequest::with(['command', 'requestedBy', 'approvedBy', 'items'])->findOrFail($id);
        
        // Verify access - only show requests from Staff Officer's command
        if (!$commandId || $request->command_id != $commandId) {
            abort(403, 'You can only view manning requests for your assigned command');
        }
        
        return view('dashboards.staff-officer.manning-level-show', compact('request'));
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
        
        // Get ranks and qualifications for dropdowns
        $ranks = [
            'Assistant Superintendent',
            'Deputy Superintendent',
            'Superintendent',
            'Chief Superintendent',
            'Assistant Comptroller',
            'Deputy Comptroller',
            'Comptroller',
            'Assistant Comptroller General',
            'Deputy Comptroller General',
            'Comptroller General',
        ];
        
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

        // Sorting
        $sortBy = $request->get('sort_by', 'approved_at');
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

        $column = $sortableColumns[$sortBy] ?? 'approved_at';
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
        
        return view('dashboards.hrd.manning-request-show', compact('request'));
    }

    public function hrdMatch(Request $request, $id)
    {
        $manningRequest = ManningRequest::with('items')->findOrFail($id);
        $itemId = $request->input('item_id');
        
        $item = ManningRequestItem::findOrFail($itemId);
        
        // Build query for matching officers
        $query = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->where('interdicted', false)  // Fixed: was 'is_interdicted'
            ->where('suspended', false)    // Fixed: was 'is_suspended'
            ->where('dismissed', false)    // Added: exclude dismissed officers
            ->whereNotNull('substantive_rank');
        
        // Match rank - handle both full names and abbreviations
        if (!empty($item->rank)) {
            // Map full rank names to common abbreviations
            $rankMapping = [
                'Assistant Superintendent' => ['ASC I', 'ASC II', 'ASC', 'Assistant Superintendent'],
                'Deputy Superintendent' => ['DSC', 'Deputy Superintendent'],
                'Superintendent' => ['SC', 'Superintendent'],
                'Chief Superintendent' => ['CSC', 'Chief Superintendent'],
                'Assistant Comptroller' => ['AC', 'Assistant Comptroller'],
                'Deputy Comptroller' => ['DC', 'Deputy Comptroller'],
                'Comptroller' => ['CC', 'Comptroller'],
                'Assistant Comptroller General' => ['ACG', 'Assistant Comptroller General'],
                'Deputy Comptroller General' => ['DCG', 'Deputy Comptroller General'],
            ];
            
            // Start with exact match
            $matchingRanks = [$item->rank];
            
            // Add mapped ranks if found
            foreach ($rankMapping as $fullName => $abbreviations) {
                // Check if item rank contains or is contained in full name
                if (stripos($item->rank, $fullName) !== false || stripos($fullName, $item->rank) !== false) {
                    $matchingRanks = array_merge($matchingRanks, $abbreviations);
                }
                // Check if item rank matches any abbreviation
                if (in_array($item->rank, $abbreviations)) {
                    $matchingRanks = array_merge($matchingRanks, [$fullName], $abbreviations);
                }
            }
            
            // Remove duplicates
            $matchingRanks = array_unique($matchingRanks);
            
            // Use whereIn for exact matches, with LIKE fallback
            $query->where(function($q) use ($item, $matchingRanks) {
                $q->whereIn('substantive_rank', $matchingRanks)
                  ->orWhere('substantive_rank', 'LIKE', '%' . $item->rank . '%');
            });
        }
        
        // Ensure officer has a current command
        $query->whereNotNull('present_station'); // Fixed: use whereNotNull instead of whereHas
        
        // Filter by sex requirement
        if ($item->sex_requirement !== 'ANY') {
            $query->where('sex', $item->sex_requirement);
        }
        
        // Filter by qualification if specified
        if (!empty($item->qualification_requirement)) {
            $query->where(function($q) use ($item) {
                $q->where('entry_qualification', 'LIKE', '%' . $item->qualification_requirement . '%')
                  ->orWhere('additional_qualification', 'LIKE', '%' . $item->qualification_requirement . '%');
            });
        }
        
        // Exclude officers already matched to this request
        $alreadyMatched = ManningRequestItem::where('manning_request_id', $id)
            ->whereNotNull('matched_officer_id')
            ->pluck('matched_officer_id');
        
        if ($alreadyMatched->isNotEmpty()) {
            $query->whereNotIn('id', $alreadyMatched);
        }
        
        // Get matched officers (limit to quantity needed * 2 for selection, or at least 10)
        $limit = max($item->quantity_needed * 2, 10);
        $matchedOfficers = $query->with('presentStation')
            ->take($limit)
            ->get();
        
        return view('dashboards.hrd.manning-request-matches', compact('manningRequest', 'item', 'matchedOfficers'));
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
            
            // Update matched officers in request items
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

    // Area Controller Methods
    public function areaControllerIndex(Request $request)
    {
        // Get submitted manning requests (status = SUBMITTED)
        $query = ManningRequest::with(['command.zone', 'requestedBy', 'items'])
            ->where('status', 'SUBMITTED')
            ->orderBy('submitted_at', 'desc');
        
        $requests = $query->paginate(20)->withQueryString();
        
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
            
            // Notify Staff Officer about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyManningRequestApproved($manningRequest);
            
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
}



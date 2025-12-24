<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PostingWorkflowService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MovementOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = \App\Models\MovementOrder::with(['manningRequest', 'createdBy']);

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'order_number' => 'order_number',
            'criteria' => 'criteria_months_at_station',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($column, $order);

        $orders = $query->paginate(20)->withQueryString();
        
        return view('dashboards.hrd.movement-orders', compact('orders'));
    }

    public function create()
    {
        // Get all manning requests (show SUBMITTED, APPROVED, or DRAFT statuses)
        // Status enum: DRAFT, SUBMITTED, APPROVED, REJECTED, FULFILLED
        $manningRequests = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
            ->with('command')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Generate order number (format: MO-YYYY-MMDD-XXX)
        $lastOrder = \App\Models\MovementOrder::orderBy('created_at', 'desc')->first();
        $orderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        return view('forms.movement-order.create', compact('manningRequests', 'orderNumber'));
    }

    public function store(Request $request)
    {
        // Auto-generate order number if not provided
        $orderNumber = $request->order_number;
        if (empty($orderNumber)) {
            $lastOrder = \App\Models\MovementOrder::orderBy('created_at', 'desc')->first();
            $orderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        }

        // Check if order number already exists
        if (\App\Models\MovementOrder::where('order_number', $orderNumber)->exists()) {
            $counter = 1;
            do {
                $newOrderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $counter++;
            } while (\App\Models\MovementOrder::where('order_number', $newOrderNumber)->exists());
            $orderNumber = $newOrderNumber;
        }

        $validated = $request->validate([
            'criteria_months_at_station' => 'required|integer|min:1',
            'manning_request_id' => 'nullable|exists:manning_requests,id',
            'status' => 'required|in:DRAFT,PUBLISHED,CANCELLED',
        ]);

        $validated['order_number'] = $orderNumber;
        $validated['created_by'] = auth()->id();
        
        try {
            $order = \App\Models\MovementOrder::create($validated);
            return redirect()->route('hrd.movement-orders')
                ->with('success', 'Movement order created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create movement order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest', 'createdBy', 'postings.officer.presentStation', 'postings.command'])
            ->findOrFail($id);
        
        // Load previous postings for each officer to show "from command"
        $order->postings->each(function($posting) {
            if ($posting->officer) {
                // Get the previous posting (before this movement order posting was created)
                // We need to find where the officer was BEFORE this posting
                // Since the workflow may have already updated present_station, we need to look at posting history
                $previousPosting = \App\Models\OfficerPosting::where('officer_id', $posting->officer_id)
                    ->where('id', '<', $posting->id)
                    ->with('command')
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($previousPosting) {
                    // Found a previous posting - use its command as "from"
                    $posting->fromCommand = $previousPosting->command;
                } else {
                    // No previous posting found - this might be the officer's first posting
                    // Try to get the command from the staff order or movement order's from_command
                    // For movement orders, we don't have a from_command, so we need another way
                    
                    // Check if there's a staff order that created this posting
                    if ($posting->staff_order_id) {
                        $staffOrder = \App\Models\StaffOrder::find($posting->staff_order_id);
                        if ($staffOrder && $staffOrder->fromCommand) {
                            $posting->fromCommand = $staffOrder->fromCommand;
                        }
                    }
                    
                    // If still no fromCommand, check if officer's present_station differs from posting's command
                    // This would indicate they were moved from somewhere else
                    if (!$posting->fromCommand && $posting->officer->present_station != $posting->command_id) {
                        // Officer was moved, try to find their original station
                        // Check all other postings for this officer
                        $allOtherPostings = \App\Models\OfficerPosting::where('officer_id', $posting->officer_id)
                            ->where('id', '!=', $posting->id)
                            ->with('command')
                            ->orderBy('id', 'desc')
                            ->get();
                        
                        if ($allOtherPostings->count() > 0) {
                            $posting->fromCommand = $allOtherPostings->first()->command;
                        }
                    }
                    
                    // Final fallback: use current station (though this may be wrong if workflow already ran)
                    if (!$posting->fromCommand) {
                        $posting->fromCommand = $posting->officer->presentStation;
                    }
                }
            }
        });
        
        return view('dashboards.hrd.movement-order-show', compact('order'));
    }

    public function edit($id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest'])
            ->findOrFail($id);
        
        $manningRequests = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
            ->with('command')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('forms.movement-order.edit', compact('order', 'manningRequests'));
    }

    public function update(Request $request, $id)
    {
        $order = \App\Models\MovementOrder::findOrFail($id);

        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:movement_orders,order_number,' . $id,
            'criteria_months_at_station' => 'required|integer|min:1',
            'manning_request_id' => 'nullable|exists:manning_requests,id',
            'status' => 'required|in:DRAFT,PUBLISHED,CANCELLED',
        ]);

        $oldStatus = $order->status;
        $order->update($validated);

        // If status changed to PUBLISHED and there are postings, process workflow
        if ($oldStatus !== 'PUBLISHED' && $validated['status'] === 'PUBLISHED') {
            $postings = $order->postings()->whereNull('documented_at')->get();
            if ($postings->count() > 0) {
                try {
                    $workflowService = new PostingWorkflowService();
                    $officerIds = $postings->pluck('officer_id')->toArray();
                    $workflowService->processMovementOrder($order, $officerIds);
                } catch (\Exception $e) {
                    Log::error("Failed to process movement order workflow: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('hrd.movement-orders.show', $order->id)
            ->with('success', 'Movement order updated successfully!');
    }

    /**
     * Get eligible officers based on movement order criteria
     * This implements: "HRD enters criteria that will bring up officers that have spent a particular time"
     */
    public function eligibleOfficers($id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest.items'])
            ->findOrFail($id);

        $criteriaMonths = $order->criteria_months_at_station;
        
        // Calculate cutoff date (criteriaMonths ago from today)
        $cutoffDate = Carbon::now()->subMonths($criteriaMonths);

        // Base query: Officers who have been at their current station for >= criteriaMonths
        $query = \App\Models\Officer::where('is_active', true)
            ->whereNotNull('present_station')
            ->whereNotNull('date_posted_to_station')
            ->where('date_posted_to_station', '<=', $cutoffDate)
            ->with(['presentStation', 'user']);

        // If linked to manning request, apply manning level filters
        if ($order->manningRequest && $order->manningRequest->items->count() > 0) {
            $manningItems = $order->manningRequest->items;
            
            // Get all required ranks, sexes, and qualifications from manning items
            $requiredRanks = $manningItems->pluck('rank')->filter()->unique()->toArray();
            $requiredSexes = $manningItems->pluck('sex_requirement')->filter()->unique()->toArray();
            $requiredQualifications = $manningItems->pluck('qualification_requirement')->filter()->unique()->toArray();

            // Apply filters if specified
            if (!empty($requiredRanks)) {
                $query->whereIn('substantive_rank', $requiredRanks);
            }
            if (!empty($requiredSexes)) {
                $query->whereIn('sex', $requiredSexes);
            }
            if (!empty($requiredQualifications)) {
                $query->where(function($q) use ($requiredQualifications) {
                    foreach ($requiredQualifications as $qual) {
                        $q->orWhere('entry_qualification', 'like', "%{$qual}%")
                          ->orWhere('additional_qualification', 'like', "%{$qual}%");
                    }
                });
            }
        }

        // Exclude officers who are:
        // - Interdicted
        // - Suspended
        // - Dismissed
        // - Deceased
        $query->where('interdicted', false)
              ->where('suspended', false)
              ->where('dismissed', false)
              ->where('is_deceased', false);

        // Exclude officers already posted in this movement order
        $postedOfficerIds = $order->postings()->pluck('officer_id')->toArray();
        if (!empty($postedOfficerIds)) {
            $query->whereNotIn('id', $postedOfficerIds);
        }

        $officers = $query->orderBy('surname')->orderBy('initials')->get();

        // Calculate months at station for each officer
        $officers = $officers->map(function($officer) {
            if ($officer->date_posted_to_station) {
                $monthsAtStation = Carbon::parse($officer->date_posted_to_station)->diffInMonths(Carbon::now());
                $officer->months_at_station = $monthsAtStation;
            } else {
                $officer->months_at_station = 0;
            }
            return $officer;
        });

        return view('dashboards.hrd.movement-order-eligible-officers', compact('order', 'officers', 'criteriaMonths'));
    }

    /**
     * Post selected officers from eligible list
     * This implements: "HRD will post the officers"
     */
    public function postOfficers(Request $request, $id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest'])
            ->findOrFail($id);

        $validated = $request->validate([
            'officer_ids' => 'required|array|min:1',
            'officer_ids.*' => 'exists:officers,id',
            'to_command_ids' => 'required|array',
            'to_command_ids.*' => 'nullable|exists:commands,id',
            'posting_date' => 'nullable|date',
        ]);

        $officerIds = $validated['officer_ids'];
        $toCommandIds = $validated['to_command_ids'];
        $postingDate = $validated['posting_date'] ?? now();

        // Filter to only post officers that have commands assigned
        // Match officer_ids with their corresponding command_ids by index
        $officersToPost = [];
        foreach ($officerIds as $index => $officerId) {
            $commandId = $toCommandIds[$index] ?? null;
            if ($commandId) {
                $officersToPost[] = [
                    'officer_id' => $officerId,
                    'command_id' => $commandId,
                ];
            }
        }

        // Ensure at least one officer has a command
        if (empty($officersToPost)) {
            return redirect()->back()
                ->with('error', 'Please assign destination commands to at least one selected officer.')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $workflowService = new PostingWorkflowService();
            $postedCount = 0;
            $postedOfficerIds = [];

            foreach ($officersToPost as $postingData) {
                $officer = \App\Models\Officer::find($postingData['officer_id']);
                $toCommand = \App\Models\Command::find($postingData['command_id']);

                if (!$officer || !$toCommand) {
                    continue;
                }

                // Create posting record
                $posting = \App\Models\OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'movement_order_id' => $order->id,
                    'posting_date' => $postingDate,
                    'is_current' => false, // Will be set to true when workflow processes
                    'documented_at' => null,
                ]);

                $postedCount++;
                $postedOfficerIds[] = $officer->id;
            }

            // Process workflow if order is PUBLISHED
            if ($order->status === 'PUBLISHED' && !empty($postedOfficerIds)) {
                $workflowService->processMovementOrder($order, $postedOfficerIds);
            }

            DB::commit();

            return redirect()->route('hrd.movement-orders.show', $order->id)
                ->with('success', "Successfully posted {$postedCount} officer(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post officers: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to post officers: ' . $e->getMessage())
                ->withInput();
        }
    }
}



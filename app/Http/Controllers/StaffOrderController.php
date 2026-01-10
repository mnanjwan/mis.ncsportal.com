<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffOrder;
use App\Services\PostingWorkflowService;
use App\Services\NotificationService;
use App\Services\ZonalPostingValidationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StaffOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow both HRD and Zone Coordinator - check in methods
    }
    
    /**
     * Check if user has access (HRD or Zone Coordinator)
     */
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user->hasRole('HRD') && !$user->hasRole('Zone Coordinator')) {
            abort(403, 'Access denied. You must be HRD or Zone Coordinator.');
        }
    }

    public function index(Request $request)
    {
        $query = StaffOrder::with(['officer', 'fromCommand.zone', 'toCommand.zone']);

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'order_number' => 'order_number',
            'officer' => function($query, $order) {
                $query->leftJoin('officers', 'staff_orders.officer_id', '=', 'officers.id')
                      ->orderBy('officers.surname', $order);
            },
            'from_command' => function($query, $order) {
                $query->leftJoin('commands as from_commands', 'staff_orders.from_command_id', '=', 'from_commands.id')
                      ->orderBy('from_commands.name', $order);
            },
            'to_command' => function($query, $order) {
                $query->leftJoin('commands as to_commands', 'staff_orders.to_command_id', '=', 'to_commands.id')
                      ->orderBy('to_commands.name', $order);
            },
            'status' => 'status',
            'effective_date' => 'effective_date',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Filter for Zone Coordinators - only show orders within their zone
        if ($isZoneCoordinator && !$isHRD) {
            $zoneCoordinatorRole = $user->roles()
                ->where('name', 'Zone Coordinator')
                ->wherePivot('is_active', true)
                ->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = \App\Models\Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                
                if ($coordinatorZone) {
                    // Only show orders where both from and to commands are in coordinator's zone
                    $zoneCommandIds = \App\Models\Command::where('zone_id', $coordinatorZone->id)
                        ->pluck('id')
                        ->toArray();
                    
                    $query->whereIn('from_command_id', $zoneCommandIds)
                          ->whereIn('to_command_id', $zoneCommandIds);
                }
            }
        }

        $orders = $query->select('staff_orders.*')->paginate(20)->withQueryString();
        
        // Determine route prefix based on role
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';
        
        return view('dashboards.hrd.staff-orders', compact('orders', 'isZoneCoordinator', 'routePrefix'));
    }

    public function create()
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Get officers with their present station (command)
        // Only active officers who are not suspended, dismissed, interdicted, or under investigation
        $officersQuery = \App\Models\Officer::where('is_active', true)
            ->where('suspended', false)
            ->where('dismissed', false)
            ->where('interdicted', false)
            ->where(function($q) {
                $q->whereNull('ongoing_investigation')
                  ->orWhere('ongoing_investigation', false);
            })
            ->with('presentStation.zone');
        
        // Get commands
        $commandsQuery = \App\Models\Command::where('is_active', true)
            ->with('zone');
        
        // Filter for Zone Coordinators
        $validationService = app(ZonalPostingValidationService::class);
        $zoneCoordinatorZone = $validationService->getZoneCoordinatorZone($user);
        
        // Check if accessing via zone-coordinator route (route middleware ensures Zone Coordinator role)
        $isZoneCoordinatorRoute = request()->is('zone-coordinator/*');
        $shouldApplyZoneFiltering = $isZoneCoordinator && $zoneCoordinatorZone;
        
        if ($shouldApplyZoneFiltering || ($isZoneCoordinatorRoute && $zoneCoordinatorZone)) {
            // Filter commands to only show those in coordinator's zone
            $commandsQuery->where('zone_id', $zoneCoordinatorZone->id);
            
            // Get zone command IDs
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Filter officers in zone commands
                $officersQuery->whereIn('present_station', $zoneCommandIds);
            }
        }
        
        $officers = $officersQuery->orderBy('surname')->orderBy('initials')->get();
        $commands = $commandsQuery->orderBy('name')->get();
        
        // For Zone Coordinators accessing via zone-coordinator routes, ALWAYS filter out officers above GL 07
        if ($shouldApplyZoneFiltering || ($isZoneCoordinatorRoute && $zoneCoordinatorZone)) {
            $initialCount = $officers->count();
            
            // Filter officers: only keep those at GL 07 and below
            $filteredOfficers = collect();
            $excludedOfficers = [];
            foreach ($officers as $officer) {
                $isGL07OrBelow = $validationService->isOfficerGL07OrBelow($officer->id);
                if ($isGL07OrBelow) {
                    $filteredOfficers->push($officer);
                } else {
                    $excludedOfficers[] = [
                        'service' => $officer->service_number,
                        'rank' => $officer->substantive_rank,
                        'grade' => $officer->salary_grade_level
                    ];
                }
            }
            $officers = $filteredOfficers;
            
            // Log for debugging
            if ($initialCount != $officers->count()) {
                Log::info('Zone Coordinator GL 07 filtering applied', [
                    'total_officers_before_filter' => $initialCount,
                    'officers_after_gl07_filter' => $officers->count(),
                    'filtered_out' => $initialCount - $officers->count(),
                    'excluded_officers' => $excludedOfficers,
                ]);
            }
        }
        
        // Generate order number (format: SO-YYYY-MMDD-XXX)
        $lastOrder = StaffOrder::orderBy('created_at', 'desc')->first();
        $orderNumber = 'SO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        // Determine route prefix based on route (not just role)
        $isZoneCoordinatorRoute = request()->is('zone-coordinator/*');
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        // Override isZoneCoordinator flag for view if accessing via zone-coordinator route
        $isZoneCoordinatorForView = $isZoneCoordinatorRoute ? true : ($isZoneCoordinator && !$isHRD);
        
        return view('forms.staff-order.create', compact('officers', 'commands', 'orderNumber', 'isZoneCoordinator', 'routePrefix'));
    }

    public function show($id)
    {
        $this->checkAccess();
        
        $order = StaffOrder::with(['officer', 'fromCommand', 'toCommand', 'createdBy'])
            ->findOrFail($id);
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Determine route prefix based on role
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';
        
        return view('dashboards.hrd.staff-order-show', compact('order', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        // Auto-generate order number if not provided
        $orderNumber = $request->order_number;
        if (empty($orderNumber)) {
            $lastOrder = StaffOrder::orderBy('created_at', 'desc')->first();
            $orderNumber = 'SO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        }

        // Check if order number already exists
        if (StaffOrder::where('order_number', $orderNumber)->exists()) {
            // If exists, generate a new one
            $counter = 1;
            do {
                $newOrderNumber = 'SO-' . date('Y') . '-' . date('md') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $counter++;
            } while (StaffOrder::where('order_number', $newOrderNumber)->exists());
            $orderNumber = $newOrderNumber;
        }

        $validated = $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'from_command_id' => 'required|exists:commands,id',
            'to_command_id' => 'required|exists:commands,id',
            'effective_date' => 'required|date',
            'order_type' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        // Zone Coordinator validation
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        
        if ($isZoneCoordinator) {
            // Get the zone coordinator's zone from their command assignment
            $zoneCoordinatorRole = $user->roles()->where('name', 'Zone Coordinator')->wherePivot('is_active', true)->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = \App\Models\Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                
                if ($coordinatorZone) {
                    // Get from and to commands
                    $fromCommand = \App\Models\Command::find($validated['from_command_id']);
                    $toCommand = \App\Models\Command::find($validated['to_command_id']);
                    
                    // Validate both commands are in coordinator's zone
                    if (!$fromCommand || $fromCommand->zone_id != $coordinatorZone->id) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'You can only post officers within your zone. The source command is not in your zone.');
                    }
                    
                    if (!$toCommand || $toCommand->zone_id != $coordinatorZone->id) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'You can only post officers within your zone. The destination command is not in your zone.');
                    }
                    
                    // Check officer's grade level (GL 07 and below only)
                    $officer = \App\Models\Officer::find($validated['officer_id']);
                    if ($officer) {
                        $validationService = app(ZonalPostingValidationService::class);
                        
                        // Rank ceiling check
                        if (!$validationService->isOfficerGL07OrBelow($officer->id)) {
                            $gradeLevelStr = $officer->salary_grade_level ?? 'Unknown';
                            return redirect()->back()
                                ->withInput()
                                ->with('error', 'Zone Coordinators can only post officers of GL 07 and below. This officer is ' . $gradeLevelStr . '.');
                        }
                        
                        // Command duration check
                        if (!$validationService->checkCommandDuration($officer->id)) {
                            $message = $validationService->getCommandDurationMessage($officer->id);
                            // Only show error if there's a specific message
                            if ($message) {
                                return redirect()->back()
                                    ->withInput()
                                    ->with('error', 'Officer has not completed minimum command duration. ' . $message);
                            }
                        }
                        
                        // Manning level check
                        if (!$validationService->checkManningLevel($validated['from_command_id'], $validated['to_command_id'], $officer->id)) {
                            $message = $validationService->getManningLevelMessage($validated['from_command_id'], $validated['to_command_id'], $officer->id);
                            return redirect()->back()
                                ->withInput()
                                ->with('error', 'This posting violates manning level requirements. ' . $message);
                        }
                    }
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Your assigned command does not have a zone. Please contact HRD.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You must be assigned to a command to post officers. Please contact HRD.');
            }
        }

        $validated['order_number'] = $orderNumber;
        $validated['created_by'] = auth()->id();
        $validated['status'] = $request->input('status', 'DRAFT');
        
        try {
            $order = StaffOrder::create($validated);
            
            // Load relationships needed for notification
            $order->load(['officer.user', 'fromCommand', 'toCommand']);
            
            // Notify officer about staff order creation
            $notificationService = app(NotificationService::class);
            $notificationService->notifyStaffOrderCreated($order);
            
            // Process workflow automation if order is published
            if ($validated['status'] === 'PUBLISHED') {
                $workflowService = new PostingWorkflowService();
                $workflowService->processStaffOrder($order);
            }
            
            $message = $validated['status'] === 'PUBLISHED' 
                ? 'Staff order created and published successfully! Officer has been posted.'
                : 'Staff order created successfully! (Draft - not yet effective)';
            
            // Redirect based on user role
            $redirectRoute = $isZoneCoordinator ? 'zone-coordinator.staff-orders' : 'hrd.staff-orders';
            return redirect()->route($redirectRoute)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create staff order: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->checkAccess();
        
        $order = StaffOrder::with(['officer', 'fromCommand', 'toCommand'])
            ->findOrFail($id);
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Get officers with their present station (command)
        // Only active officers who are not suspended, dismissed, interdicted, or under investigation
        $officersQuery = \App\Models\Officer::where('is_active', true)
            ->where('suspended', false)
            ->where('dismissed', false)
            ->where('interdicted', false)
            ->where(function($q) {
                $q->whereNull('ongoing_investigation')
                  ->orWhere('ongoing_investigation', false);
            })
            ->with('presentStation.zone');
        
        // Get commands
        $commandsQuery = \App\Models\Command::where('is_active', true)
            ->with('zone');
        
        // Filter for Zone Coordinators
        $validationService = app(ZonalPostingValidationService::class);
        $zoneCoordinatorZone = $validationService->getZoneCoordinatorZone($user);
        
        // Check if accessing via zone-coordinator route (route middleware ensures Zone Coordinator role)
        $isZoneCoordinatorRoute = request()->is('zone-coordinator/*');
        $shouldApplyZoneFiltering = $isZoneCoordinator && $zoneCoordinatorZone;
        
        if ($shouldApplyZoneFiltering || ($isZoneCoordinatorRoute && $zoneCoordinatorZone)) {
            // Filter commands to only show those in coordinator's zone
            $commandsQuery->where('zone_id', $zoneCoordinatorZone->id);
            
            // Get zone command IDs
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Filter officers in zone commands
                $officersQuery->whereIn('present_station', $zoneCommandIds);
            }
        }
        
        $officers = $officersQuery->orderBy('surname')->orderBy('initials')->get();
        $commands = $commandsQuery->orderBy('name')->get();
        
        // For Zone Coordinators accessing via zone-coordinator routes, ALWAYS filter out officers above GL 07
        // But always include the current order's officer even if they don't match filters
        if ($shouldApplyZoneFiltering || ($isZoneCoordinatorRoute && $zoneCoordinatorZone)) {
            $filteredOfficers = collect();
            $currentOrderOfficerId = $order->officer_id;
            
            // Ensure current order's officer is in the collection for display purposes
            $currentOrderOfficer = $order->officer;
            if ($currentOrderOfficer && !$officers->contains('id', $currentOrderOfficerId)) {
                $officers->push($currentOrderOfficer);
            }
            
            foreach ($officers as $officer) {
                $isGL07OrBelow = $validationService->isOfficerGL07OrBelow($officer->id);
                // Include officer if they're GL 07 or below, OR if they're the current order's officer
                if ($isGL07OrBelow || $officer->id == $currentOrderOfficerId) {
                    $filteredOfficers->push($officer);
                }
            }
            $officers = $filteredOfficers;
        }
        
        // Ensure current order's commands are in the commands collection
        if ($order->fromCommand && !$commands->contains('id', $order->from_command_id)) {
            $commands->push($order->fromCommand);
        }
        if ($order->toCommand && !$commands->contains('id', $order->to_command_id)) {
            $commands->push($order->toCommand);
        }
        
        // Determine route prefix based on route (not just role)
        $isZoneCoordinatorRoute = request()->is('zone-coordinator/*');
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        return view('forms.staff-order.edit', compact('order', 'officers', 'commands', 'routePrefix'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAccess();
        
        $order = StaffOrder::findOrFail($id);

        $validated = $request->validate([
            'order_number' => 'required|string|max:255|unique:staff_orders,order_number,' . $id,
            'officer_id' => 'required|exists:officers,id',
            'from_command_id' => 'required|exists:commands,id',
            'to_command_id' => 'required|exists:commands,id',
            'effective_date' => 'required|date',
            'order_type' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'nullable|in:DRAFT,PUBLISHED,CANCELLED',
        ]);

        // Zone Coordinator validation
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        
        if ($isZoneCoordinator) {
            // Get the zone coordinator's zone from their command assignment
            $zoneCoordinatorRole = $user->roles()->where('name', 'Zone Coordinator')->wherePivot('is_active', true)->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = \App\Models\Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                
                if ($coordinatorZone) {
                    // Get from and to commands
                    $fromCommand = \App\Models\Command::find($validated['from_command_id']);
                    $toCommand = \App\Models\Command::find($validated['to_command_id']);
                    
                    // Validate both commands are in coordinator's zone
                    if (!$fromCommand || $fromCommand->zone_id != $coordinatorZone->id) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'You can only post officers within your zone. The source command is not in your zone.');
                    }
                    
                    if (!$toCommand || $toCommand->zone_id != $coordinatorZone->id) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'You can only post officers within your zone. The destination command is not in your zone.');
                    }
                    
                    // Check officer's grade level (GL 07 and below only)
                    $officer = \App\Models\Officer::find($validated['officer_id']);
                    if ($officer) {
                        $validationService = app(ZonalPostingValidationService::class);
                        
                        // Rank ceiling check
                        if (!$validationService->isOfficerGL07OrBelow($officer->id)) {
                            $gradeLevelStr = $officer->salary_grade_level ?? 'Unknown';
                            return redirect()->back()
                                ->withInput()
                                ->with('error', 'Zone Coordinators can only post officers of GL 07 and below. This officer is ' . $gradeLevelStr . '.');
                        }
                        
                        // Command duration check
                        if (!$validationService->checkCommandDuration($officer->id)) {
                            $message = $validationService->getCommandDurationMessage($officer->id);
                            // Only show error if there's a specific message
                            if ($message) {
                                return redirect()->back()
                                    ->withInput()
                                    ->with('error', 'Officer has not completed minimum command duration. ' . $message);
                            }
                        }
                        
                        // Manning level check (now only checks destination capacity)
                        if (!$validationService->checkManningLevel($validated['from_command_id'], $validated['to_command_id'], $officer->id)) {
                            $message = $validationService->getManningLevelMessage($validated['from_command_id'], $validated['to_command_id'], $officer->id);
                            return redirect()->back()
                                ->withInput()
                                ->with('error', 'This posting violates manning level requirements. ' . $message);
                        }
                    }
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Your assigned command does not have a zone. Please contact HRD.');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You must be assigned to a command to post officers. Please contact HRD.');
            }
        }

        // Check if status is changing to PUBLISHED before updating
        $oldStatus = $order->status;
        $newStatus = $validated['status'] ?? $order->status;
        $statusChangedToPublished = ($oldStatus !== 'PUBLISHED' && $newStatus === 'PUBLISHED');

        $order->update($validated);

        // Process workflow automation if order status changed to PUBLISHED
        if ($statusChangedToPublished) {
            try {
                $workflowService = new PostingWorkflowService();
                $workflowService->processStaffOrder($order);
            } catch (\Exception $e) {
                // Log error but don't fail the update
                Log::error("Workflow automation failed: " . $e->getMessage());
            }
        }

        // Determine redirect route based on user role
        $isHRD = $user->hasRole('HRD');
        $isZoneCoordinatorRoute = request()->is('zone-coordinator/*');
        $redirectRoute = ($isZoneCoordinatorRoute || ($isZoneCoordinator && !$isHRD)) ? 'zone-coordinator.staff-orders.show' : 'hrd.staff-orders.show';

        return redirect()->route($redirectRoute, $order->id)
            ->with('success', 'Staff order updated successfully!');
    }
}



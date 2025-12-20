<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffOrder;
use App\Services\PostingWorkflowService;
use App\Services\NotificationService;

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
        
        return view('dashboards.hrd.staff-orders', compact('orders', 'isZoneCoordinator'));
    }

    public function create()
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Get officers with their present station (command)
        $officersQuery = \App\Models\Officer::where('is_active', true)
            ->with('presentStation.zone');
        
        // Get commands
        $commandsQuery = \App\Models\Command::where('is_active', true)
            ->with('zone');
        
        // Filter for Zone Coordinators
        if ($isZoneCoordinator && !$isHRD) {
            // Get the zone coordinator's zone from their command assignment
            $zoneCoordinatorRole = $user->roles()
                ->where('name', 'Zone Coordinator')
                ->wherePivot('is_active', true)
                ->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = \App\Models\Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                
                if ($coordinatorZone) {
                    // Filter commands to only show those in coordinator's zone
                    $commandsQuery->where('zone_id', $coordinatorZone->id);
                    
                    // Filter officers to only show:
                    // 1. Officers in commands within coordinator's zone
                    // 2. Officers with GL 07 and below
                    $zoneCommandIds = \App\Models\Command::where('zone_id', $coordinatorZone->id)
                        ->pluck('id')
                        ->toArray();
                    
                    $officersQuery->whereIn('present_station', $zoneCommandIds)
                        ->where(function($q) {
                            // GL 07 and below only
                            $q->where('salary_grade_level', 'GL05')
                              ->orWhere('salary_grade_level', 'GL06')
                              ->orWhere('salary_grade_level', 'GL07')
                              ->orWhere('salary_grade_level', '05')
                              ->orWhere('salary_grade_level', '06')
                              ->orWhere('salary_grade_level', '07')
                              ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7")
                              ->orWhereRaw("CAST(salary_grade_level AS UNSIGNED) <= 7");
                        });
                }
            }
        }
        
        $officers = $officersQuery->orderBy('surname')->orderBy('initials')->get();
        $commands = $commandsQuery->orderBy('name')->get();
        
        // Generate order number (format: SO-YYYY-MMDD-XXX)
        $lastOrder = StaffOrder::orderBy('created_at', 'desc')->first();
        $orderNumber = 'SO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        return view('forms.staff-order.create', compact('officers', 'commands', 'orderNumber', 'isZoneCoordinator'));
    }

    public function show($id)
    {
        $this->checkAccess();
        
        $order = StaffOrder::with(['officer', 'fromCommand', 'toCommand', 'createdBy'])
            ->findOrFail($id);
        
        return view('dashboards.hrd.staff-order-show', compact('order'));
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
                        $gradeLevelStr = $officer->salary_grade_level ?? '';
                        // Extract numeric part (handle formats like "GL07", "07", "7", etc.)
                        preg_match('/(\d+)/', $gradeLevelStr, $matches);
                        $gradeLevel = isset($matches[1]) ? (int)$matches[1] : 99;
                        
                        if ($gradeLevel > 7) {
                            return redirect()->back()
                                ->withInput()
                                ->with('error', 'Zone Coordinators can only post officers of GL 07 and below. This officer is ' . $gradeLevelStr . '.');
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
            
            return redirect()->route('hrd.staff-orders')
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
        $officers = \App\Models\Officer::where('is_active', true)
            ->orderBy('surname')
            ->get();
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('forms.staff-order.edit', compact('order', 'officers', 'commands'));
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

        $order->update($validated);

        // Process workflow automation if order status changed to PUBLISHED
        if (isset($validated['status']) && $validated['status'] === 'PUBLISHED' && $order->wasChanged('status')) {
            try {
                $workflowService = new PostingWorkflowService();
                $workflowService->processStaffOrder($order);
            } catch (\Exception $e) {
                // Log error but don't fail the update
                \Log::error("Workflow automation failed: " . $e->getMessage());
            }
        }

        return redirect()->route('hrd.staff-orders.show', $order->id)
            ->with('success', 'Staff order updated successfully!');
    }
}



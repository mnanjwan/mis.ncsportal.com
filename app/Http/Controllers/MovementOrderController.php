<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PostingWorkflowService;
use App\Services\NotificationService;
use App\Services\ZonalPostingValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;

class MovementOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow both HRD and Zone Coordinator
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->hasRole('HRD') && !$user->hasRole('Zone Coordinator')) {
                abort(403, 'Access denied. You must be HRD or Zone Coordinator.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        $query = \App\Models\MovementOrder::with(['manningRequest', 'createdBy']);

        // STRICT FILTERING: HRD sees only HRD orders, Zone Coordinators see only zone orders
        
        // Get all user IDs that have Zone Coordinator role (for exclusion in HRD queries)
        $zoneCoordinatorUserIds = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Zone Coordinator');
        })->pluck('id')->toArray();
        
        if ($isZoneCoordinator && !$isHRD) {
            // Zone Coordinators: ONLY show zone-related orders
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                $query->where(function($q) use ($zoneCommandIds, $user) {
                    // Orders linked to ZONE type manning requests from their zone
                    $q->whereHas('manningRequest', function($subQ) use ($zoneCommandIds) {
                        $subQ->where('type', 'ZONE')
                             ->whereIn('command_id', $zoneCommandIds);
                    })
                    // OR orders with postings TO zone commands (destination is in their zone)
                    ->orWhereHas('postings', function($subQ) use ($zoneCommandIds) {
                        $subQ->whereIn('command_id', $zoneCommandIds);
                    })
                    // OR orders created by this specific zone coordinator that don't have GENERAL type manning requests
                    ->orWhere(function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id)
                             ->where(function($subSubQ) {
                                 // Either no manning request
                                 $subSubQ->whereDoesntHave('manningRequest')
                                         // Or manning request is ZONE type (not GENERAL)
                                         ->orWhereHas('manningRequest', function($manningQ) {
                                             $manningQ->where('type', '!=', 'GENERAL')
                                                      ->where(function($typeQ) {
                                                          $typeQ->where('type', 'ZONE')->orWhereNull('type');
                                                      });
                                         });
                             });
                    });
                })
                // STRICT: Exclude ALL orders linked to GENERAL type manning requests (HRD only)
                ->whereDoesntHave('manningRequest', function($q) {
                    $q->where('type', 'GENERAL');
                });
            } else {
                // If no zone commands, show nothing
                $query->whereRaw('1 = 0');
            }
        } else if ($isHRD && !$isZoneCoordinator) {
            // HRD: ONLY show HRD-related orders (GENERAL type or created by HRD users only)
            // STRICT: First exclude ALL orders created by zone coordinators
            $query->whereNotIn('created_by', $zoneCoordinatorUserIds)
                  ->where(function($q) use ($zoneCoordinatorUserIds) {
                      // Orders with GENERAL type manning requests
                      $q->whereHas('manningRequest', function($subQ) {
                          $subQ->where('type', 'GENERAL');
                      })
                      // OR orders without manning requests (already excluded zone coordinator creators above)
                      ->orWhereDoesntHave('manningRequest');
                  })
                  // STRICT: Exclude ALL orders linked to ZONE type manning requests
                  ->whereDoesntHave('manningRequest', function($q) {
                      $q->where('type', 'ZONE');
                  });
        }

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
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        return view('dashboards.hrd.movement-orders', compact('orders', 'routePrefix'));
    }

    public function create()
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Get all manning requests (show SUBMITTED, APPROVED, or DRAFT statuses)
        // Status enum: DRAFT, SUBMITTED, APPROVED, REJECTED, FULFILLED
        $manningRequestsQuery = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
            ->with('command');
        
        // Filter for Zone Coordinators - only show ZONE type requests from their zone
        if ($isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                $manningRequestsQuery->where('type', 'ZONE')
                    ->whereIn('command_id', $zoneCommandIds);
            } else {
                $manningRequestsQuery->whereRaw('1 = 0'); // No results
            }
        } else {
            // For HRD, show only GENERAL type requests (or NULL for backward compatibility)
            $manningRequestsQuery->where(function($q) {
                $q->where('type', 'GENERAL')
                  ->orWhereNull('type');
            });
        }
        
        $manningRequests = $manningRequestsQuery->orderBy('created_at', 'desc')->get();
        
        // Generate order number (format: MO-YYYY-MMDD-XXX)
        $lastOrder = \App\Models\MovementOrder::orderBy('created_at', 'desc')->first();
        $orderNumber = 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad(($lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1), 3, '0', STR_PAD_LEFT);
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        return view('forms.movement-order.create', compact('manningRequests', 'orderNumber', 'routePrefix'));
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
        ]);

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Validate manning request belongs to correct type and zone (if provided)
        if (!empty($validated['manning_request_id'])) {
            $manningRequest = \App\Models\ManningRequest::find($validated['manning_request_id']);
            
            if (!$manningRequest) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected manning request not found.');
            }
            
            // For Zone Coordinators: validate manning request is ZONE type and in their zone
            if ($isZoneCoordinator && !$isHRD) {
                if ($manningRequest->type !== 'ZONE') {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Zone coordinators can only link to Zone type manning requests.');
                }
                
                $validationService = app(ZonalPostingValidationService::class);
                $zoneCommandIds = $validationService->getZoneCommandIds($user);
                
                if (empty($zoneCommandIds) || !in_array($manningRequest->command_id, $zoneCommandIds)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'The selected manning request does not belong to your zone.');
                }
            } elseif ($isHRD && !$isZoneCoordinator) {
                // For HRD: validate manning request is GENERAL type
                if ($manningRequest->type !== 'GENERAL') {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'HRD can only link to General type manning requests.');
                }
            }
        }

        $validated['order_number'] = $orderNumber;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'DRAFT'; // Always create as DRAFT
        
        try {
            $order = \App\Models\MovementOrder::create($validated);
            return redirect()->route($routePrefix . '.movement-orders')
                ->with('success', 'Movement order created successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create movement order: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
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
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        // Access control for Zone Coordinators - ensure they can only access their zone orders
        if ($isZoneCoordinatorRoute) {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
            
            if ($isZoneCoordinator && !$isHRD) {
                $validationService = app(ZonalPostingValidationService::class);
                $zoneCommandIds = $validationService->getZoneCommandIds($user);
                
                // Check if order belongs to zone coordinator (created by them)
                $orderBelongsToCoordinator = $order->created_by === $user->id;
                
                // Check if order is linked to a ZONE manning request from their zone
                $orderInZone = false;
                if ($order->manningRequest && $order->manningRequest->type === 'ZONE') {
                    $orderInZone = !empty($zoneCommandIds) && in_array($order->manningRequest->command_id, $zoneCommandIds);
                }
                
                // Check if order has postings in their zone
                $orderHasZonePostings = false;
                if ($order->postings && $order->postings->count() > 0) {
                    $postingCommandIds = $order->postings->pluck('command_id')->filter()->toArray();
                    $orderHasZonePostings = !empty($zoneCommandIds) && !empty(array_intersect($postingCommandIds, $zoneCommandIds));
                }
                
                // Allow access if order belongs to coordinator OR is in their zone
                if (!$orderBelongsToCoordinator && !$orderInZone && !$orderHasZonePostings) {
                    abort(403, 'You do not have access to this movement order.');
                }
            }
        }
        
        return view('dashboards.hrd.movement-order-show', compact('order', 'routePrefix'));
    }

    public function edit($id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest', 'postings'])
            ->findOrFail($id);
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Access control for Zone Coordinators - ensure they can only edit their zone orders
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            // Check if order belongs to zone coordinator (created by them)
            $orderBelongsToCoordinator = $order->created_by === $user->id;
            
            // Check if order is linked to a ZONE manning request from their zone
            $orderInZone = false;
            if ($order->manningRequest && $order->manningRequest->type === 'ZONE') {
                $orderInZone = !empty($zoneCommandIds) && in_array($order->manningRequest->command_id, $zoneCommandIds);
            }
            
            // Check if order has postings in their zone
            $orderHasZonePostings = false;
            if ($order->postings && $order->postings->count() > 0) {
                $postingCommandIds = $order->postings->pluck('command_id')->filter()->toArray();
                $orderHasZonePostings = !empty($zoneCommandIds) && !empty(array_intersect($postingCommandIds, $zoneCommandIds));
            }
            
            // Allow access if order belongs to coordinator OR is in their zone
            if (!$orderBelongsToCoordinator && !$orderInZone && !$orderHasZonePostings) {
                abort(403, 'You do not have access to edit this movement order.');
            }
        }
        
        // Filter manning requests for zone coordinator - only ZONE type
        if ($isZoneCoordinatorRoute) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                $manningRequests = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
                    ->where(function($q) {
                        $q->where('type', 'ZONE')->orWhereNull('type');
                    })
                    ->whereIn('command_id', $zoneCommandIds)
                    ->with('command')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $manningRequests = collect();
            }
        } else {
            // For HRD, show only GENERAL type requests
            $manningRequests = \App\Models\ManningRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'APPROVED'])
                ->where(function($q) {
                    $q->where('type', 'GENERAL')->orWhereNull('type');
                })
                ->with('command')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('forms.movement-order.edit', compact('order', 'manningRequests', 'routePrefix'));
    }

    public function update(Request $request, $id)
    {
        $order = \App\Models\MovementOrder::with(['manningRequest', 'postings'])->findOrFail($id);
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Access control for Zone Coordinators - ensure they can only update their zone orders
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $validationService = app(ZonalPostingValidationService::class);
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            // Check if order belongs to zone coordinator (created by them)
            $orderBelongsToCoordinator = $order->created_by === $user->id;
            
            // Check if order is linked to a ZONE manning request from their zone
            $orderInZone = false;
            if ($order->manningRequest && $order->manningRequest->type === 'ZONE') {
                $orderInZone = !empty($zoneCommandIds) && in_array($order->manningRequest->command_id, $zoneCommandIds);
            }
            
            // Check if order has postings in their zone
            $orderHasZonePostings = false;
            if ($order->postings && $order->postings->count() > 0) {
                $postingCommandIds = $order->postings->pluck('command_id')->filter()->toArray();
                $orderHasZonePostings = !empty($zoneCommandIds) && !empty(array_intersect($postingCommandIds, $zoneCommandIds));
            }
            
            // Allow access if order belongs to coordinator OR is in their zone
            if (!$orderBelongsToCoordinator && !$orderInZone && !$orderHasZonePostings) {
                abort(403, 'You do not have access to update this movement order.');
            }
        }

        $validated = $request->validate([
            'order_number' => 'required|string|max:100|unique:movement_orders,order_number,' . $id,
            'criteria_months_at_station' => 'required|integer|min:1',
            'manning_request_id' => 'nullable|exists:manning_requests,id',
        ]);
        
        if (!empty($validated['manning_request_id'])) {
            $manningRequest = \App\Models\ManningRequest::find($validated['manning_request_id']);
            
            if ($manningRequest) {
                // For Zone Coordinators: validate manning request is ZONE type and in their zone
                if ($isZoneCoordinator && !$isHRD) {
                    if ($manningRequest->type !== 'ZONE') {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Zone coordinators can only link to Zone type manning requests.');
                    }
                    
                    $validationService = app(ZonalPostingValidationService::class);
                    $zoneCommandIds = $validationService->getZoneCommandIds($user);
                    
                    if (empty($zoneCommandIds) || !in_array($manningRequest->command_id, $zoneCommandIds)) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'The selected manning request does not belong to your zone.');
                    }
                } elseif ($isHRD && !$isZoneCoordinator) {
                    // For HRD: validate manning request is GENERAL type
                    if ($manningRequest->type !== 'GENERAL') {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'HRD can only link to General type manning requests.');
                    }
                }
            }
        }

        // Don't allow status change through edit - use publish action instead
        $order->update($validated);

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
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

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $validationService = app(ZonalPostingValidationService::class);
        
        // Base query: Officers who have been at their current station for >= criteriaMonths
        $query = \App\Models\Officer::where('is_active', true)
            ->whereNotNull('present_station')
            ->whereNotNull('date_posted_to_station')
            ->where('date_posted_to_station', '<=', $cutoffDate)
            ->with(['presentStation', 'user']);
        
        // Filter for Zone Coordinators - only show officers currently stationed in their zone
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            
            if (!empty($zoneCommandIds)) {
                // Filter by zone - only officers currently stationed in zone commands
                $query->whereIn('present_station', $zoneCommandIds);
            } else {
                $query->whereRaw('1 = 0'); // No results if no zone commands
            }
        }

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

        // For Zone Coordinators, filter out officers above GL 07
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $officers = $officers->filter(function($officer) use ($validationService) {
                return $validationService->isOfficerGL07OrBelow($officer->id);
            })->values();
        }

        // Calculate months at station for each officer and add validation status
        $officers = $officers->map(function($officer) use ($isZoneCoordinatorRoute, $isZoneCoordinator, $isHRD, $validationService) {
            if ($officer->date_posted_to_station) {
                $monthsAtStation = Carbon::parse($officer->date_posted_to_station)->diffInMonths(Carbon::now());
                $officer->months_at_station = $monthsAtStation;
            } else {
                $officer->months_at_station = 0;
            }
            
            // For Zone Coordinators, check additional validations
            if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
                $officer->meets_command_duration = $validationService->checkCommandDuration($officer->id);
                $officer->is_gl07_or_below = true; // Already filtered above
                // Manning level check will be done when selecting destination command
            }
            
            return $officer;
        });

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        // Get available commands for posting
        // For Zone Coordinators: only show commands in their zone
        // For HRD: show all active commands
        $availableCommands = \App\Models\Command::where('is_active', true);
        
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            if (!empty($zoneCommandIds)) {
                $availableCommands = $availableCommands->whereIn('id', $zoneCommandIds);
            } else {
                $availableCommands = $availableCommands->whereRaw('1 = 0'); // No results
            }
        }
        
        $availableCommands = $availableCommands->orderBy('name')->get();
        
        return view('dashboards.hrd.movement-order-eligible-officers', compact('order', 'officers', 'criteriaMonths', 'routePrefix', 'availableCommands'));
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

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $validationService = app(ZonalPostingValidationService::class);
        
        // For Zone Coordinators: get zone command IDs upfront
        $zoneCommandIds = [];
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            if (empty($zoneCommandIds)) {
                return redirect()->back()
                    ->with('error', 'You do not have any zone commands assigned.')
                    ->withInput();
            }
        }
        
        DB::beginTransaction();
        try {
            $postedCount = 0;
            $errors = [];

            foreach ($officersToPost as $postingData) {
                $officer = \App\Models\Officer::with('presentStation')->find($postingData['officer_id']);
                $toCommand = \App\Models\Command::find($postingData['command_id']);

                if (!$officer || !$toCommand) {
                    continue;
                }
                
                // Get from command ID (current station of the officer)
                $fromCommandId = $officer->present_station ?? null;
                
                // Zone Coordinator validations
                if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
                    
                    // Check zone - both FROM and TO commands must be in zone
                    $fromCommandInZone = !empty($fromCommandId) && in_array($fromCommandId, $zoneCommandIds);
                    $toCommandInZone = in_array($toCommand->id, $zoneCommandIds);
                    
                    if (!$fromCommandInZone || !$toCommandInZone) {
                        $errors[] = "Officer {$officer->service_number}: Commands must be in your zone.";
                        continue;
                    }
                    
                    // Check rank ceiling
                    if (!$validationService->isOfficerGL07OrBelow($officer->id)) {
                        $errors[] = "Officer {$officer->service_number}: Only GL 07 and below can be posted.";
                        continue;
                    }
                    
                    // Check command duration
                    if (!$validationService->checkCommandDuration($officer->id)) {
                        $errors[] = "Officer {$officer->service_number}: Has not completed minimum command duration.";
                        continue;
                    }
                    
                    // Check manning level
                    if (!$validationService->checkManningLevel($fromCommandId, $toCommand->id, $officer->id)) {
                        $errors[] = "Officer {$officer->service_number}: Violates manning level requirements.";
                        continue;
                    }
                }

                // Create posting record (for tracking/reference)
                $posting = \App\Models\OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'movement_order_id' => $order->id,
                    'posting_date' => $postingDate,
                    'is_current' => false, // Will be set to true when workflow processes
                    'documented_at' => null,
                ]);

                // Get or create role-aware draft deployment
                // Zone Coordinators use Zone Coordinator deployments, HRD uses HRD deployments
                $zoneCoordinatorUserIds = \App\Models\User::whereHas('roles', function($q) {
                    $q->where('name', 'Zone Coordinator');
                })->pluck('id')->toArray();
                
                $deploymentQuery = ManningDeployment::draft();
                
                // Filter by creator role to ensure separation
                // Each role has ONE shared draft for all users in that role
                if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
                    // Zone Coordinators: use deployments created by ANY Zone Coordinator (shared draft)
                    $deploymentQuery->whereIn('created_by', $zoneCoordinatorUserIds);
                } else if ($isHRD && !$isZoneCoordinator) {
                    // HRD: only use deployments created by HRD users (not Zone Coordinators) - shared draft
                    $deploymentQuery->whereNotIn('created_by', $zoneCoordinatorUserIds);
                }
                
                $deployment = $deploymentQuery->latest()->first();

                if (!$deployment) {
                    // No active draft exists for this role - create a new role-specific draft
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

                // Add officer to draft deployment (if not already there from this movement order)
                $existingAssignment = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                    ->where('officer_id', $officer->id)
                    ->where('to_command_id', $toCommand->id)
                    ->where('notes', 'like', '%From Movement Order: ' . $order->order_number . '%')
                    ->first();

                if (!$existingAssignment) {
                    ManningDeploymentAssignment::create([
                        'manning_deployment_id' => $deployment->id,
                        'manning_request_id' => $order->manning_request_id, // Link to manning request if exists
                        'manning_request_item_id' => null, // Movement orders don't have specific items
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommandId,
                        'to_command_id' => $toCommand->id,
                        'rank' => $officer->substantive_rank,
                        'notes' => 'From Movement Order: ' . $order->order_number,
                    ]);
                }

                $postedCount++;
            }
            
            if (!empty($errors) && $postedCount == 0) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'No officers were posted. Errors: ' . implode(' ', $errors))
                    ->withInput();
            }
            
            if (!empty($errors)) {
                // Some posted, some failed
                Log::warning('Some officers failed validation: ' . implode(' ', $errors));
            }

            DB::commit();
            
            // Determine route prefix based on URL path or route name
            $path = request()->path();
            $routeName = request()->route() ? request()->route()->getName() : '';
            
            $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                       (strpos($routeName, 'zone-coordinator.') === 0);
            $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
            
            $message = "Successfully posted {$postedCount} officer(s) and added to draft deployment.";
            if (!empty($errors)) {
                $message .= " Some officers were skipped due to validation errors.";
            }
            $message .= " You can review and publish from the draft page.";

            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to post officers: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to post officers: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Publish movement order and process workflow
     */
    public function publish($id)
    {
        $order = \App\Models\MovementOrder::findOrFail($id);

        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        if ($order->status === 'PUBLISHED') {
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Movement order is already published.');
        }

        if ($order->status === 'CANCELLED') {
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Cannot publish a cancelled movement order.');
        }

        // Check if there are postings
        $postings = $order->postings()->whereNull('documented_at')->get();
        if ($postings->isEmpty()) {
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Cannot publish movement order without any officer postings.');
        }

        DB::beginTransaction();
        try {
            // Update status to PUBLISHED
            $order->update(['status' => 'PUBLISHED']);

            // Dispatch job to process workflow asynchronously
            $officerIds = $postings->pluck('officer_id')->toArray();
            \App\Jobs\ProcessMovementOrderJob::dispatch($order, $officerIds);

            DB::commit();

            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('success', 'Movement order published successfully! Workflow is being processed in the background.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to publish movement order: " . $e->getMessage());
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Failed to publish movement order: ' . $e->getMessage());
        }
    }

    /**
     * Add movement order officers to draft deployment
     * This ensures officers from movement orders appear in the general draft
     */
    public function addToDraft($id)
    {
        $order = \App\Models\MovementOrder::with(['postings.officer', 'postings.command'])->findOrFail($id);
        
        // Determine route prefix based on URL path or route name
        $path = request()->path();
        $routeName = request()->route() ? request()->route()->getName() : '';
        
        $isZoneCoordinatorRoute = (strpos($path, 'zone-coordinator/') === 0) || 
                                   (strpos($routeName, 'zone-coordinator.') === 0);
        $routePrefix = $isZoneCoordinatorRoute ? 'zone-coordinator' : 'hrd';
        
        if ($order->status !== 'DRAFT') {
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Only draft movement orders can be added to draft deployment.');
        }

        if (!$order->postings || $order->postings->isEmpty()) {
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'No officers posted to this movement order yet.');
        }

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $validationService = app(ZonalPostingValidationService::class);
        
        // For Zone Coordinators: get zone command IDs for validation
        $zoneCommandIds = [];
        if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
            $zoneCommandIds = $validationService->getZoneCommandIds($user);
            if (empty($zoneCommandIds)) {
                return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                    ->with('error', 'You do not have any zone commands assigned.');
            }
        }

        DB::beginTransaction();
        try {
            // Get or create role-aware draft deployment
            // Zone Coordinators use Zone Coordinator deployments, HRD uses HRD deployments
            $zoneCoordinatorUserIds = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'Zone Coordinator');
            })->pluck('id')->toArray();
            
            $deploymentQuery = ManningDeployment::draft();
            
            // Filter by creator role to ensure separation
            if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
                // Zone Coordinators: only use deployments created by Zone Coordinators
                $deploymentQuery->whereIn('created_by', array_merge($zoneCoordinatorUserIds, [$user->id]));
            } else if ($isHRD && !$isZoneCoordinator) {
                // HRD: only use deployments created by HRD users (not Zone Coordinators)
                $deploymentQuery->whereNotIn('created_by', $zoneCoordinatorUserIds);
            }
            
            $deployment = $deploymentQuery->latest()->first();

            if (!$deployment) {
                // No active draft exists for this role - create a new role-specific draft
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

            $addedCount = 0;
            $alreadyInDraftCount = 0;
            $errors = [];

            foreach ($order->postings as $posting) {
                if (!$posting->officer || !$posting->command) {
                    continue;
                }
                
                $officer = $posting->officer;
                $toCommand = $posting->command;
                $fromCommandId = $posting->officer->present_station ?? null;
                
                // Zone Coordinator validations
                if ($isZoneCoordinatorRoute && $isZoneCoordinator && !$isHRD) {
                    $fromCommandInZone = !empty($fromCommandId) && in_array($fromCommandId, $zoneCommandIds);
                    $toCommandInZone = in_array($toCommand->id, $zoneCommandIds);
                    
                    if (!$fromCommandInZone || !$toCommandInZone) {
                        $errors[] = "Officer {$officer->service_number}: Commands must be in your zone.";
                        continue;
                    }
                }

                // Check if officer is already in this deployment from this movement order
                $existingAssignment = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                    ->where('officer_id', $officer->id)
                    ->where('to_command_id', $toCommand->id)
                    ->where('notes', 'like', '%From Movement Order: ' . $order->order_number . '%')
                    ->first();

                if (!$existingAssignment) {
                    ManningDeploymentAssignment::create([
                        'manning_deployment_id' => $deployment->id,
                        'manning_request_id' => $order->manning_request_id, // Link to manning request if exists
                        'manning_request_item_id' => null, // Movement orders don't have specific items
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommandId,
                        'to_command_id' => $toCommand->id,
                        'rank' => $officer->substantive_rank,
                        'notes' => 'From Movement Order: ' . $order->order_number,
                    ]);
                    $addedCount++;
                } else {
                    $alreadyInDraftCount++;
                }
            }

            DB::commit();

            if ($addedCount > 0) {
                $message = "Successfully added {$addedCount} officer(s) to draft deployment.";
                if ($alreadyInDraftCount > 0) {
                    $message .= " {$alreadyInDraftCount} officer(s) were already in draft.";
                }
                if (!empty($errors)) {
                    $message .= " Some officers were skipped: " . implode(' ', $errors);
                }
            } else {
                $message = "All officers from this movement order are already in draft deployment.";
            }

            return redirect()->route($routePrefix . '.manning-deployments.draft')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add movement order officers to draft: " . $e->getMessage());
            return redirect()->route($routePrefix . '.movement-orders.show', $order->id)
                ->with('error', 'Failed to add officers to draft: ' . $e->getMessage());
        }
    }
}



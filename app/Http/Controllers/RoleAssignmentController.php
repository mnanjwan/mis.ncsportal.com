<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Command;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\NotificationService;

class RoleAssignmentController extends Controller
{
    /**
     * List all users with their role assignments
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        // Get Zone Coordinator's zone if applicable
        $coordinatorZoneId = null;
        if ($isZoneCoordinator && !$isHRD) {
            $zoneCoordinatorRole = $user->roles()
                ->where('name', 'Zone Coordinator')
                ->wherePivot('is_active', true)
                ->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                $coordinatorZoneId = $coordinatorZone ? $coordinatorZone->id : null;
            }
        }
        
        // Get the Officer role ID to exclude users who ONLY have Officer role
        $officerRole = Role::where('name', 'Officer')->first();
        $officerRoleId = $officerRole ? $officerRole->id : null;
        
        // Build base query - get users with active roles, excluding those who ONLY have Officer role
        $query = User::query()
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.is_active', true)
            ->where('roles.name', '!=', 'Officer') // Exclude Officer role from the join
            ->select('users.*')
            ->distinct();

        // Filter for Zone Coordinators - only show role assignments in their zone
        if ($isZoneCoordinator && !$isHRD && $coordinatorZoneId) {
            // Get all command IDs in the coordinator's zone
            $zoneCommandIds = Command::where('zone_id', $coordinatorZoneId)
                ->pluck('id')
                ->toArray();
            
            // Only show users with role assignments to commands in their zone
            $query->whereIn('user_roles.command_id', $zoneCommandIds);
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('user_roles.role_id', $request->role_id);
        }

        // Filter by command
        if ($request->filled('command_id')) {
            $query->where('user_roles.command_id', $request->command_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('user_roles.is_active', $isActive);
        }

        // Search by officer name or service number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('initials', 'like', "%{$search}%")
                  ->orWhere('service_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'name' => function($query, $order) {
                $query->leftJoin('officers', 'users.id', '=', 'officers.user_id')
                      ->orderBy('officers.surname', $order);
            },
            'email' => 'users.email',
            'role' => 'roles.name',
            'command' => function($query, $order) {
                $query->leftJoin('commands', 'user_roles.command_id', '=', 'commands.id')
                      ->orderBy('commands.name', $order);
            },
            'status' => 'user_roles.is_active',
            'assigned_at' => 'user_roles.assigned_at',
            'created_at' => 'users.created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'users.created_at';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $users = $query->paginate(20)->withQueryString();
        
        // Get user IDs from the paginated collection
        $userIds = $users->pluck('id')->toArray();
        
        // Eager load roles and officer relationships for these users
        if (!empty($userIds)) {
            $usersWithRelations = User::whereIn('id', $userIds)
                ->with(['roles' => function($q) {
                    $q->wherePivot('is_active', true);
                }, 'officer.presentStation'])
                ->get()
                ->keyBy('id');
            
            // Map the relationships back to the paginated collection
            $users->getCollection()->transform(function($user) use ($usersWithRelations, $request) {
                if ($relatedUser = $usersWithRelations->get($user->id)) {
                    $user->setRelation('roles', $relatedUser->roles);
                    $user->setRelation('officer', $relatedUser->officer);
                    
                    // Filter roles to only show active ones (or based on status filter), excluding "Officer" role
                    if ($request->filled('status')) {
                        $isActive = $request->status === 'active';
                        $user->setRelation('roles', $user->roles->filter(function($role) use ($isActive) {
                            return $role->pivot->is_active == $isActive && $role->name !== 'Officer';
                        }));
                    } else {
                        // Only show active roles by default, excluding "Officer" role
                        $user->setRelation('roles', $user->roles->filter(function($role) {
                            return $role->pivot->is_active == true && $role->name !== 'Officer';
                        }));
                    }
                }
                return $user;
            });
        }

        $allRoles = Role::orderBy('name')->get();
        
        // Get commands - filter by zone for Zone Coordinators
        $commandsQuery = Command::where('is_active', true);
        
        // Filter for Zone Coordinators - only show commands in their zone
        if ($isZoneCoordinator && !$isHRD && $coordinatorZoneId) {
            // Only show commands in the coordinator's zone
            $commandsQuery->where('zone_id', $coordinatorZoneId);
        }
        
        $commands = $commandsQuery->orderBy('name')->get();
        
        // Define which roles require command assignment
        $commandBasedRoles = [
            'Assessor',
            'Validator',
            'Staff Officer',
            'Area Controller',
            'DC Admin',
            'Building Unit',
            'Admin'
        ];

        return view('dashboards.hrd.role-assignments', compact(
            'users',
            'allRoles',
            'commands',
            'commandBasedRoles'
        ));
    }

    /**
     * Show form to assign role to user
     */
    public function create()
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        
        $allRoles = Role::orderBy('name')->get();
        
        // Get commands - filter by zone for Zone Coordinators
        $commandsQuery = Command::where('is_active', true);
        
        // Filter for Zone Coordinators - only show commands in their zone
        if ($isZoneCoordinator && !$isHRD) {
            $zoneCoordinatorRole = $user->roles()
                ->where('name', 'Zone Coordinator')
                ->wherePivot('is_active', true)
                ->first();
            
            if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
                $coordinatorCommand = Command::find($zoneCoordinatorRole->pivot->command_id);
                $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
                
                if ($coordinatorZone) {
                    // Only show commands in the coordinator's zone
                    $commandsQuery->where('zone_id', $coordinatorZone->id);
                }
            }
        }
        
        $commands = $commandsQuery->orderBy('name')->get();

        // Define which roles require command assignment
        $commandBasedRoles = [
            'Assessor',
            'Validator',
            'Staff Officer',
            'Area Controller',
            'DC Admin',
            'Building Unit',
            'Admin'
        ];

        return view('dashboards.hrd.assign-role', compact(
            'allRoles',
            'commands',
            'commandBasedRoles'
        ));
    }

    /**
     * API: Get officers by command
     */
    public function getOfficersByCommand(Request $request)
    {
        try {
            // Validate request - accept both string and integer
            $validated = $request->validate([
                'command_id' => 'required'
            ]);

            $commandId = (int) $validated['command_id'];
            
            if ($commandId <= 0) {
                return response()->json([
                    'error' => 'Invalid command ID',
                    'message' => 'Command ID must be a positive integer'
                ], 422);
            }

            // Verify command exists
            $commandExists = Command::where('id', $commandId)->exists();
            if (!$commandExists) {
                return response()->json([
                    'error' => 'Command not found',
                    'message' => "Command with ID {$commandId} does not exist"
                ], 404);
            }

            // Query officers exactly like the Officers List does - simple and direct
            // Match the exact query pattern used in OfficerController@index
            $officersQuery = Officer::where('present_station', $commandId)
                ->orderBy('surname')
                ->orderBy('initials');
            
            $officers = $officersQuery->get()->map(function($officer) {
                if (!$officer) {
                    return null;
                }
                return [
                    'id' => $officer->id ?? null,
                    'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')),
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A',
                ];
            })->filter()->values();

            return response()->json($officers);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in getOfficersByCommand', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error fetching officers by command', [
                'command_id' => $request->command_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch officers',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching officers'
            ], 500);
        }
    }

    /**
     * API: Check existing active roles for an officer
     */
    public function checkExistingRoles(Request $request)
    {
        try {
            $validated = $request->validate([
                'officer_id' => 'required|exists:officers,id',
            ]);

            $officer = Officer::findOrFail($validated['officer_id']);
            $user = $officer->user;

            if (!$user) {
                // No user means no existing roles
                return response()->json([
                    'has_existing_roles' => false,
                    'existing_roles' => []
                ]);
            }

            // Get Officer role ID to exclude it
            $officerRole = Role::where('name', 'Officer')->first();
            $officerRoleId = $officerRole ? $officerRole->id : null;

            // Get all active roles excluding Officer role
            $activeRoles = $user->roles()
                ->wherePivot('is_active', true)
                ->get()
                ->filter(function($role) use ($officerRoleId) {
                    return $role->id != $officerRoleId;
                })
                ->map(function($role) {
                    $command = $role->pivot->command_id ? Command::find($role->pivot->command_id) : null;
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'command_id' => $role->pivot->command_id,
                        'command_name' => $command ? $command->name : null,
                    ];
                })
                ->values();

            return response()->json([
                'has_existing_roles' => $activeRoles->count() > 0,
                'existing_roles' => $activeRoles
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking existing roles', [
                'officer_id' => $request->officer_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to check existing roles',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while checking existing roles'
            ], 500);
        }
    }

    /**
     * Helper method to deactivate all active roles except Officer and the new role
     */
    private function deactivateOtherRoles(User $user, $newRoleId)
    {
        // Get Officer role ID
        $officerRole = Role::where('name', 'Officer')->first();
        $officerRoleId = $officerRole ? $officerRole->id : null;

        // Get all active roles excluding Officer role and the new role being assigned
        $otherActiveRoles = $user->roles()
            ->wherePivot('is_active', true)
            ->where('roles.id', '!=', $newRoleId);

        // Exclude Officer role from deactivation
        if ($officerRoleId) {
            $otherActiveRoles->where('roles.id', '!=', $officerRoleId);
        }

        $rolesToDeactivate = $otherActiveRoles->get();

        // Deactivate each role
        foreach ($rolesToDeactivate as $role) {
            $user->roles()->updateExistingPivot($role->id, [
                'is_active' => false,
            ]);
        }

        return $rolesToDeactivate;
    }

    /**
     * Store role assignment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'officer_id' => 'required|exists:officers,id',
                'role_id' => 'required|exists:roles,id',
                'confirm_override' => 'sometimes|boolean',
                'command_id' => [
                    'nullable',
                    'exists:commands,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$request->role_id) return;
                        $role = Role::find($request->role_id);
                        if (!$role) return;
                        
                        $commandBasedRoles = [
                            'Assessor',
                            'Validator',
                            'Staff Officer',
                            'Area Controller',
                            'DC Admin',
                            'Building Unit',
                            'Admin'
                        ];
                        
                        if (in_array($role->name, $commandBasedRoles) && !$value) {
                            $fail('Command assignment is required for ' . $role->name . ' role.');
                        }
                    },
                ],
            ]);

            $officer = Officer::findOrFail($validated['officer_id']);
            $role = Role::findOrFail($validated['role_id']);
            
            // Get or create user for officer
            $user = $officer->user;
            if (!$user) {
                // Create user if doesn't exist
                $user = User::create([
                    'email' => $officer->email ?? $officer->service_number . '@ncs.local',
                    'password' => Hash::make('password'), // Default password, should be changed
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
                
                // Link user to officer
                $officer->update(['user_id' => $user->id]);
            }

            // Get Officer role ID to exclude it
            $officerRole = Role::where('name', 'Officer')->first();
            $officerRoleId = $officerRole ? $officerRole->id : null;

            // Check if user has any active roles (excluding Officer)
            $otherActiveRoles = $user->roles()
                ->wherePivot('is_active', true)
                ->where('roles.id', '!=', $role->id);

            if ($officerRoleId) {
                $otherActiveRoles->where('roles.id', '!=', $officerRoleId);
            }

            $hasOtherActiveRoles = $otherActiveRoles->exists();

            // If user has other active roles and confirmation not provided, return error
            if ($hasOtherActiveRoles && !($validated['confirm_override'] ?? false)) {
                $existingRoles = $user->roles()
                    ->wherePivot('is_active', true)
                    ->where('roles.id', '!=', $role->id);

                if ($officerRoleId) {
                    $existingRoles->where('roles.id', '!=', $officerRoleId);
                }

                $existingRolesList = $existingRoles->get()->map(function($r) {
                    $command = $r->pivot->command_id ? Command::find($r->pivot->command_id) : null;
                    return [
                        'id' => $r->id,
                        'name' => $r->name,
                        'command_name' => $command ? $command->name : null,
                    ];
                })->values();

                return response()->json([
                    'requires_confirmation' => true,
                    'message' => 'User already has active roles. Please confirm to override.',
                    'existing_roles' => $existingRolesList
                ], 422);
            }

            $commandId = $validated['command_id'] ?? null;

            // Check if this officer already has this exact role for this command (avoid duplicate key)
            $existingPivotQuery = DB::table('user_roles')
                ->where('user_id', $user->id)
                ->where('role_id', $role->id);
            if ($commandId === null) {
                $existingPivotQuery->whereNull('command_id');
            } else {
                $existingPivotQuery->where('command_id', $commandId);
            }
            $existingPivot = $existingPivotQuery->first();

            if ($existingPivot && $existingPivot->is_active) {
                $commandName = $commandId ? (Command::find($commandId)?->name ?? 'this command') : 'this command';
                $message = "This officer already has the role \"{$role->name}\" for {$commandName}.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', $message)
                    ->withInput();
            }

            // Use database transaction to ensure consistency
            DB::beginTransaction();
            try {
                // If confirmation provided, deactivate other active roles
                if ($hasOtherActiveRoles && ($validated['confirm_override'] ?? false)) {
                    $this->deactivateOtherRoles($user, $role->id);
                }

                if ($existingPivot && !$existingPivot->is_active) {
                    // Reactivate existing assignment
                    DB::table('user_roles')->where('id', $existingPivot->id)->update([
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Attach new role
                    $user->roles()->attach($role->id, [
                        'command_id' => $commandId,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => true,
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Refresh user's roles relationship to clear cache
            $user->load(['roles' => function($query) {
                $query->wherePivot('is_active', true);
            }]);
            
            // Notify user about role assignment
            $command = $validated['command_id'] ? Command::find($validated['command_id']) : null;
            $commandName = $command ? $command->name : null;
            $notificationService = app(NotificationService::class);
            $notificationService->notifyRoleAssigned($user, $role->name, $commandName);
            
            // If this is the current logged-in user, log them out for security
            if (auth()->id() == $user->id) {
                // Log out the user and invalidate their session
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Your role has been updated. Please log in again.',
                        'redirect' => route('login')
                    ]);
                }
                
                return redirect()->route('login')
                    ->with('info', 'Your role has been updated. Please log in again.');
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Role '{$role->name}' assigned successfully to {$officer->surname} {$officer->initials}",
                    'redirect' => route('hrd.role-assignments')
                ]);
            }

            return redirect()->route('hrd.role-assignments')
                ->with('success', "Role '{$role->name}' assigned successfully to {$officer->surname} {$officer->initials}");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $isDuplicateRole = (
                $e->getCode() === '23000' ||
                str_contains($msg, '23000') ||
                (str_contains($msg, 'Duplicate entry') && str_contains($msg, 'user_roles_user_id_role_id_command_id_unique'))
            );
            if ($isDuplicateRole) {
                $message = 'This officer already has this role for this command.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', $message)
                    ->withInput();
            }

            \Log::error('Role assignment error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign role: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to assign role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update role assignment (change role, command or deactivate)
     */
    public function update(Request $request, $userId, $roleId)
    {
        $user = User::findOrFail($userId);
        $oldRole = Role::findOrFail($roleId);
        $officer = $user->officer;

        // Get command-based roles
        $commandBasedRoles = [
            'Assessor',
            'Validator',
            'Staff Officer',
            'Area Controller',
            'DC Admin',
            'Building Unit',
            'Admin'
        ];

        // Validate request
        $validated = $request->validate([
            'role_id' => [
                'required',
                'exists:roles,id',
            ],
            'command_id' => [
                'nullable',
                'exists:commands,id',
                function ($attribute, $value, $fail) use ($request, $officer, $commandBasedRoles) {
                    $newRole = Role::find($request->role_id);
                    if ($newRole && in_array($newRole->name, $commandBasedRoles)) {
                        if (!$value) {
                            $fail('Command assignment is required for ' . $newRole->name . ' role.');
                        } elseif ($officer && $officer->present_station && $value != $officer->present_station) {
                            $fail('Command must match the officer\'s assigned command (' . ($officer->presentStation->name ?? 'N/A') . ').');
                        }
                    }
                },
            ],
            'is_active' => 'boolean',
        ]);

        $newRole = Role::findOrFail($validated['role_id']);

        // Get Officer role ID to preserve it (Officer role should always remain active)
        $officerRole = Role::where('name', 'Officer')->first();
        $officerRoleId = $officerRole ? $officerRole->id : null;

        // Use database transaction to ensure consistency
        DB::beginTransaction();
        try {
            // If role is changing, deactivate ALL old active roles first
            if ($newRole->id != $oldRole->id) {
                // Deactivate the specific old role being changed (unless it's Officer role)
                if ($roleId != $officerRoleId) {
                    $user->roles()->updateExistingPivot($roleId, [
                        'is_active' => false,
                    ]);
                }
                
                // Also deactivate any other active roles (to ensure only one active role)
                // This prevents issues if user somehow has multiple active roles
                // EXCEPT the Officer role which should always remain active
                $otherActiveRoles = $user->roles()
                    ->wherePivot('is_active', true)
                    ->where('roles.id', '!=', $newRole->id);
                
                // Exclude Officer role from deactivation
                if ($officerRoleId) {
                    $otherActiveRoles->where('roles.id', '!=', $officerRoleId);
                }
                
                $otherActiveRoles = $otherActiveRoles->get();
                
                foreach ($otherActiveRoles as $role) {
                    $user->roles()->updateExistingPivot($role->id, [
                        'is_active' => false,
                    ]);
                }

                // Check if user already has this new role (could be inactive)
                $existingRole = $user->roles()
                    ->where('roles.id', $newRole->id)
                    ->first();

                if ($existingRole) {
                    // Reactivate and update existing role
                    $user->roles()->updateExistingPivot($newRole->id, [
                        'command_id' => $validated['command_id'] ?? null,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => $validated['is_active'] ?? true,
                    ]);
                } else {
                    // Attach new role
                    $user->roles()->attach($newRole->id, [
                        'command_id' => $validated['command_id'] ?? null,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => $validated['is_active'] ?? true,
                    ]);
                }
            } else {
                // Same role, just update command and status
                // But still deactivate any other active roles to ensure only one active
                // EXCEPT the Officer role which should always remain active
                $otherActiveRoles = $user->roles()
                    ->wherePivot('is_active', true)
                    ->where('roles.id', '!=', $roleId);
                
                // Exclude Officer role from deactivation
                if ($officerRoleId) {
                    $otherActiveRoles->where('roles.id', '!=', $officerRoleId);
                }
                
                $otherActiveRoles = $otherActiveRoles->get();
                
                foreach ($otherActiveRoles as $role) {
                    $user->roles()->updateExistingPivot($role->id, [
                        'is_active' => false,
                    ]);
                }
                
                $user->roles()->updateExistingPivot($roleId, [
                    'command_id' => $validated['command_id'] ?? null,
                    'is_active' => $validated['is_active'] ?? true,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update role: ' . $e->getMessage())
                ->withInput();
        }

        // Clear all cached relationships and reload fresh data
        $user->unsetRelation('roles');
        $user->refresh();
        
        // Reload roles with fresh data from database
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);
        
        // If this is the current logged-in user, log them out for security
        if (auth()->id() == $user->id) {
            // Log out the user and invalidate their session
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('info', 'Your role has been updated. Please log in again.');
        }

        return redirect()->route('hrd.role-assignments')
            ->with('success', "Role assignment updated successfully");
    }

    /**
     * Remove role from user
     */
    public function destroy($userId, $roleId)
    {
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);

        // Get Officer role ID - Officer role should never be deactivated
        $officerRole = Role::where('name', 'Officer')->first();
        $officerRoleId = $officerRole ? $officerRole->id : null;

        // Prevent deactivating the Officer role
        if ($roleId == $officerRoleId) {
            return redirect()->back()
                ->with('error', 'Cannot remove the Officer role. This role must always remain active.');
        }

        // Deactivate instead of deleting
        $user->roles()->updateExistingPivot($roleId, [
            'is_active' => false,
        ]);

        // Refresh user's roles relationship to clear cache
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);
        
        // If this is the current logged-in user, log them out for security
        if (auth()->id() == $user->id) {
            // Log out the user and invalidate their session
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('info', 'Your role has been removed. Please log in again.');
        }

        return redirect()->route('hrd.role-assignments')
            ->with('success', "Role '{$role->name}' removed from user");
    }
}


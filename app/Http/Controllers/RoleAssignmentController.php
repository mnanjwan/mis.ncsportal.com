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
                  ->orWhere('first_name', 'like', "%{$search}%")
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
        $request->validate([
            'command_id' => 'required|exists:commands,id'
        ]);

        $commandId = $request->command_id;

        // Query officers exactly like the Officers List does - simple and direct
        // Match the exact query pattern used in OfficerController@index
        $officers = Officer::with(['user', 'presentStation'])
            ->where('present_station', $commandId)
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get()
            ->map(function($officer) {
                return [
                    'id' => $officer->id,
                    'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '') . ' ' . ($officer->first_name ?? '')),
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A',
                ];
            });

        // Log for debugging (helps identify issues on live server)
        \Log::info('Officers by command query', [
            'command_id' => $commandId,
            'command_id_type' => gettype($commandId),
            'officers_count' => $officers->count(),
            'query_used' => 'present_station = ' . $commandId,
            'sample_officer' => $officers->first() ? [
                'id' => $officers->first()['id'],
                'name' => $officers->first()['name'],
                'present_station_from_db' => Officer::find($officers->first()['id'])?->present_station,
            ] : null,
        ]);

        return response()->json($officers);
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

            // Check if user already has this role
            $existingRole = $user->roles()
                ->where('roles.id', $role->id)
                ->wherePivot('is_active', true)
                ->first();

            if ($existingRole) {
                // Update existing role assignment
                $user->roles()->updateExistingPivot($role->id, [
                    'command_id' => $validated['command_id'] ?? null,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
            } else {
                // Attach new role
                $user->roles()->attach($role->id, [
                    'command_id' => $validated['command_id'] ?? null,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
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
                
                return redirect()->route('login')
                    ->with('info', 'Your role has been updated. Please log in again.');
            }

            return redirect()->route('hrd.role-assignments')
                ->with('success', "Role '{$role->name}' assigned successfully to {$officer->surname} {$officer->first_name}");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Role assignment error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
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

        // Use database transaction to ensure consistency
        DB::beginTransaction();
        try {
            // If role is changing, deactivate ALL old active roles first
            if ($newRole->id != $oldRole->id) {
                // Deactivate the specific old role being changed
                $user->roles()->updateExistingPivot($roleId, [
                    'is_active' => false,
                ]);
                
                // Also deactivate any other active roles (to ensure only one active role)
                // This prevents issues if user somehow has multiple active roles
                $otherActiveRoles = $user->roles()
                    ->wherePivot('is_active', true)
                    ->where('roles.id', '!=', $newRole->id)
                    ->get();
                
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
                $otherActiveRoles = $user->roles()
                    ->wherePivot('is_active', true)
                    ->where('roles.id', '!=', $roleId)
                    ->get();
                
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


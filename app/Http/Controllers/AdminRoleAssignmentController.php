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

class AdminRoleAssignmentController extends Controller
{
    /**
     * Get Admin's assigned command
     */
    private function getAdminCommand()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('Admin')) {
            abort(403, 'Unauthorized. Admin role required.');
        }
        
        $adminRole = $user->roles()
            ->where('name', 'Admin')
            ->wherePivot('is_active', true)
            ->first();
        
        if (!$adminRole || !$adminRole->pivot->command_id) {
            abort(403, 'Admin role not properly assigned to a command.');
        }
        
        return Command::findOrFail($adminRole->pivot->command_id);
    }

    /**
     * List role assignments for Admin's command
     */
    public function index(Request $request)
    {
        $adminCommand = $this->getAdminCommand();
        
        // Get the Officer role ID to exclude users who ONLY have Officer role
        $officerRole = Role::where('name', 'Officer')->first();
        $officerRoleId = $officerRole ? $officerRole->id : null;
        
        // Build query - get users with active roles in Admin's command, excluding those who ONLY have Officer role
        $query = User::query()
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.is_active', true)
            ->where('user_roles.command_id', $adminCommand->id)
            ->where('roles.name', '!=', 'Officer') // Exclude Officer role from the join
            ->select('users.*')
            ->distinct();

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('user_roles.role_id', $request->role_id);
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
                ->with(['roles' => function($q) use ($adminCommand) {
                    $q->wherePivot('is_active', true)
                      ->wherePivot('command_id', $adminCommand->id);
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

        // Get roles that Admin can assign (Staff Officer, Area Controller/Unit Head, DC Admin)
        $assignableRoles = Role::whereIn('name', [
            'Staff Officer',
            'Area Controller',
            'DC Admin'
        ])->orderBy('name')->get();

        return view('dashboards.admin.role-assignments', compact(
            'users',
            'assignableRoles',
            'adminCommand'
        ));
    }

    /**
     * Show form to assign role to officer
     */
    public function create()
    {
        $adminCommand = $this->getAdminCommand();
        
        // Get roles that Admin can assign
        $assignableRoles = Role::whereIn('name', [
            'Staff Officer',
            'Area Controller',
            'DC Admin'
        ])->orderBy('name')->get();

        // Get officers in Admin's command
        $officers = Officer::with(['user', 'presentStation'])
            ->where('present_station', $adminCommand->id)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        return view('dashboards.admin.assign-role', compact(
            'assignableRoles',
            'adminCommand',
            'officers'
        ));
    }

    /**
     * Store role assignment
     */
    public function store(Request $request)
    {
        try {
            $adminCommand = $this->getAdminCommand();
            
            $validated = $request->validate([
                'officer_id' => [
                    'required',
                    'exists:officers,id',
                    function ($attribute, $value, $fail) use ($adminCommand) {
                        $officer = Officer::find($value);
                        if ($officer && $officer->present_station != $adminCommand->id) {
                            $fail('Officer must be assigned to your command (' . $adminCommand->name . ').');
                        }
                    },
                ],
                'role_id' => [
                    'required',
                    'exists:roles,id',
                    function ($attribute, $value, $fail) {
                        $role = Role::find($value);
                        $assignableRoles = ['Staff Officer', 'Area Controller', 'DC Admin'];
                        if ($role && !in_array($role->name, $assignableRoles)) {
                            $fail('You can only assign: Staff Officer, Area Controller, or DC Admin roles.');
                        }
                    },
                ],
            ]);

            $officer = Officer::findOrFail($validated['officer_id']);
            $role = Role::findOrFail($validated['role_id']);
            
            // Verify officer is in Admin's command
            if ($officer->present_station != $adminCommand->id) {
                return redirect()->back()
                    ->with('error', 'Officer is not assigned to your command.')
                    ->withInput();
            }
            
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

            // Check if user already has this role for this command
            $existingRole = $user->roles()
                ->where('roles.id', $role->id)
                ->wherePivot('command_id', $adminCommand->id)
                ->wherePivot('is_active', true)
                ->first();

            if ($existingRole) {
                // Update existing role assignment
                $user->roles()->updateExistingPivot($role->id, [
                    'command_id' => $adminCommand->id,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'is_active' => true,
                ], [
                    'command_id' => $adminCommand->id
                ]);
            } else {
                // Attach new role
                $user->roles()->attach($role->id, [
                    'command_id' => $adminCommand->id,
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
            $notificationService = app(NotificationService::class);
            $notificationService->notifyRoleAssigned($user, $role->name, $adminCommand->name);
            
            // If this is the current logged-in user, log them out for security
            if (auth()->id() == $user->id) {
                // Log out the user and invalidate their session
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('info', 'Your role has been updated. Please log in again.');
            }

            return redirect()->route('admin.role-assignments')
                ->with('success', "Role '{$role->name}' assigned successfully to {$officer->surname} {$officer->first_name}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Admin role assignment error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'admin_id' => auth()->id(),
                'command_id' => $adminCommand->id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to assign role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update role assignment
     */
    public function update(Request $request, $userId, $roleId)
    {
        $adminCommand = $this->getAdminCommand();
        $user = User::findOrFail($userId);
        $oldRole = Role::findOrFail($roleId);
        $officer = $user->officer;

        // Verify the role assignment belongs to Admin's command
        $existingAssignment = $user->roles()
            ->where('roles.id', $roleId)
            ->wherePivot('command_id', $adminCommand->id)
            ->first();
            
        if (!$existingAssignment) {
            return redirect()->back()
                ->with('error', 'Role assignment not found or does not belong to your command.');
        }

        // Validate request
        $validated = $request->validate([
            'role_id' => [
                'required',
                'exists:roles,id',
                function ($attribute, $value, $fail) {
                    $role = Role::find($value);
                    $assignableRoles = ['Staff Officer', 'Area Controller', 'DC Admin'];
                    if ($role && !in_array($role->name, $assignableRoles)) {
                        $fail('You can only assign: Staff Officer, Area Controller, or DC Admin roles.');
                    }
                },
            ],
            'is_active' => 'boolean',
        ]);

        $newRole = Role::findOrFail($validated['role_id']);

        // Use database transaction to ensure consistency
        DB::beginTransaction();
        try {
            // If role is changing, deactivate the old role assignment for this command
            if ($newRole->id != $oldRole->id) {
                // Deactivate the specific old role being changed for this command
                $user->roles()->wherePivot('command_id', $adminCommand->id)
                    ->updateExistingPivot($roleId, [
                        'is_active' => false,
                    ]);

                // Check if user already has this new role for this command (could be inactive)
                $existingRole = $user->roles()
                    ->where('roles.id', $newRole->id)
                    ->wherePivot('command_id', $adminCommand->id)
                    ->first();

                if ($existingRole) {
                    // Reactivate and update existing role
                    $user->roles()->wherePivot('command_id', $adminCommand->id)
                        ->updateExistingPivot($newRole->id, [
                            'command_id' => $adminCommand->id,
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                            'is_active' => $validated['is_active'] ?? true,
                        ]);
                } else {
                    // Attach new role
                    $user->roles()->attach($newRole->id, [
                        'command_id' => $adminCommand->id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => $validated['is_active'] ?? true,
                    ]);
                }
            } else {
                // Same role, just update status
                $user->roles()->wherePivot('command_id', $adminCommand->id)
                    ->updateExistingPivot($roleId, [
                        'is_active' => $validated['is_active'] ?? true,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin role assignment update error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'admin_id' => auth()->id(),
                'command_id' => $adminCommand->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update role: ' . $e->getMessage())
                ->withInput();
        }

        // Clear all cached relationships and reload fresh data
        $user->unsetRelation('roles');
        $user->refresh();
        
        // Reload roles with fresh data from database
        $user->load(['roles' => function($query) use ($adminCommand) {
            $query->wherePivot('is_active', true)
                  ->wherePivot('command_id', $adminCommand->id);
        }]);
        
        // Notify user about role assignment update
        $notificationService = app(NotificationService::class);
        $notificationService->notifyRoleAssigned($user, $newRole->name, $adminCommand->name);
        
        // If this is the current logged-in user, log them out for security
        if (auth()->id() == $user->id) {
            // Log out the user and invalidate their session
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('info', 'Your role has been updated. Please log in again.');
        }

        return redirect()->route('admin.role-assignments')
            ->with('success', "Role assignment updated successfully");
    }

    /**
     * Remove role from user
     */
    public function destroy($userId, $roleId)
    {
        $adminCommand = $this->getAdminCommand();
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);

        // Verify the role assignment belongs to Admin's command
        $existingAssignment = $user->roles()
            ->where('roles.id', $roleId)
            ->wherePivot('command_id', $adminCommand->id)
            ->first();
            
        if (!$existingAssignment) {
            return redirect()->back()
                ->with('error', 'Role assignment not found or does not belong to your command.');
        }

        // Deactivate instead of deleting
        $user->roles()->wherePivot('command_id', $adminCommand->id)
            ->updateExistingPivot($roleId, [
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

        return redirect()->route('admin.role-assignments')
            ->with('success', "Role '{$role->name}' removed from user");
    }
}


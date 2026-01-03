@extends('layouts.app')

@section('title', 'Role Assignments')
@section('page-title', 'Role Assignments Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Role Assignments</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Role Assignments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('hrd.role-assignments') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 w-full md:min-w-[250px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="kt-input w-full" 
                                       placeholder="Search by name, service number, or email...">
                            </div>
                        </div>

                        <!-- Role Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Role</label>
                            <select name="role_id" class="kt-input w-full">
                                <option value="">All Roles</option>
                                @foreach($allRoles as $role)
                                    <option value="{{ $role->id }}" {{ (string)request('role_id') === (string)$role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Command Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <select name="command_id" class="kt-input w-full">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ (string)request('command_id') === (string)$command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select name="status" class="kt-input w-full">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'role_id', 'command_id', 'status', 'sort_by', 'sort_order']))
                                <a href="{{ route('hrd.role-assignments') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(request('sort_by') || request('sort_order'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif
                </form>
            </div>
        </div>

        <!-- Role Assignments Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">All Role Assignments</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.role-assignments.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Assign Role
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Mobile scroll hint -->
                <div class="block md:hidden px-4 py-3 bg-muted/50 border-b border-border">
                    <div class="flex items-center gap-2 text-xs text-secondary-foreground">
                        <i class="ki-filled ki-arrow-left-right"></i>
                        <span>Swipe left to view more columns</span>
                    </div>
                </div>

                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Officer Details
                                        @if(request('sort_by') === 'name')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Service Number
                                        @if(request('sort_by') === 'service_number')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'role', 'sort_order' => request('sort_by') === 'role' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Role
                                        @if(request('sort_by') === 'role')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'command', 'sort_order' => request('sort_by') === 'command' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Command
                                        @if(request('sort_by') === 'command')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Status
                                        @if(request('sort_by') === 'status')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'assigned_at', 'sort_order' => request('sort_by') === 'assigned_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Assigned
                                        @if(request('sort_by') === 'assigned_at')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    // Filter out "Officer" role from display
                                    $displayRoles = $user->roles->filter(function($role) {
                                        return $role->name !== 'Officer';
                                    });
                                @endphp
                                @foreach($displayRoles as $role)
                                    @php
                                        $pivot = $role->pivot;
                                        $command = $pivot->command_id ? \App\Models\Command::find($pivot->command_id) : null;
                                        $officer = $user->officer;
                                        $initials = $officer->initials ?? '';
                                        $surname = $officer->surname ?? '';
                                        $fullName = trim("{$initials} {$surname}");
                                        $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                                    {{ $avatarInitials }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-foreground">{{ $fullName }}</div>
                                                    <div class="text-xs text-secondary-foreground">{{ $user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-primary kt-badge-sm">
                                                {{ $role->name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            @if($command)
                                                <span class="text-foreground">{{ $command->name }}</span>
                                            @else
                                                <span class="text-secondary-foreground italic">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $pivot->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $pivot->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $pivot->assigned_at ? \Carbon\Carbon::parse($pivot->assigned_at)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <div class="flex items-center justify-end gap-2">
                                                <button 
                                                    onclick="editAssignment({{ $user->id }}, {{ $role->id }}, '{{ $role->name }}', {{ $pivot->command_id ?? 'null' }}, {{ $pivot->is_active ? 'true' : 'false' }}, {{ in_array($role->name, $commandBasedRoles) ? 'true' : 'false' }}, {{ $officer->present_station ?? 'null' }})"
                                                    class="kt-btn kt-btn-sm kt-btn-ghost"
                                                    title="Edit Assignment">
                                                    <i class="ki-filled ki-notepad-edit"></i>
                                                </button>
                                                <form 
                                                    action="{{ route('hrd.role-assignments.destroy', [$user->id, $role->id]) }}" 
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to remove this role assignment?')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost text-danger" title="Remove Role">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground">No role assignments found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div class="kt-modal" data-kt-modal="true" id="editModal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Edit Role Assignment</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="kt-modal-body py-5 px-5">
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="editRoleId" class="kt-input w-full" required>
                                <option value="">Select Role</option>
                                @foreach($allRoles as $role)
                                    <option value="{{ $role->id }}" data-requires-command="{{ in_array($role->name, $commandBasedRoles) ? '1' : '0' }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="editCommandField">
                            <label class="block text-sm font-medium mb-1">Command <span class="text-danger">*</span></label>
                            <select name="command_id" id="editCommandId" class="kt-input w-full" required>
                                <option value="">Select Command</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}">{{ $command->name }} ({{ $command->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="editIsActive" value="1" class="kt-checkbox">
                                <span class="text-sm">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                        Cancel
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script>
        function editAssignment(userId, roleId, roleName, commandId, isActive, requiresCommand, officerCommandId) {
            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editCommandId').value = commandId || '';
            document.getElementById('editIsActive').checked = isActive;
            
            // Store original values for command filtering
            window.currentEditRequiresCommand = requiresCommand;
            window.currentEditOfficerCommandId = officerCommandId;
            window.currentEditCommandId = commandId;
            
            // Update command field visibility based on selected role
            toggleEditCommandRequirement();
            
            document.getElementById('editForm').action = `/hrd/role-assignments/${userId}/${roleId}`;
            
            // Show modal using kt-modal system
            const modal = document.getElementById('editModal');
            if (modal) {
                // Check if kt-modal system is available
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    // Fallback: show modal manually with backdrop blur
                    modal.style.display = 'flex';
                }
            }
        }
        
        function toggleEditCommandRequirement() {
            const roleSelect = document.getElementById('editRoleId');
            const commandField = document.getElementById('editCommandField');
            const commandSelect = document.getElementById('editCommandId');
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            const requiresCommand = selectedOption ? selectedOption.getAttribute('data-requires-command') === '1' : window.currentEditRequiresCommand;
            
            // Reset all options visibility first
            Array.from(commandSelect.options).forEach(option => {
                option.style.display = '';
            });
            
            if (requiresCommand) {
                commandField.style.display = 'block';
                commandSelect.required = true;
                
                // Filter commands to only show the officer's command if role is command-based
                if (window.currentEditOfficerCommandId && window.currentEditOfficerCommandId !== 'null') {
                    Array.from(commandSelect.options).forEach(option => {
                        if (option.value === '') return; // Keep placeholder
                        // Only show the officer's command
                        option.style.display = option.value == window.currentEditOfficerCommandId || option.value == window.currentEditCommandId ? '' : 'none';
                    });
                }
            } else {
                commandField.style.display = 'none';
                commandSelect.required = false;
            }
        }
        
        // Listen for role changes in edit modal
        document.getElementById('editRoleId')?.addEventListener('change', toggleEditCommandRequirement);
    </script>
@endsection
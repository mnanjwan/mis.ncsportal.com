@extends('layouts.app')

@section('title', 'Role Assignments')
@section('page-title', 'Role Assignments Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.dashboard') }}">Admin</a>
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Command Info -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information-2 text-info text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-info">Managing role assignments for: <strong>{{ $adminCommand->name }}</strong></p>
                        <p class="text-xs text-secondary-foreground mt-1">You can assign: Staff Officer, Area Controller, and DC Admin roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Role Assignments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('admin.role-assignments') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 w-full md:min-w-[250px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="kt-input w-full" 
                                       placeholder="Search by name or service number...">
                            </div>
                        </div>

                        <!-- Role Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Role</label>
                            <div class="relative">
                                <input type="hidden" name="role_id" id="filter_role_id" value="{{ request('role_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_role_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_role_select_text">{{ request('role_id') ? ($assignableRoles->firstWhere('id', request('role_id')) ? $assignableRoles->firstWhere('id', request('role_id'))->name : 'All Roles') : 'All Roles' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_role_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_role_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search roles..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_role_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="filter_status_id" value="{{ request('status') ?? '' }}">
                                <button type="button" 
                                        id="filter_status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_status_select_text">{{ request('status') ? ucfirst(request('status')) : 'All Status' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_status_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_status_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search status..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_status_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'role_id', 'status']))
                                <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Assignments Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Role Assignments</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('admin.role-assignments.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Assign Role
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-auto">
                <table class="kt-table w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Role</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Assigned</th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $displayRoles = $user->roles->filter(function($role) {
                                    return $role->name !== 'Officer';
                                });
                            @endphp
                            @foreach($displayRoles as $role)
                                @php
                                    $pivot = $role->pivot;
                                    $officer = $user->officer;
                                    $initials = $officer->initials ?? '';
                                    $surname = $officer->surname ?? '';
                                    $fullName = trim("{$initials} {$surname}");
                                    $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                                {{ $avatarInitials }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-foreground">{{ $fullName }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-primary kt-badge-sm">
                                            {{ $role->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $pivot->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                            {{ $pivot->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $pivot->assigned_at ? \Carbon\Carbon::parse($pivot->assigned_at)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                type="button"
                                                class="kt-btn kt-btn-sm kt-btn-ghost text-danger" 
                                                title="Remove Role"
                                                data-kt-modal-toggle="#delete-modal-{{ $user->id }}-{{ $role->id }}">
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                    <p class="text-secondary-foreground">No role assignments found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
    @foreach($users as $user)
        @php
            $displayRoles = $user->roles->filter(function($role) {
                return $role->name !== 'Officer';
            });
        @endphp
        @foreach($displayRoles as $role)
            @php
                $officer = $user->officer;
                $initials = $officer->initials ?? '';
                $surname = $officer->surname ?? '';
                $fullName = trim("{$initials} {$surname}");
            @endphp
            <div class="kt-modal" data-kt-modal="true" id="delete-modal-{{ $user->id }}-{{ $role->id }}">
                <div class="kt-modal-content max-w-[400px]">
                    <div class="kt-modal-header py-4 px-5">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                                <i class="ki-filled ki-information text-danger text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-foreground">Confirm Removal</h3>
                        </div>
                        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                            <i class="ki-filled ki-cross"></i>
                        </button>
                    </div>
                    <div class="kt-modal-body py-5 px-5">
                        <p class="text-sm text-secondary-foreground">
                            Are you sure you want to remove the role <strong>{{ $role->name }}</strong> from <strong>{{ $fullName }}</strong>?
                        </p>
                        <p class="text-xs text-secondary-foreground mt-2">
                            This action will deactivate the role assignment for this officer.
                        </p>
                    </div>
                    <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                        <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                            Cancel
                        </button>
                        <form action="{{ route('admin.role-assignments.destroy', [$user->id, $role->id]) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-trash"></i>
                                <span>Remove Role</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    <script>
        // Data for searchable selects
        @php
            $rolesData = $assignableRoles->map(function($role) {
                return ['id' => $role->id, 'name' => $role->name];
            })->values();
            $statusOptions = [
                ['id' => '', 'name' => 'All Status'],
                ['id' => 'active', 'name' => 'Active'],
                ['id' => 'inactive', 'name' => 'Inactive']
            ];
        @endphp
        const filterRoles = @json($rolesData);
        const filterStatuses = @json($statusOptions);

        // Reusable function to create searchable select
        function createSearchableSelect(config) {
            const {
                triggerId,
                hiddenInputId,
                dropdownId,
                searchInputId,
                optionsContainerId,
                displayTextId,
                options,
                displayFn,
                onSelect,
                placeholder = 'Select...',
                searchPlaceholder = 'Search...'
            } = config;

            const trigger = document.getElementById(triggerId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const dropdown = document.getElementById(dropdownId);
            const searchInput = document.getElementById(searchInputId);
            const optionsContainer = document.getElementById(optionsContainerId);
            const displayText = document.getElementById(displayTextId);

            let selectedOption = null;
            let filteredOptions = [...options];

            // Render options
            function renderOptions(opts) {
                if (opts.length === 0) {
                    optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                    return;
                }

                optionsContainer.innerHTML = opts.map(opt => {
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id);
                    const value = opt.id || opt.value || '';
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                             data-id="${value}" 
                             data-name="${display}">
                            <div class="text-sm text-foreground">${display}</div>
                        </div>
                    `;
                }).join('');

                // Add click handlers
                optionsContainer.querySelectorAll('.select-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        selectedOption = options.find(o => (o.id || o.value || '') == id);
                        
                        if (selectedOption || id === '') {
                            hiddenInput.value = id;
                            displayText.textContent = name;
                            dropdown.classList.add('hidden');
                            searchInput.value = '';
                            filteredOptions = [...options];
                            renderOptions(filteredOptions);
                            
                            if (onSelect) onSelect(selectedOption || {id: id, name: name});
                        }
                    });
                });
            }

            // Initial render
            renderOptions(filteredOptions);

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filteredOptions = options.filter(opt => {
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id || '');
                    return display.toLowerCase().includes(searchTerm);
                });
                renderOptions(filteredOptions);
            });

            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden')) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Initialize filter selects
        document.addEventListener('DOMContentLoaded', function() {
            // Filter Role Select
            createSearchableSelect({
                triggerId: 'filter_role_select_trigger',
                hiddenInputId: 'filter_role_id',
                dropdownId: 'filter_role_dropdown',
                searchInputId: 'filter_role_search_input',
                optionsContainerId: 'filter_role_options',
                displayTextId: 'filter_role_select_text',
                options: [{id: '', name: 'All Roles'}, ...filterRoles],
                placeholder: 'All Roles',
                searchPlaceholder: 'Search roles...'
            });

            // Filter Status Select
            createSearchableSelect({
                triggerId: 'filter_status_select_trigger',
                hiddenInputId: 'filter_status_id',
                dropdownId: 'filter_status_dropdown',
                searchInputId: 'filter_status_search_input',
                optionsContainerId: 'filter_status_options',
                displayTextId: 'filter_status_select_text',
                options: filterStatuses,
                placeholder: 'All Status',
                searchPlaceholder: 'Search status...'
            });
        });
    </script>
@endsection


@extends('layouts.app')

@section('title', 'Assign Role')
@section('page-title', 'Assign Role to Officer')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.dashboard') }}">Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('admin.role-assignments') }}">Role Assignments</a>
    <span>/</span>
    <span class="text-primary">Assign Role</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('error'))
            <div class="kt-alert kt-alert-error flex items-center gap-3 p-4 rounded-lg">
                <i class="ki-filled ki-information-2 text-danger text-xl"></i>
                <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
            </div>
        @endif
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Assign Role to Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('admin.role-assignments.store') }}" method="POST" class="flex flex-col gap-5">
                    @csrf

                    <!-- Information Banner -->
                    <div class="bg-info/10 border border-info/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information-2 text-info text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-info mb-2">Role Assignment Guidelines</h4>
                                <ul class="text-sm text-secondary-foreground space-y-1 list-disc list-inside">
                                    <li>You can assign roles to officers in your command: <strong>{{ $adminCommand->name }}</strong></li>
                                    <li>You can assign the following roles: <strong>Staff Officer</strong>, <strong>Area Controller</strong>, and <strong>DC Admin</strong></li>
                                    <li>All role assignments are specific to your command</li>
                                    <li>The officer will receive email and app notifications when assigned</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Command Display (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Command</label>
                        <input type="text" 
                               value="{{ $adminCommand->name }}" 
                               class="kt-input w-full bg-muted/50" 
                               readonly>
                        <p class="text-xs text-secondary-foreground mt-1">You can only assign roles within your assigned command</p>
                    </div>

                    <!-- Officer Selection (Searchable Select) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Officer <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="officer_id" id="officer_id" required>
                            <button type="button" 
                                    id="officer_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="officer_select_text">Select Officer</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="officer_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                        <input type="text" 
                                               id="officer_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search officers..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="officer_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1" id="officer_info"></p>
                        @error('officer_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Selection (Searchable Select) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Role <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="role_id" id="role_id" required>
                            <button type="button" 
                                    id="role_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="role_select_text">Select Role</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="role_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="role_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search roles..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="role_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        @error('role_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">Select a role to assign</p>
                    </div>

                    <!-- Hidden field for confirmation -->
                    <input type="hidden" name="confirm_override" id="confirm_override" value="0">

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                        <a href="{{ route('admin.role-assignments') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary" id="submit-btn">
                            <i class="ki-filled ki-check"></i> Assign Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="role-override-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <i class="ki-filled ki-information-2 text-warning text-2xl"></i>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Role Override</h3>
                </div>
                <p class="text-sm text-secondary-foreground mb-4">
                    This officer already has the following active role(s):
                </p>
                <ul id="existing-roles-list" class="list-disc list-inside mb-4 text-sm text-foreground space-y-1">
                    <!-- Existing roles will be populated here -->
                </ul>
                <p class="text-sm text-secondary-foreground mb-4">
                    Assigning a new role will <strong>replace</strong> the existing role(s). Do you want to continue?
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button" id="cancel-override-btn" class="kt-btn kt-btn-outline">
                        Cancel
                    </button>
                    <button type="button" id="confirm-override-btn" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Confirm & Assign
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Officers data
        @php
            $officersData = $officers->map(function($officer) {
                // Match HRD version name structure
                $name = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                return [
                    'id' => $officer->id,
                    'name' => $name,
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->display_rank ?? 'N/A'
                ];
            })->values();
            $rolesData = $assignableRoles->map(function($role) {
                return ['id' => $role->id, 'name' => $role->name];
            })->values();
        @endphp
        const officers = @json($officersData);
        const roles = @json($rolesData);
        
        // Initialize officer options
        function renderOfficerOptions(officersList) {
            const officerOptions = document.getElementById('officer_options');
            
            if (officersList.length === 0) {
                officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                return;
            }
            
            officerOptions.innerHTML = officersList.map(officer => {
                const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                const rank = officer.rank !== 'N/A' && officer.rank ? ' - ' + officer.rank : '';
                const displayText = officer.name + (details ? ' (' + details + rank + ')' : '');
                
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                         data-id="${officer.id}" 
                         data-name="${officer.name}"
                         data-service="${details}"
                         data-rank="${officer.rank}">
                        <div class="text-sm text-foreground">${officer.name}</div>
                        <div class="text-xs text-secondary-foreground">${details}${rank}</div>
                    </div>
                `;
            }).join('');
            
            // Add click handlers
            officerOptions.querySelectorAll('.officer-option').forEach(option => {
                option.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const service = this.dataset.service;
                    const rank = this.dataset.rank;
                    
                    // Update hidden input
                    document.getElementById('officer_id').value = id;
                    
                    // Update display text
                    const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' && rank ? ' - ' + rank : '') + ')' : '');
                    document.getElementById('officer_select_text').textContent = displayText;
                    
                    // Close dropdown
                    document.getElementById('officer_dropdown').classList.add('hidden');
                    
                    // Clear search
                    document.getElementById('officer_search_input').value = '';
                });
            });
        }

        // Setup officer search
        function setupOfficerSearch() {
            const searchInput = document.getElementById('officer_search_input');
            const officerOptions = document.getElementById('officer_options');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = officers.filter(officer => {
                    const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                    const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                    const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
                    return nameMatch || serviceMatch || rankMatch;
                });
                
                renderOfficerOptions(filtered);
            });
        }

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
                            displayText.textContent = name || placeholder;
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Render initial officer options
            renderOfficerOptions(officers);
            
            // Setup officer search
            setupOfficerSearch();
            
            // Initialize role searchable select
            createSearchableSelect({
                triggerId: 'role_select_trigger',
                hiddenInputId: 'role_id',
                dropdownId: 'role_dropdown',
                searchInputId: 'role_search_input',
                optionsContainerId: 'role_options',
                displayTextId: 'role_select_text',
                options: roles,
                placeholder: 'Select Role',
                searchPlaceholder: 'Search roles...'
            });
            
            // Update info text
            const officerInfo = document.getElementById('officer_info');
            if (officers.length > 0) {
                officerInfo.textContent = `${officers.length} officer${officers.length !== 1 ? 's' : ''} available`;
            } else {
                officerInfo.textContent = 'No officers found in this command.';
                officerInfo.classList.add('text-danger');
            }
        });

        // Toggle dropdown
        document.getElementById('officer_select_trigger')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('officer_dropdown');
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                // Focus search input when dropdown opens
                setTimeout(() => {
                    document.getElementById('officer_search_input').focus();
                }, 100);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('officer_dropdown');
            const trigger = document.getElementById('officer_select_trigger');
            if (dropdown && !dropdown.contains(e.target) && !trigger.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Form submission handler with role override confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="{{ route('admin.role-assignments.store') }}"]');
            const submitBtn = document.getElementById('submit-btn');
            const confirmOverrideInput = document.getElementById('confirm_override');
            const modal = document.getElementById('role-override-modal');
            const cancelBtn = document.getElementById('cancel-override-btn');
            const confirmBtn = document.getElementById('confirm-override-btn');
            const existingRolesList = document.getElementById('existing-roles-list');
            let pendingSubmit = false;

            async function handleFormSubmit(e) {
                // If already confirmed, submit via fetch to handle JSON responses
                if (confirmOverrideInput.value === '1') {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const submitBtn = document.getElementById('submit-btn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Assigning...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = '{{ route('admin.role-assignments') }}';
                            }
                        } else {
                            alert(data.message || 'Failed to assign role');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="ki-filled ki-check"></i> Assign Role';
                        }
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('An error occurred. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="ki-filled ki-check"></i> Assign Role';
                    }
                    return;
                }

                e.preventDefault();

                const officerId = document.getElementById('officer_id').value;
                const roleId = document.getElementById('role_id').value;

                // Validate required fields
                if (!officerId || !roleId) {
                    // Submit normally if validation fails (let server handle it)
                    form.removeEventListener('submit', handleFormSubmit);
                    form.submit();
                    return;
                }

                // Check for existing roles
                try {
                    const response = await fetch(`{{ route('admin.role-assignments.check-existing-roles') }}?officer_id=${officerId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.has_existing_roles && data.existing_roles.length > 0) {
                        // Show confirmation modal
                        existingRolesList.innerHTML = data.existing_roles.map(role => {
                            const commandText = role.command_name ? ` (${role.command_name})` : '';
                            return `<li>${role.name}${commandText}</li>`;
                        }).join('');

                        modal.classList.remove('hidden');
                        pendingSubmit = true;
                    } else {
                        // No existing roles, submit normally
                        form.removeEventListener('submit', handleFormSubmit);
                        form.submit();
                    }
                } catch (error) {
                    console.error('Error checking existing roles:', error);
                    // On error, submit normally
                    form.removeEventListener('submit', handleFormSubmit);
                    form.submit();
                }
            }

            form.addEventListener('submit', handleFormSubmit);

            // Handle cancel button
            cancelBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
                pendingSubmit = false;
            });

            // Handle confirm button
            confirmBtn.addEventListener('click', async function() {
                confirmOverrideInput.value = '1';
                modal.classList.add('hidden');
                
                // Submit form with AJAX to handle JSON responses
                const formData = new FormData(form);
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Assigning...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '{{ route('admin.role-assignments') }}';
                        }
                    } else {
                        alert(data.message || 'Failed to assign role');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="ki-filled ki-check"></i> Assign Role';
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                    // Fallback to normal form submission
                    form.submit();
                }
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    pendingSubmit = false;
                }
            });
        });
    </script>
    @endpush
@endsection


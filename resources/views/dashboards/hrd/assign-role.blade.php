@extends('layouts.app')

@section('title', 'Assign Role')
@section('page-title', 'Assign Role to Officer')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.role-assignments') }}">Role Assignments</a>
    <span>/</span>
    <span class="text-primary">Assign Role</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Assign Role to Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.role-assignments.store') }}" method="POST" class="flex flex-col gap-5">
                    @csrf

                    <!-- Information Banner -->
                    <div class="bg-info/10 border border-info/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information-2 text-info text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-info mb-2">Role Assignment Guidelines</h4>
                                <ul class="text-sm text-secondary-foreground space-y-1 list-disc list-inside">
                                    <li><strong>Command-Based Roles</strong> require a command assignment: 
                                        @foreach(($commandBasedRoles ?? ['Assessor', 'Validator', 'Staff Officer', 'Area Controller', 'DC Admin', 'Building Unit']) as $role)
                                            <span class="font-medium">{{ $role }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </li>
                                    <li><strong>Independent Roles</strong> don't need a command: HRD, Establishment, Accounts, Board, Welfare, Investigation Unit, Officer</li>
                                    <li>Select a command first to see officers in that command</li>
                                    <li>If an officer doesn't have a user account, one will be created automatically</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Command Selection (Searchable) -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Command <span id="command_required_indicator" class="text-danger hidden">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="command_search" 
                                   class="kt-input w-full" 
                                   placeholder="Search command..."
                                   autocomplete="off">
                            <input type="hidden" 
                                   name="command_id" 
                                   id="command_id">
                            <div id="command_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                        <div id="selected_command" class="mt-2 p-2 bg-muted/50 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium" id="selected_command_name"></span>
                                <button type="button" 
                                        id="clear_command" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                        @error('command_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">Select a command to view officers in that command</p>
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
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer"
                                    disabled>
                                <span id="officer_select_text">Select command first, then choose officer...</span>
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
                                    <div class="p-3 text-sm text-secondary-foreground text-center">
                                        Select command first
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-1" id="officer_info"></p>
                        @error('officer_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select name="role_id" id="role_id" class="kt-input w-full" required onchange="toggleCommandRequirement()">
                            <option value="">Select Role</option>
                            @foreach($allRoles as $role)
                                <option value="{{ $role->id }}" 
                                        data-requires-command="{{ in_array($role->name, $commandBasedRoles ?? []) ? '1' : '0' }}">
                                    {{ $role->name }}
                                    @if(in_array($role->name, $commandBasedRoles ?? []))
                                        (Requires Command)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-secondary-foreground mt-1" id="role_info"></p>
                        @error('role_id')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Assign Role
                        </button>
                        <a href="{{ route('hrd.role-assignments') }}" class="kt-btn kt-btn-outline">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Commands data
        @php
            $commandsData = $commands->map(function($command) {
                return [
                    'id' => $command->id,
                    'name' => $command->name,
                    'code' => $command->code ?? ''
                ];
            })->values();
        @endphp
        const commands = @json($commandsData);
        let officers = [];

        // Searchable Select Helper Function
        function createSearchableSelect(searchInput, hiddenInput, dropdown, selectedDiv, selectedName, options, onSelect, displayFn) {
            let selectedOption = null;

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = options.filter(opt => {
                    if (displayFn) {
                        return displayFn(opt).toLowerCase().includes(searchTerm);
                    }
                    const nameMatch = opt.name && opt.name.toLowerCase().includes(searchTerm);
                    const codeMatch = opt.code && opt.code.toLowerCase().includes(searchTerm);
                    return nameMatch || codeMatch;
                });

                if (filtered.length > 0 && searchTerm.length > 0) {
                    dropdown.innerHTML = filtered.map(opt => {
                        const display = displayFn ? displayFn(opt) : (opt.name + (opt.code ? ' (' + opt.code + ')' : ''));
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                                    'data-id="' + opt.id + '" ' +
                                    'data-name="' + opt.name + '">' +
                                    display +
                                '</div>';
                    }).join('');
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            });

            dropdown.addEventListener('click', function(e) {
                const option = e.target.closest('[data-id]');
                if (option) {
                    selectedOption = options.find(o => o.id == option.dataset.id);
                    if (selectedOption) {
                        hiddenInput.value = selectedOption.id;
                        const display = displayFn ? displayFn(selectedOption) : (selectedOption.name + (selectedOption.code ? ' (' + selectedOption.code + ')' : ''));
                        searchInput.value = display;
                        if (selectedName) {
                            selectedDiv.querySelector(selectedName).textContent = display;
                        }
                        selectedDiv.classList.remove('hidden');
                        dropdown.classList.add('hidden');
                        if (onSelect) onSelect(selectedOption);
                    }
                }
            });

            // Clear selection
            const clearBtn = selectedDiv.querySelector('button');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    hiddenInput.value = '';
                    searchInput.value = '';
                    selectedDiv.classList.add('hidden');
                    selectedOption = null;
                    if (onSelect) onSelect(null);
                });
            }

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // Initialize Command Searchable Select
        createSearchableSelect(
            document.getElementById('command_search'),
            document.getElementById('command_id'),
            document.getElementById('command_dropdown'),
            document.getElementById('selected_command'),
            '#selected_command_name',
            commands,
            function(selectedCommand) {
                if (selectedCommand) {
                    // Load officers for this command
                    loadOfficersByCommand(selectedCommand.id);
                } else {
                    // Clear officers
                    clearOfficers();
                }
            },
            function(cmd) {
                return cmd.name + (cmd.code ? ' (' + cmd.code + ')' : '');
            }
        );

        // Load officers by command
        function loadOfficersByCommand(commandId) {
            const officerSelectTrigger = document.getElementById('officer_select_trigger');
            const officerSelectText = document.getElementById('officer_select_text');
            const officerHiddenInput = document.getElementById('officer_id');
            const officerDropdown = document.getElementById('officer_dropdown');
            const officerOptions = document.getElementById('officer_options');
            const officerSearchInput = document.getElementById('officer_search_input');
            const officerInfo = document.getElementById('officer_info');
            
            // Update UI to loading state
            officerSelectText.textContent = 'Loading officers...';
            officerSelectTrigger.disabled = true;
            officerHiddenInput.value = '';
            officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Loading...</div>';

            fetch(`{{ route('hrd.role-assignments.officers-by-command') }}?command_id=${commandId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                officers = data;
                
                if (data.length > 0) {
                    // Populate options
                    renderOfficerOptions(data);
                    
                    officerInfo.textContent = `${data.length} officer${data.length !== 1 ? 's' : ''} available`;
                    officerInfo.classList.remove('text-danger');
                    officerInfo.classList.add('text-secondary-foreground');
                } else {
                    officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found in this command.</div>';
                    officerInfo.textContent = 'No officers found in this command.';
                    officerInfo.classList.remove('text-secondary-foreground');
                    officerInfo.classList.add('text-danger');
                }
                
                officerSelectTrigger.disabled = false;
                officerSelectText.textContent = 'Select Officer';
                
                // Setup search functionality
                setupOfficerSearch();
            })
            .catch(error => {
                console.error('Error loading officers:', error);
                officerOptions.innerHTML = '<div class="p-3 text-sm text-danger text-center">Error loading officers</div>';
                officerInfo.textContent = 'Error loading officers. Please try again.';
                officerInfo.classList.remove('text-secondary-foreground');
                officerInfo.classList.add('text-danger');
                officerSelectTrigger.disabled = false;
                officerSelectText.textContent = 'Error loading officers';
            });
        }

        function renderOfficerOptions(officersList) {
            const officerOptions = document.getElementById('officer_options');
            
            if (officersList.length === 0) {
                officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                return;
            }
            
            officerOptions.innerHTML = officersList.map(officer => {
                const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                const rank = officer.rank !== 'N/A' ? ' - ' + officer.rank : '';
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
                    const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
                    document.getElementById('officer_select_text').textContent = displayText;
                    
                    // Close dropdown
                    document.getElementById('officer_dropdown').classList.add('hidden');
                    
                    // Clear search
                    document.getElementById('officer_search_input').value = '';
                });
            });
        }

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

        function clearOfficers() {
            const officerSelectTrigger = document.getElementById('officer_select_trigger');
            const officerSelectText = document.getElementById('officer_select_text');
            const officerHiddenInput = document.getElementById('officer_id');
            const officerDropdown = document.getElementById('officer_dropdown');
            const officerOptions = document.getElementById('officer_options');
            const officerInfo = document.getElementById('officer_info');
            
            officerSelectText.textContent = 'Select command first, then choose officer...';
            officerSelectTrigger.disabled = true;
            officerHiddenInput.value = '';
            officerDropdown.classList.add('hidden');
            officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select command first</div>';
            officerInfo.textContent = '';
            officers = [];
        }

        // Toggle dropdown
        document.getElementById('officer_select_trigger')?.addEventListener('click', function(e) {
            if (this.disabled) return;
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

        // Toggle command requirement based on selected role
        function toggleCommandRequirement() {
            const roleSelect = document.getElementById('role_id');
            const roleInfo = document.getElementById('role_info');
            const commandRequiredIndicator = document.getElementById('command_required_indicator');
            const commandIdInput = document.getElementById('command_id');
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const requiresCommand = selectedOption.getAttribute('data-requires-command') === '1';
                if (requiresCommand) {
                    roleInfo.textContent = 'This role requires a command assignment.';
                    roleInfo.classList.remove('text-secondary-foreground');
                    roleInfo.classList.add('text-info');
                    if (commandRequiredIndicator) commandRequiredIndicator.classList.remove('hidden');
                    if (commandIdInput) commandIdInput.setAttribute('required', 'required');
                } else {
                    roleInfo.textContent = 'This role is independent (no command needed).';
                    roleInfo.classList.remove('text-info');
                    roleInfo.classList.add('text-secondary-foreground');
                    if (commandRequiredIndicator) commandRequiredIndicator.classList.add('hidden');
                    if (commandIdInput) commandIdInput.removeAttribute('required');
                }
            } else {
                roleInfo.textContent = '';
                if (commandRequiredIndicator) commandRequiredIndicator.classList.add('hidden');
                if (commandIdInput) commandIdInput.removeAttribute('required');
            }
        }
    </script>
    @endpush
@endsection

@extends('layouts.app')

@section('title', 'Create Internal Staff Order')
@section('page-title', 'Create Internal Staff Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.internal-staff-orders.index') }}">Internal Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Internal Staff Orders
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Create Internal Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('staff-officer.internal-staff-orders.store') }}" method="POST" id="internalStaffOrderForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Command Info (Read-only) -->
                        @if($command)
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">Command</label>
                                <input type="text" 
                                       class="kt-input" 
                                       value="{{ $command->name }}" 
                                       readonly>
                                <span class="text-xs text-secondary-foreground">Internal staff orders are created for your assigned command.</span>
                            </div>
                        @endif

                        <!-- Order Number (Auto-generated, but editable) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number <span class="text-danger">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       name="order_number" 
                                       id="order_number"
                                       class="kt-input flex-1" 
                                       value="{{ old('order_number', $orderNumber) }}"
                                       placeholder="Auto-generated order number"
                                       readonly
                                       required>
                                <button type="button" 
                                        id="edit-order-number"
                                        class="kt-btn kt-btn-sm kt-btn-ghost"
                                        title="Edit order number">
                                    <i class="ki-filled ki-pencil"></i>
                                </button>
                            </div>
                            <span class="text-xs text-secondary-foreground">Order number is auto-generated. Click edit to customize.</span>
                            @error('order_number')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Order Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="order_date" 
                                   id="order_date"
                                   class="kt-input" 
                                   value="{{ old('order_date', date('Y-m-d')) }}"
                                   required>
                            @error('order_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Officer Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Officer <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id') }}" required>
                                <button type="button" 
                                        id="officer_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('officer_id') border-danger @enderror">
                                    <span id="officer_select_text">
                                        @if(old('officer_id'))
                                            @php $selectedOfficer = $officers->find(old('officer_id')); @endphp
                                            {{ $selectedOfficer ? $selectedOfficer->initials . ' ' . $selectedOfficer->surname . ' (' . $selectedOfficer->service_number . ')' : 'Select an officer...' }}
                                        @else
                                            Select an officer...
                                        @endif
                                    </span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="officer_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="officer_search_input" 
                                                   class="kt-input w-full" 
                                                   placeholder="Search officers..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="officer_options" class="max-h-60 overflow-y-auto">
                                        @foreach($officers as $officer)
                                            <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                                                 data-id="{{ $officer->id }}" 
                                                 data-name="{{ $officer->initials }} {{ $officer->surname }}"
                                                 data-service="{{ $officer->service_number }}"
                                                 data-rank="{{ $officer->substantive_rank }}">
                                                <div class="text-sm text-foreground font-medium">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $officer->service_number }} - {{ $officer->substantive_rank ?? 'N/A' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground">Select the officer for this internal staff order.</span>
                            @error('officer_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Current Assignment (Auto-displayed) -->
                        <div id="current-assignment-section" class="hidden">
                            <div class="kt-card bg-muted/50">
                                <div class="kt-card-content p-4">
                                    <h4 class="text-sm font-semibold mb-3">Current Assignment (from Active Duty Roster)</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-secondary-foreground">Current Command</label>
                                            <p class="text-sm font-medium" id="current-command">-</p>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-secondary-foreground">Current Unit</label>
                                            <p class="text-sm font-medium" id="current-unit">-</p>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="text-xs text-secondary-foreground">Current Role</label>
                                            <p class="text-sm font-medium" id="current-role">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Target Unit Selection -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Target Unit <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="target_unit" id="target_unit" value="{{ old('target_unit') ?? '' }}" required>
                                <button type="button" 
                                        id="target_unit_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('target_unit') border-danger @enderror">
                                    <span id="target_unit_select_text">{{ old('target_unit') ? old('target_unit') : 'Select target unit...' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="target_unit_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="target_unit_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search units..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="target_unit_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground">Select the target unit for this transfer (must be in the same command).</span>
                            @error('target_unit')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Target Role Selection -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Target Role <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="target_role" id="target_role" value="{{ old('target_role') ?? '' }}" required>
                                <button type="button" 
                                        id="target_role_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('target_role') border-danger @enderror">
                                    <span id="target_role_select_text">{{ old('target_role') ? (old('target_role') === 'Member' ? 'Member' : (old('target_role') === '2IC' ? '2IC (Second In Command)' : 'OIC (Officer In Charge)')) : 'Select target role...' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="target_role_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="target_role_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search roles..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="target_role_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground">Select the intended role in the target unit.</span>
                            @error('target_role')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Conflict Warning (Displayed when OIC/2IC conflict detected) -->
                        <div id="conflict-warning" class="hidden">
                            <div class="kt-card bg-warning/10 border border-warning/20">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-warning mb-2">Role Takeover Warning</h4>
                                            <p class="text-sm text-foreground mb-2" id="conflict-message"></p>
                                            <div id="conflict-details" class="text-xs text-secondary-foreground"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Description</label>
                            <textarea name="description" 
                                      id="description"
                                      class="kt-input" 
                                      rows="5"
                                      placeholder="Enter order description or details...">{{ old('description') }}</textarea>
                            <span class="text-xs text-secondary-foreground">Optional: Provide additional details about this internal staff order.</span>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-3 pt-4 border-t border-border">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Create Internal Staff Order
                            </button>
                            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-ghost">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Officers data
            @php
                $officersData = $officers->map(function($officer) {
                    return [
                        'id' => $officer->id,
                        'name' => $officer->initials . ' ' . $officer->surname,
                        'service_number' => $officer->service_number ?? 'N/A',
                        'rank' => $officer->substantive_rank ?? 'N/A'
                    ];
                })->values();
            @endphp
            const officers = @json($officersData);

            const officerSelectTrigger = document.getElementById('officer_select_trigger');
            const officerSelectText = document.getElementById('officer_select_text');
            const officerHiddenInput = document.getElementById('officer_id');
            const officerDropdown = document.getElementById('officer_dropdown');
            const officerOptions = document.getElementById('officer_options');
            const officerSearchInput = document.getElementById('officer_search_input');

            // Render officer options
            function renderOfficerOptions(officersList) {
                if (officersList.length === 0) {
                    officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                    return;
                }
                
                officerOptions.innerHTML = officersList.map(officer => {
                    const details = officer.service_number !== 'N/A' ? officer.service_number : '';
                    const rank = officer.rank !== 'N/A' ? ' - ' + officer.rank : '';
                    
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                             data-id="${officer.id}" 
                             data-name="${officer.name}"
                             data-service="${officer.service_number}"
                             data-rank="${officer.rank}">
                            <div class="text-sm text-foreground font-medium">${officer.name}</div>
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
                        officerHiddenInput.value = id;
                        
                        // Update display text
                        const displayText = name + (service !== 'N/A' ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
                        officerSelectText.textContent = displayText;
                        
                        // Close dropdown
                        officerDropdown.classList.add('hidden');
                        
                        // Clear search
                        officerSearchInput.value = '';
                        
                        // Re-render all options
                        renderOfficerOptions(officers);
                        
                        // Trigger change event to fetch assignment
                        officerHiddenInput.dispatchEvent(new Event('change'));
                    });
                });
            }

            // Setup search functionality
            function setupOfficerSearch() {
                officerSearchInput.addEventListener('input', function() {
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

            // Initialize
            renderOfficerOptions(officers);
            setupOfficerSearch();

            // Toggle dropdown
            officerSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                officerDropdown.classList.toggle('hidden');
                
                if (!officerDropdown.classList.contains('hidden')) {
                    // Focus search input when dropdown opens
                    setTimeout(() => {
                        officerSearchInput.focus();
                    }, 100);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (officerDropdown && !officerDropdown.contains(e.target) && !officerSelectTrigger.contains(e.target)) {
                    officerDropdown.classList.add('hidden');
                }
            });

            // Set initial selected officer if old input exists
            @if(old('officer_id'))
                const selectedOfficer = officers.find(o => o.id == {{ old('officer_id') }});
                if (selectedOfficer) {
                    const displayText = selectedOfficer.name + (selectedOfficer.service_number !== 'N/A' ? ' (' + selectedOfficer.service_number + (selectedOfficer.rank !== 'N/A' ? ' - ' + selectedOfficer.rank : '') + ')' : '');
                    officerSelectText.textContent = displayText;
                }
            @endif

            // Allow editing order number
            document.getElementById('edit-order-number').addEventListener('click', function() {
                const orderNumberInput = document.getElementById('order_number');
                orderNumberInput.removeAttribute('readonly');
                orderNumberInput.focus();
                this.style.display = 'none';
            });

            // Fetch officer's current assignment when officer is selected
            officerHiddenInput.addEventListener('change', function() {
            const officerId = officerHiddenInput.value;
            const currentSection = document.getElementById('current-assignment-section');
            const conflictWarning = document.getElementById('conflict-warning');
            
            // Hide sections initially
            currentSection.classList.add('hidden');
            conflictWarning.classList.add('hidden');
            
            if (!officerId) {
                return;
            }

            // Show loading state
            document.getElementById('current-command').textContent = 'Loading...';
            document.getElementById('current-unit').textContent = 'Loading...';
            document.getElementById('current-role').textContent = 'Loading...';

            // Fetch officer assignment
            fetch('{{ route("staff-officer.internal-staff-orders.get-officer-assignment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    officer_id: officerId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Display current assignment
                document.getElementById('current-command').textContent = data.current_command || 'N/A';
                document.getElementById('current-unit').textContent = data.current_unit || 'Not Assigned';
                document.getElementById('current-role').textContent = data.current_role || 'Not Assigned';
                currentSection.classList.remove('hidden');

                // Check for conflicts if target unit and role are selected
                checkConflicts();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to fetch officer assignment. Please try again.');
            });
        });

        // Check for conflicts when target unit or role changes
        function checkConflicts() {
            const officerId = officerHiddenInput.value;
            const targetUnit = document.getElementById('target_unit').value;
            const targetRole = document.getElementById('target_role').value;
            const conflictWarning = document.getElementById('conflict-warning');
            const conflictMessage = document.getElementById('conflict-message');
            const conflictDetails = document.getElementById('conflict-details');

            // Hide warning initially
            conflictWarning.classList.add('hidden');

            if (!officerId || !targetUnit || !targetRole) {
                return;
            }

            // Only check for OIC/2IC roles
            if (!['OIC', '2IC'].includes(targetRole)) {
                return;
            }

            // Fetch conflict information
            fetch('{{ route("staff-officer.internal-staff-orders.check-conflicts") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    officer_id: officerId,
                    target_unit: targetUnit,
                    target_role: targetRole
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }

                if (data.has_conflict) {
                    conflictMessage.textContent = data.message;
                    conflictDetails.innerHTML = `
                        <strong>Current ${data.conflict_type}:</strong><br>
                        ${data.current_holder.name} (${data.current_holder.service_number})<br>
                        Rank: ${data.current_holder.rank}
                    `;
                    conflictWarning.classList.remove('hidden');
                } else {
                    conflictWarning.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
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

        // Initialize target unit and role selects
        @php
            $unitsData = collect($availableUnits)->map(function($unit) {
                return ['id' => $unit, 'name' => $unit];
            })->values();
            $rolesData = [
                ['id' => 'Member', 'name' => 'Member'],
                ['id' => '2IC', 'name' => '2IC (Second In Command)'],
                ['id' => 'OIC', 'name' => 'OIC (Officer In Charge)']
            ];
        @endphp
        const unitOptions = @json($unitsData);
        const roleOptions = @json($rolesData);

        createSearchableSelect({
            triggerId: 'target_unit_select_trigger',
            hiddenInputId: 'target_unit',
            dropdownId: 'target_unit_dropdown',
            searchInputId: 'target_unit_search_input',
            optionsContainerId: 'target_unit_options',
            displayTextId: 'target_unit_select_text',
            options: unitOptions,
            placeholder: 'Select target unit...',
            searchPlaceholder: 'Search units...',
            onSelect: function() {
                checkConflicts();
            }
        });

        createSearchableSelect({
            triggerId: 'target_role_select_trigger',
            hiddenInputId: 'target_role',
            dropdownId: 'target_role_dropdown',
            searchInputId: 'target_role_search_input',
            optionsContainerId: 'target_role_options',
            displayTextId: 'target_role_select_text',
            options: roleOptions,
            placeholder: 'Select target role...',
            searchPlaceholder: 'Search roles...',
            onSelect: function() {
                checkConflicts();
            }
        });

        // Update checkConflicts to use hidden inputs
        function checkConflicts() {
            const officerId = officerHiddenInput.value;
            const targetUnit = document.getElementById('target_unit').value;
            const targetRole = document.getElementById('target_role').value;
            const conflictWarning = document.getElementById('conflict-warning');
            const conflictMessage = document.getElementById('conflict-message');
            const conflictDetails = document.getElementById('conflict-details');

            // Hide warning initially
            conflictWarning.classList.add('hidden');

            if (!officerId || !targetUnit || !targetRole) {
                return;
            }

            // Only check for OIC/2IC roles
            if (!['OIC', '2IC'].includes(targetRole)) {
                return;
            }

            // Fetch conflict information
            fetch('{{ route("staff-officer.internal-staff-orders.check-conflicts") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    officer_id: officerId,
                    target_unit: targetUnit,
                    target_role: targetRole
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }

                if (data.has_conflict) {
                    conflictMessage.textContent = data.message;
                    conflictDetails.innerHTML = `
                        <strong>Current ${data.conflict_type}:</strong><br>
                        ${data.current_holder.name} (${data.current_holder.service_number})<br>
                        Rank: ${data.current_holder.rank}
                    `;
                    conflictWarning.classList.remove('hidden');
                } else {
                    conflictWarning.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        });
    </script>
@endsection

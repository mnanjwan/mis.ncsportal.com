@extends('layouts.app')

@section('title', 'Create Staff Order')
@section('page-title', 'Create Staff Order')

@section('breadcrumbs')
    @if($routePrefix === 'zone-coordinator')
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.dashboard') }}">Zone Coordinator</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.staff-orders') }}">Staff Orders</a>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.staff-orders') }}">Staff Orders</a>
    @endif
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route($routePrefix . '.staff-orders') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Staff Orders
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
                <h3 class="kt-card-title">Create Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route($routePrefix . '.staff-orders.store') }}" method="POST" id="staff-order-form">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Order Number (Auto-generated, but editable) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number</label>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       name="order_number" 
                                       id="order_number"
                                       class="kt-input flex-1" 
                                       value="{{ old('order_number', $orderNumber) }}"
                                       placeholder="Auto-generated order number"
                                       readonly>
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

                        <!-- Officer Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <label class="kt-form-label">Officer <span class="text-danger">*</span></label>
                                @if($isZoneCoordinator ?? false)
                                    <span class="text-xs text-secondary-foreground bg-blue-50 px-2 py-1 rounded">
                                        <i class="ki-filled ki-information"></i> Showing only GL 07 and below officers in your zone
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id') }}" required>
                                <button type="button" 
                                        id="officer_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('officer_id') border-danger @enderror">
                                    <span id="officer_select_text">
                                        @if(old('officer_id'))
                                            @php $selectedOfficer = $officers->firstWhere('id', old('officer_id')); @endphp
                                            @if($selectedOfficer)
                                                {{ $selectedOfficer->initials . ' ' . $selectedOfficer->surname }} 
                                                ({{ $selectedOfficer->service_number ?? 'N/A' }})
                                                @if($selectedOfficer->salary_grade_level) - Grade: {{ $selectedOfficer->salary_grade_level }}@endif
                                                @if($selectedOfficer->substantive_rank) - Rank: {{ $selectedOfficer->substantive_rank }}@endif
                                            @else
                                                Select an officer...
                                            @endif
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
                                                 data-service="{{ $officer->service_number ?? 'N/A' }}"
                                                 data-rank="{{ $officer->substantive_rank ?? 'N/A' }}"
                                                 data-grade-level="{{ $officer->salary_grade_level ?? 'N/A' }}"
                                                 data-command-id="{{ $officer->present_station }}"
                                                 data-command-name="{{ $officer->presentStation->name ?? 'N/A' }}">
                                                <div class="text-sm text-foreground font-medium">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                <div class="text-xs text-secondary-foreground">
                                                    {{ $officer->service_number ?? 'N/A' }}
                                                    @if($officer->salary_grade_level)
                                                        - Grade: {{ $officer->salary_grade_level }}
                                                    @endif
                                                    @if($officer->substantive_rank)
                                                        - Rank: {{ $officer->substantive_rank }}
                                                    @endif
                                                    @if($officer->presentStation) - {{ $officer->presentStation->name }}@endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground">
                                The "From Command" will be auto-filled when you select an officer.
                                @if($isZoneCoordinator ?? false)
                                    <br><strong>Note:</strong> Only officers at GL 07 (IC) and below from commands in your zone are displayed.
                                @endif
                            </span>
                            @error('officer_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- From Command Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">From Command <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="from_command_id" id="from_command_id" value="{{ old('from_command_id') }}" required>
                                <button type="button" 
                                        id="from_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('from_command_id') border-danger @enderror">
                                    <span id="from_command_select_text">
                                        @if(old('from_command_id'))
                                            @php $selectedCommand = $commands->firstWhere('id', old('from_command_id')); @endphp
                                            {{ $selectedCommand ? $selectedCommand->name . ($selectedCommand->zone ? ' (' . $selectedCommand->zone->name . ')' : '') : 'Select a command...' }}
                                        @else
                                            Select a command (will auto-fill when officer is selected)...
                                        @endif
                                    </span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="from_command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="from_command_search_input" 
                                                   class="kt-input w-full" 
                                                   placeholder="Search commands..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="from_command_options" class="max-h-60 overflow-y-auto">
                                        @foreach($commands as $command)
                                            <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 from-command-option" 
                                                 data-id="{{ $command->id }}" 
                                                 data-name="{{ $command->name }}"
                                                 data-zone="{{ $command->zone ? $command->zone->name : '' }}">
                                                <div class="text-sm text-foreground font-medium">{{ $command->name }}</div>
                                                @if($command->zone)
                                                    <div class="text-xs text-secondary-foreground">{{ $command->zone->name }}</div>
                                                @endif
                                            </div>
                                @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('from_command_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- To Command Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">To Command <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="to_command_id" id="to_command_id" value="{{ old('to_command_id') }}" required>
                                <button type="button" 
                                        id="to_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('to_command_id') border-danger @enderror">
                                    <span id="to_command_select_text">
                                        @if(old('to_command_id'))
                                            @php $selectedCommand = $commands->firstWhere('id', old('to_command_id')); @endphp
                                            {{ $selectedCommand ? $selectedCommand->name . ($selectedCommand->zone ? ' (' . $selectedCommand->zone->name . ')' : '') : 'Select a command...' }}
                                        @else
                                            Select a command...
                                        @endif
                                    </span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="to_command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="to_command_search_input" 
                                                   class="kt-input w-full" 
                                                   placeholder="Search commands..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="to_command_options" class="max-h-60 overflow-y-auto">
                                        @foreach($commands as $command)
                                            <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 to-command-option" 
                                                 data-id="{{ $command->id }}" 
                                                 data-name="{{ $command->name }}"
                                                 data-zone="{{ $command->zone ? $command->zone->name : '' }}">
                                                <div class="text-sm text-foreground font-medium">{{ $command->name }}</div>
                                                @if($command->zone)
                                                    <div class="text-xs text-secondary-foreground">{{ $command->zone->name }}</div>
                                                @endif
                                            </div>
                                @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('to_command_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Effective Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="effective_date" 
                                   class="kt-input" 
                                   value="{{ old('effective_date') }}"
                                   required>
                            @error('effective_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Order Type -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Posting Type</label>
                            <div class="relative">
                                <input type="hidden" name="order_type" id="order_type_id" value="{{ old('order_type') ?? '' }}">
                                <button type="button" 
                                        id="order_type_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="order_type_select_text">{{ old('order_type') ? (old('order_type') === 'POSTING' ? 'Posting' : (old('order_type') === 'TRANSFER' ? 'Transfer' : (old('order_type') === 'DEPLOYMENT' ? 'Deployment' : (old('order_type') === 'REASSIGNMENT' ? 'Reassignment' : 'Select Posting Type')))) : 'Select Posting Type' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="order_type_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="order_type_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search posting type..."
                                               autocomplete="off">
                                    </div>
                                    <div id="order_type_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                            @error('order_type')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="status_id" value="{{ old('status', 'DRAFT') }}">
                                <button type="button" 
                                        id="status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="status_select_text">{{ old('status', 'DRAFT') === 'PUBLISHED' ? 'Published' : 'Draft' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="status_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="status_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search status..."
                                               autocomplete="off">
                                    </div>
                                    <div id="status_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground mt-1">
                                <strong>Draft:</strong> Order is saved but not yet effective. <br>
                                <strong>Published:</strong> Order becomes effective immediately and triggers workflow automation.
                            </span>
                            @error('status')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Description</label>
                            <textarea name="description" 
                                      class="kt-input" 
                                      rows="4"
                                      placeholder="Enter order description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route($routePrefix . '.staff-orders') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Create Staff Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Officers data
            @php
                $officersData = $officers->map(function($officer) {
                    return [
                        'id' => $officer->id,
                        'name' => $officer->initials . ' ' . $officer->surname,
                        'service_number' => $officer->service_number ?? 'N/A',
                        'rank' => $officer->substantive_rank ?? 'N/A',
                        'grade_level' => $officer->salary_grade_level ?? 'N/A',
                        'command_id' => $officer->present_station,
                        'command_name' => $officer->presentStation->name ?? 'N/A'
                    ];
                })->values();
            @endphp
            const officers = @json($officersData);

            // Commands data
            @php
                $commandsData = $commands->map(function($command) {
                    return [
                        'id' => $command->id,
                        'name' => $command->name,
                        'zone' => $command->zone ? $command->zone->name : ''
                    ];
                })->values();
            @endphp
            const commands = @json($commandsData);

            // Order Number Edit Toggle
            document.getElementById('edit-order-number').addEventListener('click', function() {
                const input = document.getElementById('order_number');
                if (input.readOnly) {
                    input.readOnly = false;
                    input.focus();
                    this.innerHTML = '<i class="ki-filled ki-check"></i>';
                    this.title = 'Lock order number';
                } else {
                    input.readOnly = true;
                    this.innerHTML = '<i class="ki-filled ki-pencil"></i>';
                    this.title = 'Edit order number';
                }
            });

            // ========== Officer Searchable Select ==========
            const officerSelectTrigger = document.getElementById('officer_select_trigger');
            const officerSelectText = document.getElementById('officer_select_text');
            const officerHiddenInput = document.getElementById('officer_id');
            const officerDropdown = document.getElementById('officer_dropdown');
            const officerOptions = document.getElementById('officer_options');
            const officerSearchInput = document.getElementById('officer_search_input');

            function renderOfficerOptions(officersList) {
                if (officersList.length === 0) {
                    officerOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                    return;
                }
                
                officerOptions.innerHTML = officersList.map(officer => {
                    const serviceText = officer.service_number !== 'N/A' ? officer.service_number : '';
                    const gradeText = officer.grade_level !== 'N/A' ? ' - Grade: ' + officer.grade_level : '';
                    const rankText = officer.rank !== 'N/A' ? ' - Rank: ' + officer.rank : '';
                    const commandText = officer.command_name !== 'N/A' ? ' - ' + officer.command_name : '';
                    
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                             data-id="${officer.id}" 
                             data-name="${officer.name}"
                             data-service="${officer.service_number}"
                             data-rank="${officer.rank}"
                             data-grade-level="${officer.grade_level}"
                             data-command-id="${officer.command_id}"
                             data-command-name="${officer.command_name}">
                            <div class="text-sm text-foreground font-medium">${officer.name}</div>
                            <div class="text-xs text-secondary-foreground">${serviceText}${gradeText}${rankText}${commandText}</div>
                        </div>
                    `;
                }).join('');
                
                // Add click handlers
                officerOptions.querySelectorAll('.officer-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        const service = this.dataset.service;
                        const commandId = this.dataset.commandId;
                        const commandName = this.dataset.commandName;
                        
                        // Update hidden input
                        officerHiddenInput.value = id;
                        
                        // Update display text
                        const rank = this.dataset.rank;
                        const gradeLevel = this.dataset.gradeLevel;
                        const rankDisplay = rank && rank !== 'N/A' ? ' - ' + rank : '';
                        const gradeDisplay = gradeLevel && gradeLevel !== 'N/A' ? ' (' + gradeLevel + ')' : '';
                        const displayText = name + (service !== 'N/A' ? ' (' + service + ')' : '') + rankDisplay + gradeDisplay;
                        officerSelectText.textContent = displayText;
                        
                        // Close dropdown
                        officerDropdown.classList.add('hidden');
                        
                        // Clear search
                        officerSearchInput.value = '';
                        
                        // Re-render all options
                        renderOfficerOptions(officers);
                        
                        // Auto-fill From Command
                        if (commandId) {
                            updateFromCommandSelect(commandId, commandName);
                        }
                        
                        // Trigger change event
                        officerHiddenInput.dispatchEvent(new Event('change'));
                    });
                });
            }

            function setupOfficerSearch() {
                officerSearchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = officers.filter(officer => {
                        const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                        const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                        const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
                        const gradeMatch = officer.grade_level && officer.grade_level.toLowerCase().includes(searchTerm);
                        const commandMatch = officer.command_name && officer.command_name.toLowerCase().includes(searchTerm);
                        return nameMatch || serviceMatch || rankMatch || gradeMatch || commandMatch;
                    });
                    
                    renderOfficerOptions(filtered);
                });
            }

            renderOfficerOptions(officers);
            setupOfficerSearch();

            officerSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                officerDropdown.classList.toggle('hidden');
                
                if (!officerDropdown.classList.contains('hidden')) {
                    setTimeout(() => {
                        officerSearchInput.focus();
                    }, 100);
                }
            });

            // ========== From Command Searchable Select ==========
            const fromCommandSelectTrigger = document.getElementById('from_command_select_trigger');
            const fromCommandSelectText = document.getElementById('from_command_select_text');
            const fromCommandHiddenInput = document.getElementById('from_command_id');
            const fromCommandDropdown = document.getElementById('from_command_dropdown');
            const fromCommandOptions = document.getElementById('from_command_options');
            const fromCommandSearchInput = document.getElementById('from_command_search_input');

            function updateFromCommandSelect(commandId, commandName) {
                fromCommandHiddenInput.value = commandId;
                fromCommandSelectText.textContent = commandName;
            }

            function renderFromCommandOptions(commandsList) {
                if (commandsList.length === 0) {
                    fromCommandOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
                    return;
                }
                
                fromCommandOptions.innerHTML = commandsList.map(command => {
                    const zoneText = command.zone ? ' (' + command.zone + ')' : '';
                    
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 from-command-option" 
                             data-id="${command.id}" 
                             data-name="${command.name}"
                             data-zone="${command.zone}">
                            <div class="text-sm text-foreground font-medium">${command.name}</div>
                            ${command.zone ? '<div class="text-xs text-secondary-foreground">' + command.zone + '</div>' : ''}
                        </div>
                    `;
                }).join('');
                
                fromCommandOptions.querySelectorAll('.from-command-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        const zone = this.dataset.zone;
                        
                        fromCommandHiddenInput.value = id;
                        fromCommandSelectText.textContent = name + (zone ? ' (' + zone + ')' : '');
                        fromCommandDropdown.classList.add('hidden');
                        fromCommandSearchInput.value = '';
                        renderFromCommandOptions(commands);
                    });
                });
            }

            function setupFromCommandSearch() {
                fromCommandSearchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = commands.filter(command => {
                        const nameMatch = command.name.toLowerCase().includes(searchTerm);
                        const zoneMatch = command.zone && command.zone.toLowerCase().includes(searchTerm);
                        return nameMatch || zoneMatch;
                    });
                    
                    renderFromCommandOptions(filtered);
                });
            }

            renderFromCommandOptions(commands);
            setupFromCommandSearch();

            fromCommandSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                fromCommandDropdown.classList.toggle('hidden');
                
                if (!fromCommandDropdown.classList.contains('hidden')) {
                    setTimeout(() => {
                        fromCommandSearchInput.focus();
                    }, 100);
                }
            });

            // ========== To Command Searchable Select ==========
            const toCommandSelectTrigger = document.getElementById('to_command_select_trigger');
            const toCommandSelectText = document.getElementById('to_command_select_text');
            const toCommandHiddenInput = document.getElementById('to_command_id');
            const toCommandDropdown = document.getElementById('to_command_dropdown');
            const toCommandOptions = document.getElementById('to_command_options');
            const toCommandSearchInput = document.getElementById('to_command_search_input');

            function renderToCommandOptions(commandsList) {
                if (commandsList.length === 0) {
                    toCommandOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
                    return;
                }
                
                toCommandOptions.innerHTML = commandsList.map(command => {
                    const zoneText = command.zone ? ' (' + command.zone + ')' : '';
                    
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 to-command-option" 
                             data-id="${command.id}" 
                             data-name="${command.name}"
                             data-zone="${command.zone}">
                            <div class="text-sm text-foreground font-medium">${command.name}</div>
                            ${command.zone ? '<div class="text-xs text-secondary-foreground">' + command.zone + '</div>' : ''}
                        </div>
                    `;
                }).join('');
                
                toCommandOptions.querySelectorAll('.to-command-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        const zone = this.dataset.zone;
                        
                        toCommandHiddenInput.value = id;
                        toCommandSelectText.textContent = name + (zone ? ' (' + zone + ')' : '');
                        toCommandDropdown.classList.add('hidden');
                        toCommandSearchInput.value = '';
                        renderToCommandOptions(commands);
                    });
                });
            }

            function setupToCommandSearch() {
                toCommandSearchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = commands.filter(command => {
                        const nameMatch = command.name.toLowerCase().includes(searchTerm);
                        const zoneMatch = command.zone && command.zone.toLowerCase().includes(searchTerm);
                        return nameMatch || zoneMatch;
                    });
                    
                    renderToCommandOptions(filtered);
                });
            }

            renderToCommandOptions(commands);
            setupToCommandSearch();

            toCommandSelectTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                toCommandDropdown.classList.toggle('hidden');
                
                if (!toCommandDropdown.classList.contains('hidden')) {
                    setTimeout(() => {
                        toCommandSearchInput.focus();
                    }, 100);
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (officerDropdown && !officerDropdown.contains(e.target) && !officerSelectTrigger.contains(e.target)) {
                    officerDropdown.classList.add('hidden');
                }
                if (fromCommandDropdown && !fromCommandDropdown.contains(e.target) && !fromCommandSelectTrigger.contains(e.target)) {
                    fromCommandDropdown.classList.add('hidden');
                }
                if (toCommandDropdown && !toCommandDropdown.contains(e.target) && !toCommandSelectTrigger.contains(e.target)) {
                    toCommandDropdown.classList.add('hidden');
                }
            });

            // Set initial selected values if old input exists
            @if(old('officer_id'))
                const selectedOfficer = officers.find(o => o.id == {{ old('officer_id') }});
                if (selectedOfficer) {
                    const rankDisplay = selectedOfficer.rank && selectedOfficer.rank !== 'N/A' ? ' - ' + selectedOfficer.rank : '';
                    const gradeDisplay = selectedOfficer.grade_level && selectedOfficer.grade_level !== 'N/A' ? ' (' + selectedOfficer.grade_level + ')' : '';
                    const displayText = selectedOfficer.name + (selectedOfficer.service_number !== 'N/A' ? ' (' + selectedOfficer.service_number + ')' : '') + rankDisplay + gradeDisplay;
                    officerSelectText.textContent = displayText;
                }
            @endif

            @if(old('from_command_id'))
                const selectedFromCommand = commands.find(c => c.id == {{ old('from_command_id') }});
                if (selectedFromCommand) {
                    fromCommandSelectText.textContent = selectedFromCommand.name + (selectedFromCommand.zone ? ' (' + selectedFromCommand.zone + ')' : '');
                }
            @endif

            @if(old('to_command_id'))
                const selectedToCommand = commands.find(c => c.id == {{ old('to_command_id') }});
                if (selectedToCommand) {
                    toCommandSelectText.textContent = selectedToCommand.name + (selectedToCommand.zone ? ' (' + selectedToCommand.zone + ')' : '');
                }
            @endif

            // Form validation before submit
            document.getElementById('staff-order-form').addEventListener('submit', function(e) {
                const officerId = officerHiddenInput.value;
                const fromCommandId = fromCommandHiddenInput.value;
                const toCommandId = toCommandHiddenInput.value;
                
                if (!officerId || !fromCommandId || !toCommandId) {
                    e.preventDefault();
                    let missing = [];
                    if (!officerId) missing.push('Officer');
                    if (!fromCommandId) missing.push('From Command');
                    if (!toCommandId) missing.push('To Command');
                    alert('Please select: ' + missing.join(', '));
                    return false;
                }
                
                if (fromCommandId === toCommandId) {
                    e.preventDefault();
                    alert('From Command and To Command cannot be the same.');
                    return false;
                }
                
                if (!document.getElementById('effective_date').value) {
                    e.preventDefault();
                    alert('Please select an Effective Date');
                    return false;
                }
            });
        });

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

            if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
                return;
            }

            let selectedOption = null;
            let filteredOptions = [...options];

            // Render options
            function renderOptions(opts) {
                if (opts.length === 0) {
                    optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
                    return;
                }

                optionsContainer.innerHTML = opts.map(opt => {
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                    const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
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
                        selectedOption = options.find(o => {
                            const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                            return String(optValue) === String(id);
                        });
                        
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
                    const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
                    return String(display).toLowerCase().includes(searchTerm);
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

        // Initialize order type and status selects
        document.addEventListener('DOMContentLoaded', function() {
            // Order type options
            const orderTypeOptions = [
                {id: '', name: 'Select Posting Type'},
                {id: 'POSTING', name: 'Posting'},
                {id: 'TRANSFER', name: 'Transfer'},
                {id: 'DEPLOYMENT', name: 'Deployment'},
                {id: 'REASSIGNMENT', name: 'Reassignment'}
            ];

            // Status options
            const statusOptions = [
                {id: 'DRAFT', name: 'Draft'},
                {id: 'PUBLISHED', name: 'Published'}
            ];

            // Initialize order type select
            createSearchableSelect({
                triggerId: 'order_type_select_trigger',
                hiddenInputId: 'order_type_id',
                dropdownId: 'order_type_dropdown',
                searchInputId: 'order_type_search_input',
                optionsContainerId: 'order_type_options',
                displayTextId: 'order_type_select_text',
                options: orderTypeOptions,
                placeholder: 'Select Posting Type',
                searchPlaceholder: 'Search posting type...'
            });

            // Initialize status select
            createSearchableSelect({
                triggerId: 'status_select_trigger',
                hiddenInputId: 'status_id',
                dropdownId: 'status_dropdown',
                searchInputId: 'status_search_input',
                optionsContainerId: 'status_options',
                displayTextId: 'status_select_text',
                options: statusOptions,
                placeholder: 'Draft',
                searchPlaceholder: 'Search status...'
            });
        });
    </script>
@endpush
@endsection

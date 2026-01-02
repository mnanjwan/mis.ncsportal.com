@extends('layouts.app')

@section('title', 'Edit Staff Order')
@section('page-title', 'Edit Staff Order')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.staff-orders') }}">Staff Orders</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.staff-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Order Details
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
                <h3 class="kt-card-title">Edit Staff Order</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.staff-orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-5">
                        <!-- Order Number -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Order Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="order_number" 
                                   class="kt-input" 
                                   value="{{ old('order_number', $order->order_number) }}"
                                   placeholder="Enter order number"
                                   required>
                            @error('order_number')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Officer Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Officer <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="officer_id" id="officer_id" value="{{ old('officer_id', $order->officer_id) }}" required>
                                <button type="button" 
                                        id="officer_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('officer_id') border-danger @enderror">
                                    <span id="officer_select_text">
                                        @php
                                            $selectedOfficerId = old('officer_id', $order->officer_id);
                                            $selectedOfficer = $officers->find($selectedOfficerId);
                                        @endphp
                                        {{ $selectedOfficer ? $selectedOfficer->initials . ' ' . $selectedOfficer->surname . ' (' . ($selectedOfficer->service_number ?? 'N/A') . ')' : 'Select an officer...' }}
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
                                                 data-service="{{ $officer->service_number ?? 'N/A' }}">
                                                <div class="text-sm text-foreground font-medium">{{ $officer->initials }} {{ $officer->surname }}</div>
                                                <div class="text-xs text-secondary-foreground">{{ $officer->service_number ?? 'N/A' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('officer_id')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- From Command Selection (Searchable Select) -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">From Command <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="hidden" name="from_command_id" id="from_command_id" value="{{ old('from_command_id', $order->from_command_id) }}" required>
                                <button type="button" 
                                        id="from_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('from_command_id') border-danger @enderror">
                                    <span id="from_command_select_text">
                                        @php
                                            $selectedFromCommandId = old('from_command_id', $order->from_command_id);
                                            $selectedFromCommand = $commands->find($selectedFromCommandId);
                                        @endphp
                                        {{ $selectedFromCommand ? $selectedFromCommand->name . ($selectedFromCommand->zone ? ' (' . $selectedFromCommand->zone->name . ')' : '') : 'Select a command...' }}
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
                                <input type="hidden" name="to_command_id" id="to_command_id" value="{{ old('to_command_id', $order->to_command_id) }}" required>
                                <button type="button" 
                                        id="to_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer @error('to_command_id') border-danger @enderror">
                                    <span id="to_command_select_text">
                                        @php
                                            $selectedToCommandId = old('to_command_id', $order->to_command_id);
                                            $selectedToCommand = $commands->find($selectedToCommandId);
                                        @endphp
                                        {{ $selectedToCommand ? $selectedToCommand->name . ($selectedToCommand->zone ? ' (' . $selectedToCommand->zone->name . ')' : '') : 'Select a command...' }}
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
                                   value="{{ old('effective_date', $order->effective_date ? $order->effective_date->format('Y-m-d') : '') }}"
                                   required>
                            @error('effective_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Order Type -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Posting Type</label>
                            <select name="order_type" class="kt-input">
                                <option value="">Select Posting Type</option>
                                <option value="POSTING" {{ old('order_type', $order->order_type) == 'POSTING' ? 'selected' : '' }}>Posting</option>
                                <option value="TRANSFER" {{ old('order_type', $order->order_type) == 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                                <option value="DEPLOYMENT" {{ old('order_type', $order->order_type) == 'DEPLOYMENT' ? 'selected' : '' }}>Deployment</option>
                                <option value="REASSIGNMENT" {{ old('order_type', $order->order_type) == 'REASSIGNMENT' ? 'selected' : '' }}>Reassignment</option>
                            </select>
                            @error('order_type')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Status</label>
                            <select name="status" class="kt-input">
                                <option value="DRAFT" {{ old('status', $order->status ?? 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                <option value="PUBLISHED" {{ old('status', $order->status ?? 'DRAFT') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                                <option value="CANCELLED" {{ old('status', $order->status ?? 'DRAFT') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                            </select>
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
                                      placeholder="Enter order description">{{ old('description', $order->description) }}</textarea>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.staff-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Update Staff Order
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
                        'service_number' => $officer->service_number ?? 'N/A'
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
                    
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 officer-option" 
                             data-id="${officer.id}" 
                             data-name="${officer.name}"
                             data-service="${officer.service_number}">
                            <div class="text-sm text-foreground font-medium">${officer.name}</div>
                            <div class="text-xs text-secondary-foreground">${serviceText}</div>
                        </div>
                    `;
                }).join('');
                
                officerOptions.querySelectorAll('.officer-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        const service = this.dataset.service;
                        
                        officerHiddenInput.value = id;
                        const displayText = name + (service !== 'N/A' ? ' (' + service + ')' : '');
                        officerSelectText.textContent = displayText;
                        officerDropdown.classList.add('hidden');
                        officerSearchInput.value = '';
                        renderOfficerOptions(officers);
                    });
                });
            }

            function setupOfficerSearch() {
                officerSearchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filtered = officers.filter(officer => {
                        const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                        const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                        return nameMatch || serviceMatch;
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

            function renderFromCommandOptions(commandsList) {
                if (commandsList.length === 0) {
                    fromCommandOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
                    return;
                }
                
                fromCommandOptions.innerHTML = commandsList.map(command => {
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
        });
    </script>
    @endpush
@endsection


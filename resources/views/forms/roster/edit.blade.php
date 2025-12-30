@extends('layouts.app')

@section('title', 'Edit Duty Roster')
@section('page-title', 'Edit Duty Roster')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster') }}">Duty Roster</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster.show', $roster->id) }}">View</a>
    <span>/</span>
    <span class="text-primary">Edit</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm font-semibold text-danger">Please fix the following errors:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-danger ml-8">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@if($roster->status !== 'DRAFT')
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">Only DRAFT rosters can be edited.</p>
                <a href="{{ route('staff-officer.roster.show', $roster->id) }}" class="kt-btn kt-btn-primary mt-4">
                    Back to Roster
                </a>
            </div>
        </div>
    </div>
@else
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Info Card -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-5">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-2xl text-info"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-semibold text-foreground">Edit Duty Roster</span>
                        <span class="text-xs text-secondary-foreground">
                            Period: {{ $roster->roster_period_start->format('M d, Y') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form -->
        <form class="kt-card" method="POST" action="{{ route('staff-officer.roster.update', $roster->id) }}" id="roster-edit-form">
            @csrf
            @method('PUT')
            <div class="kt-card-header">
                <h3 class="kt-card-title">Roster Leadership & Assignments</h3>
            </div>
            <div class="kt-card-content space-y-6">
                <!-- Unit Selection -->
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">Unit/Title</label>
                    <div class="flex gap-2">
                        <select class="kt-input flex-1" name="unit" id="unit-select-edit">
                            <option value="">Select Unit</option>
                            @if(isset($allUnits) && count($allUnits) > 0)
                                @foreach($allUnits as $unit)
                                    <option value="{{ $unit }}" {{ $roster->unit === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            @endif
                            <option value="__NEW__" {{ !isset($allUnits) || !in_array($roster->unit, $allUnits) && $roster->unit ? 'selected' : '' }}>➕ Create New Unit</option>
                        </select>
                        <input type="text" class="kt-input flex-1 {{ (isset($allUnits) && in_array($roster->unit, $allUnits)) || !$roster->unit ? 'hidden' : '' }}" name="unit_custom" id="unit-custom-input-edit" placeholder="Enter new unit name" value="{{ (!isset($allUnits) || !in_array($roster->unit, $allUnits)) && $roster->unit ? $roster->unit : old('unit_custom') }}"/>
                    </div>
                    <p class="text-xs text-secondary-foreground mt-1">Select a unit from the list or create a new one</p>
                </div>
                
                <!-- Leadership Selection -->
                <div class="kt-card shadow-none bg-info/10 border border-info/20">
                    <div class="kt-card-content p-4">
                        <h4 class="text-sm font-semibold text-foreground mb-4">Roster Leadership</h4>
                        
                        <!-- Command Selection (Searchable) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">
                                Command <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="command_search" 
                                       class="kt-input w-full" 
                                       placeholder="Search command..."
                                       autocomplete="off">
                                <input type="hidden" 
                                       name="command_id" 
                                       id="command_id"
                                       value="{{ $commandId }}">
                                <div id="command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                            <div id="selected_command" class="mt-2 p-2 bg-muted/50 rounded-lg {{ $commandId ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium" id="selected_command_name">
                                        @if($commandId && $roster->command)
                                            {{ $roster->command->name }}{{ $roster->command->code ? ' (' . $roster->command->code . ')' : '' }}
                                        @endif
                                    </span>
                                    <button type="button" 
                                            id="clear_command" 
                                            class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                        <i class="ki-filled ki-cross"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-secondary-foreground mt-1">Select a command to view officers in that command</p>
                        </div>
                        
                        <div class="grid sm:grid-cols-2 gap-4">
                            <!-- OIC Selection -->
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Officer in Charge (OIC) <span class="text-danger">*</span>
                                </label>
                                <div class="relative">
                                    <input type="hidden" name="oic_officer_id" id="oic_officer_id" value="{{ $roster->oic_officer_id }}" required>
                                    <button type="button" 
                                            id="oic_select_trigger" 
                                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer"
                                            {{ !$commandId ? 'disabled' : '' }}>
                                        <span id="oic_select_text">
                                            @if($roster->oic_officer_id && $roster->oicOfficer)
                                                {{ $roster->oicOfficer->initials }} {{ $roster->oicOfficer->surname }} ({{ $roster->oicOfficer->service_number }})
                                            @else
                                                {{ $commandId ? 'Select OIC' : 'Select command first, then choose OIC...' }}
                                            @endif
                                        </span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="oic_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <!-- Search Box -->
                                        <div class="p-3 border-b border-input">
                                <div class="relative">
                                    <input type="text" 
                                                       id="oic_search_input" 
                                                       class="kt-input w-full" 
                                                       placeholder="Search officers..."
                                           autocomplete="off">
                                </div>
                            </div>
                                        <!-- Options Container -->
                                        <div id="oic_options" class="max-h-60 overflow-y-auto">
                                            <div class="p-3 text-sm text-secondary-foreground text-center">
                                                {{ $commandId ? 'Loading officers...' : 'Select command first' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-secondary-foreground mt-1" id="oic_info"></p>
                            </div>
                            
                            <!-- 2IC Selection -->
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    Second In Command (2IC)
                                </label>
                                <div class="relative">
                                    <input type="hidden" name="second_in_command_officer_id" id="second_in_command_officer_id" value="{{ $roster->second_in_command_officer_id }}">
                                    <button type="button" 
                                            id="second_ic_select_trigger" 
                                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer"
                                            {{ !$commandId ? 'disabled' : '' }}>
                                        <span id="second_ic_select_text">
                                            @if($roster->second_in_command_officer_id && $roster->secondInCommandOfficer)
                                                {{ $roster->secondInCommandOfficer->initials }} {{ $roster->secondInCommandOfficer->surname }} ({{ $roster->secondInCommandOfficer->service_number }})
                                            @else
                                                {{ $commandId ? 'Select 2IC (Optional)' : 'Select command first, then choose 2IC...' }}
                                            @endif
                                        </span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="second_ic_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <!-- Search Box -->
                                        <div class="p-3 border-b border-input">
                                <div class="relative">
                                    <input type="text" 
                                                       id="second_ic_search_input" 
                                                       class="kt-input w-full" 
                                                       placeholder="Search officers..."
                                           autocomplete="off">
                                </div>
                                        </div>
                                        <!-- Options Container -->
                                        <div id="second_ic_options" class="max-h-60 overflow-y-auto">
                                            <div class="p-3 text-sm text-secondary-foreground text-center">
                                                {{ $commandId ? 'Loading officers...' : 'Select command first' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-secondary-foreground mt-1" id="second_ic_info"></p>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground mt-3">
                            <i class="ki-filled ki-information"></i> OIC and 2IC will be notified along with all assigned officers when the roster is updated.
                        </p>
                    </div>
                </div>
                
                <!-- Assignments Section -->
                <div>
                    <h4 class="text-sm font-semibold text-foreground mb-4">Officer Assignments</h4>
                <div id="assignments-container" class="space-y-4">
                    @if($roster->assignments->count() > 0)
                        @foreach($roster->assignments as $index => $assignment)
                            <div class="kt-card shadow-none bg-muted/30 border border-input" data-assignment-index="{{ $index }}">
                                <div class="kt-card-content p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-mono">Assignment #{{ $index + 1 }}</span>
                                        <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-assignment-btn" data-index="{{ $index }}">
                                            <i class="ki-filled ki-trash"></i> Remove
                                        </button>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-1">
                                            <label class="block text-sm font-medium mb-1">Officer <span class="text-danger">*</span></label>
                                            <div class="relative">
                                                <input type="hidden" 
                                                        name="assignments[{{ $index }}][officer_id]" 
                                                        id="assignment-officer-{{ $index }}"
                                                       value="{{ $assignment->officer_id }}"
                                                        required>
                                                <button type="button" 
                                                        id="assignment-officer-{{ $index }}-trigger" 
                                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer assignment-officer-trigger"
                                                        {{ !$commandId ? 'disabled' : '' }}>
                                                    <span id="assignment-officer-{{ $index }}-text">
                                                        @if($assignment->officer_id && $assignment->officer)
                                                            {{ $assignment->officer->initials }} {{ $assignment->officer->surname }} ({{ $assignment->officer->service_number }})
                                                        @else
                                                            {{ $commandId ? 'Select Officer' : 'Select command first, then choose officer...' }}
                                                        @endif
                                                    </span>
                                                    <i class="ki-filled ki-down text-gray-400"></i>
                                                </button>
                                                <div id="assignment-officer-{{ $index }}-dropdown" 
                                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden assignment-officer-dropdown">
                                                    <!-- Search Box -->
                                                    <div class="p-3 border-b border-input">
                                                        <div class="relative">
                                                            <input type="text" 
                                                                   id="assignment-officer-{{ $index }}-search-input" 
                                                                   class="kt-input w-full assignment-officer-search" 
                                                                   placeholder="Search officers..."
                                                                   autocomplete="off"
                                                                   data-assignment-index="{{ $index }}">
                                            </div>
                                        </div>
                                                    <!-- Options Container -->
                                                    <div id="assignment-officer-{{ $index }}-options" class="max-h-60 overflow-y-auto assignment-officer-options">
                                                        <div class="p-3 text-sm text-secondary-foreground text-center">
                                                            {{ $commandId ? 'Loading officers...' : 'Select command first' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label class="kt-form-label font-normal text-mono text-xs">Shift</label>
                                            <input type="text" class="kt-input" name="assignments[{{ $index }}][shift]" 
                                                   value="{{ $assignment->shift }}" placeholder="e.g., Morning, Evening, Night"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    </div>
                    <div class="mt-4">
                        <button type="button" class="kt-btn kt-btn-primary" id="add-assignment-btn">
                            <i class="ki-filled ki-plus"></i> Add Assignment
                        </button>
                    </div>
                </div>
            </div>
            <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                <a class="kt-btn kt-btn-outline" href="{{ route('staff-officer.roster.show', $roster->id) }}">Cancel</a>
                <button class="kt-btn kt-btn-primary" type="submit">
                    Save Changes
                    <i class="ki-filled ki-check text-base"></i>
                </button>
            </div>
        </form>
    </div>
@endif

@push('styles')
<style>
    .relative {
        position: relative;
    }
    
    .officer-search-input {
        background: white;
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0.75rem;
    }
    
    .officer-search-input:focus {
        outline: 2px solid #3b82f6;
        outline-offset: -2px;
        z-index: 10;
    }
    
    .assignment-officer-select[size] {
        margin-top: 0;
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    
    .assignment-officer-select[size="1"] {
        margin-top: 0;
    }
</style>
@endpush

@push('scripts')
<script>
let assignmentCount = {{ $roster->assignments->count() }};
const allOfficers = @json($allOfficers ?? $officers);
const periodStart = '{{ $roster->roster_period_start->format('Y-m-d') }}';
const periodEnd = '{{ $roster->roster_period_end->format('Y-m-d') }}';

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
const initialCommandId = {{ $commandId ?? 'null' }};
const initialOicId = {{ $roster->oic_officer_id ?? 'null' }};
const initialSecondIcId = {{ $roster->second_in_command_officer_id ?? 'null' }};
let isInitialLoad = true;

// Searchable Select Helper Function (for Command selection)
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

// Load officers by command
function loadOfficersByCommand(commandId) {
    const oicSelectTrigger = document.getElementById('oic_select_trigger');
    const oicSelectText = document.getElementById('oic_select_text');
    const oicHiddenInput = document.getElementById('oic_officer_id');
    const oicDropdown = document.getElementById('oic_dropdown');
    const oicOptions = document.getElementById('oic_options');
    const oicSearchInput = document.getElementById('oic_search_input');
    const oicInfo = document.getElementById('oic_info');
    
    const secondIcSelectTrigger = document.getElementById('second_ic_select_trigger');
    const secondIcSelectText = document.getElementById('second_ic_select_text');
    const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
    const secondIcDropdown = document.getElementById('second_ic_dropdown');
    const secondIcOptions = document.getElementById('second_ic_options');
    const secondIcSearchInput = document.getElementById('second_ic_search_input');
    const secondIcInfo = document.getElementById('second_ic_info');
    
    // Update UI to loading state
    oicSelectText.textContent = 'Loading officers...';
    oicSelectTrigger.disabled = true;
    if (!isInitialLoad || commandId != initialCommandId) {
        oicHiddenInput.value = '';
    }
    oicOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Loading...</div>';
    
    secondIcSelectText.textContent = 'Loading officers...';
    secondIcSelectTrigger.disabled = true;
    if (!isInitialLoad || commandId != initialCommandId) {
        secondIcHiddenInput.value = '';
    }
    secondIcOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Loading...</div>';

    return fetch(`{{ route('staff-officer.roster.officers-by-command') }}?command_id=${commandId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        officers = data;
        
        // Calculate initial IDs to use (before the if block)
        const oicIdToUse = isInitialLoad && commandId == initialCommandId ? initialOicId : null;
        const secondIcIdToUse = isInitialLoad && commandId == initialCommandId ? initialSecondIcId : null;
        
        if (data.length > 0) {
            // Populate OIC options
            renderOfficerOptions(data, oicOptions, oicHiddenInput, oicSelectText, oicDropdown, oicSearchInput, 'oic', oicIdToUse);
            
            // Populate 2IC options
            renderOfficerOptions(data, secondIcOptions, secondIcHiddenInput, secondIcSelectText, secondIcDropdown, secondIcSearchInput, 'second_ic', secondIcIdToUse);
            
            oicInfo.textContent = `${data.length} officer${data.length !== 1 ? 's' : ''} available`;
            oicInfo.classList.remove('text-danger');
            oicInfo.classList.add('text-secondary-foreground');
            
            secondIcInfo.textContent = `${data.length} officer${data.length !== 1 ? 's' : ''} available`;
            secondIcInfo.classList.remove('text-danger');
            secondIcInfo.classList.add('text-secondary-foreground');
            
            oicSelectTrigger.disabled = false;
            if (!oicIdToUse) {
                oicSelectText.textContent = 'Select OIC';
            }
            
            secondIcSelectTrigger.disabled = false;
            if (!secondIcIdToUse) {
                secondIcSelectText.textContent = 'Select 2IC (Optional)';
            }
        } else {
            oicOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found in this command.</div>';
            oicInfo.textContent = 'No officers found in this command.';
            oicInfo.classList.remove('text-secondary-foreground');
            oicInfo.classList.add('text-danger');
            
            secondIcOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found in this command.</div>';
            secondIcInfo.textContent = 'No officers found in this command.';
            secondIcInfo.classList.remove('text-secondary-foreground');
            secondIcInfo.classList.add('text-danger');
            
            oicSelectTrigger.disabled = false;
            oicSelectText.textContent = 'Select OIC';
            
            secondIcSelectTrigger.disabled = false;
            secondIcSelectText.textContent = 'Select 2IC (Optional)';
        }
        
        // Setup search functionality
        setupOfficerSearch('oic');
        setupOfficerSearch('second_ic');
        
        // Update assignment dropdowns
        updateAssignmentDropdowns();
        
        // Enable assignment triggers
        document.querySelectorAll('.assignment-officer-trigger').forEach(trigger => {
            trigger.disabled = false;
        });
        
        // Setup assignment officer dropdowns
        setupAssignmentOfficerDropdowns();
        
        isInitialLoad = false;
    })
    .catch(error => {
        console.error('Error loading officers:', error);
        oicOptions.innerHTML = '<div class="p-3 text-sm text-danger text-center">Error loading officers</div>';
        oicInfo.textContent = 'Error loading officers. Please try again.';
        oicInfo.classList.remove('text-secondary-foreground');
        oicInfo.classList.add('text-danger');
        oicSelectTrigger.disabled = false;
        oicSelectText.textContent = 'Error loading officers';
        
        secondIcOptions.innerHTML = '<div class="p-3 text-sm text-danger text-center">Error loading officers</div>';
        secondIcInfo.textContent = 'Error loading officers. Please try again.';
        secondIcInfo.classList.remove('text-secondary-foreground');
        secondIcInfo.classList.add('text-danger');
        secondIcSelectTrigger.disabled = false;
        secondIcSelectText.textContent = 'Error loading officers';
    });
}

function renderOfficerOptions(officersList, optionsContainer, hiddenInput, selectText, dropdown, searchInput, prefix, initialId) {
    if (officersList.length === 0) {
        optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
        return;
    }
    
    optionsContainer.innerHTML = officersList.map(officer => {
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
    
    // Set initial selection if provided
    if (initialId) {
        const initialOfficer = officersList.find(o => o.id == initialId);
        if (initialOfficer) {
            hiddenInput.value = initialId;
            const details = initialOfficer.service_number !== 'N/A' ? initialOfficer.service_number : '';
            const rank = initialOfficer.rank !== 'N/A' ? ' - ' + initialOfficer.rank : '';
            const displayText = initialOfficer.name + (details ? ' (' + details + rank + ')' : '');
            selectText.textContent = displayText;
        }
    }
    
    // Add click handlers
    optionsContainer.querySelectorAll('.officer-option').forEach(option => {
        option.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const service = this.dataset.service;
            const rank = this.dataset.rank;
            
            // Update hidden input
            hiddenInput.value = id;
            
            // Update display text
            const displayText = name + (service !== 'N/A' && service ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
            selectText.textContent = displayText;
            
            // Close dropdown
            dropdown.classList.add('hidden');
            
            // Clear search
            searchInput.value = '';
            
            // Update OIC/2IC validation
            updateOic2icOptions();
        });
    });
}

function setupOfficerSearch(prefix) {
    const searchInput = document.getElementById(`${prefix}_search_input`);
    const optionsContainer = document.getElementById(`${prefix}_options`);
    
    if (!searchInput || !optionsContainer) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filtered = officers.filter(officer => {
            const nameMatch = officer.name.toLowerCase().includes(searchTerm);
            const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
            const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
            return nameMatch || serviceMatch || rankMatch;
        });
        
        renderOfficerOptions(filtered, optionsContainer, 
            document.getElementById(prefix === 'oic' ? 'oic_officer_id' : 'second_in_command_officer_id'),
            document.getElementById(prefix === 'oic' ? 'oic_select_text' : 'second_ic_select_text'),
            document.getElementById(prefix === 'oic' ? 'oic_dropdown' : 'second_ic_dropdown'),
            searchInput, prefix, null);
    });
}

function clearOfficers() {
    const oicSelectTrigger = document.getElementById('oic_select_trigger');
    const oicSelectText = document.getElementById('oic_select_text');
    const oicHiddenInput = document.getElementById('oic_officer_id');
    const oicDropdown = document.getElementById('oic_dropdown');
    const oicOptions = document.getElementById('oic_options');
    const oicInfo = document.getElementById('oic_info');
    
    const secondIcSelectTrigger = document.getElementById('second_ic_select_trigger');
    const secondIcSelectText = document.getElementById('second_ic_select_text');
    const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
    const secondIcDropdown = document.getElementById('second_ic_dropdown');
    const secondIcOptions = document.getElementById('second_ic_options');
    const secondIcInfo = document.getElementById('second_ic_info');
    
    oicSelectText.textContent = 'Select command first, then choose OIC...';
    oicSelectTrigger.disabled = true;
    oicHiddenInput.value = '';
    oicDropdown.classList.add('hidden');
    oicOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select command first</div>';
    oicInfo.textContent = '';
    
    secondIcSelectText.textContent = 'Select command first, then choose 2IC...';
    secondIcSelectTrigger.disabled = true;
    secondIcHiddenInput.value = '';
    secondIcDropdown.classList.add('hidden');
    secondIcOptions.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select command first</div>';
    secondIcInfo.textContent = '';
    
    officers = [];
    updateAssignmentDropdowns();
    
    // Clear all assignment officer dropdowns
    document.querySelectorAll('.assignment-officer-options').forEach(optionsContainer => {
        optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select command first</div>';
    });
    
    document.querySelectorAll('.assignment-officer-trigger').forEach(trigger => {
        trigger.disabled = true;
        const assignmentIndex = trigger.id.replace('assignment-officer-', '').replace('-trigger', '');
        const selectText = document.getElementById(`assignment-officer-${assignmentIndex}-text`);
        if (selectText) {
            selectText.textContent = 'Select command first, then choose officer...';
        }
    });
}

// Get officers available for assignments (excludes OIC and 2IC)
function getAvailableOfficersForAssignments() {
    const oicHiddenInput = document.getElementById('oic_officer_id');
    const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
    const oicId = oicHiddenInput ? oicHiddenInput.value : null;
    const secondIcId = secondIcHiddenInput ? secondIcHiddenInput.value : null;
    
    // Use officers from current command if available, otherwise use allOfficers
    const officersList = officers.length > 0 ? officers : allOfficers;
    
    return officersList.filter(officer => {
        const officerId = officer.id || officer.id;
        return officerId != oicId && officerId != secondIcId;
    });
}

// Assignment template
function createAssignmentTemplate(index) {
    return `
        <div class="kt-card shadow-none bg-muted/30 border border-input" data-assignment-index="${index}">
            <div class="kt-card-content p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-mono">Assignment #${index + 1}</span>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-assignment-btn" data-index="${index}">
                        <i class="ki-filled ki-trash"></i> Remove
                    </button>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="block text-sm font-medium mb-1">Officer <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" 
                                    name="assignments[${index}][officer_id]" 
                                    id="assignment-officer-${index}"
                                    required>
                            <button type="button" 
                                    id="assignment-officer-${index}-trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer assignment-officer-trigger"
                                    ${!officers.length ? 'disabled' : ''}>
                                <span id="assignment-officer-${index}-text">
                                    ${officers.length ? 'Select Officer' : 'Select command first, then choose officer...'}
                                </span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="assignment-officer-${index}-dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden assignment-officer-dropdown">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="assignment-officer-${index}-search-input" 
                                               class="kt-input w-full assignment-officer-search" 
                                               placeholder="Search officers..."
                                               autocomplete="off"
                                               data-assignment-index="${index}">
                        </div>
                    </div>
                                <!-- Options Container -->
                                <div id="assignment-officer-${index}-options" class="max-h-60 overflow-y-auto assignment-officer-options">
                                    <div class="p-3 text-sm text-secondary-foreground text-center">
                                        ${officers.length ? 'Loading officers...' : 'Select command first'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Shift</label>
                        <input type="text" class="kt-input" name="assignments[${index}][shift]" placeholder="e.g., Morning, Evening, Night"/>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Add assignment
function addAssignment() {
    const container = document.getElementById('assignments-container');
    const assignmentHtml = createAssignmentTemplate(assignmentCount);
    container.insertAdjacentHTML('beforeend', assignmentHtml);
    assignmentCount++;
    updateRemoveButtons();
    // Make sure new assignment dropdown excludes OIC/2IC
    updateAssignmentDropdowns();
    // Setup the new assignment dropdown
    setupAssignmentOfficerDropdowns();
}

// Remove assignment
function removeAssignment(index) {
    const assignment = document.querySelector(`[data-assignment-index="${index}"]`);
    if (assignment) {
        assignment.remove();
        updateAssignmentNumbers();
    }
}

// Update assignment numbers
function updateAssignmentNumbers() {
    const assignments = document.querySelectorAll('[data-assignment-index]');
    assignments.forEach((assignment, index) => {
        const oldIndex = assignment.getAttribute('data-assignment-index');
        assignment.setAttribute('data-assignment-index', index);
        
        const title = assignment.querySelector('.text-mono');
        if (title) {
            title.textContent = `Assignment #${index + 1}`;
        }
        
        // Update all inputs and selects
        assignment.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('assignments[')) {
                const newName = name.replace(/assignments\[\d+\]/, `assignments[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });
    assignmentCount = assignments.length;
    updateRemoveButtons();
}

// Update remove buttons
function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-assignment-btn');
    removeButtons.forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    document.querySelectorAll('.remove-assignment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            removeAssignment(index);
        });
    });
}

// Update assignment dropdowns to exclude OIC and 2IC
function updateAssignmentDropdowns() {
    const oicHiddenInput = document.getElementById('oic_officer_id');
    const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
    const oicId = oicHiddenInput ? oicHiddenInput.value : null;
    const secondIcId = secondIcHiddenInput ? secondIcHiddenInput.value : null;
    
    // Get available officers (exclude OIC and 2IC)
    const availableOfficers = officers.filter(officer => {
        const officerId = officer.id;
        return officerId != oicId && officerId != secondIcId;
    });
    
    // Update all assignment officer dropdowns
    document.querySelectorAll('.assignment-officer-options').forEach(optionsContainer => {
        const assignmentIndex = optionsContainer.id.replace('assignment-officer-', '').replace('-options', '');
        const hiddenInput = document.getElementById(`assignment-officer-${assignmentIndex}`);
        const currentValue = hiddenInput ? hiddenInput.value : null;
        
        if (availableOfficers.length > 0) {
            renderAssignmentOfficerOptions(availableOfficers, optionsContainer, hiddenInput, assignmentIndex, currentValue);
        } else {
            optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers available (OIC and 2IC are excluded)</div>';
        }
    });
}

function renderAssignmentOfficerOptions(officersList, optionsContainer, hiddenInput, assignmentIndex, currentValue) {
    if (officersList.length === 0) {
        optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers available</div>';
                return;
            }
            
    optionsContainer.innerHTML = officersList.map(officer => {
        const details = officer.service_number !== 'N/A' ? officer.service_number : '';
        const rank = officer.rank !== 'N/A' ? ' - ' + officer.rank : '';
        const displayText = officer.name + (details ? ' (' + details + rank + ')' : '');
        
        return `
            <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 assignment-officer-option" 
                 data-id="${officer.id}" 
                 data-name="${officer.name}"
                 data-service="${details}"
                 data-rank="${officer.rank}">
                <div class="text-sm text-foreground">${officer.name}</div>
                <div class="text-xs text-secondary-foreground">${details}${rank}</div>
            </div>
        `;
    }).join('');
    
    // Set current selection if provided
    if (currentValue) {
        const currentOfficer = officersList.find(o => o.id == currentValue);
        if (currentOfficer) {
            const selectText = document.getElementById(`assignment-officer-${assignmentIndex}-text`);
            if (selectText) {
                const details = currentOfficer.service_number !== 'N/A' ? currentOfficer.service_number : '';
                const rank = currentOfficer.rank !== 'N/A' ? ' - ' + currentOfficer.rank : '';
                const displayText = currentOfficer.name + (details ? ' (' + details + rank + ')' : '');
                selectText.textContent = displayText;
            }
        }
    }
    
    // Add click handlers
    optionsContainer.querySelectorAll('.assignment-officer-option').forEach(option => {
        option.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const service = this.dataset.service;
            const rank = this.dataset.rank;
            
            // Update hidden input
            if (hiddenInput) {
                hiddenInput.value = id;
            }
            
            // Update display text
            const selectText = document.getElementById(`assignment-officer-${assignmentIndex}-text`);
            const dropdown = document.getElementById(`assignment-officer-${assignmentIndex}-dropdown`);
            const searchInput = document.getElementById(`assignment-officer-${assignmentIndex}-search-input`);
            
            if (selectText) {
                const displayText = name + (service !== 'N/A' && service ? ' (' + service + (rank !== 'N/A' ? ' - ' + rank : '') + ')' : '');
                selectText.textContent = displayText;
            }
            
            // Close dropdown
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
            
            // Clear search
            if (searchInput) {
                searchInput.value = '';
            }
        });
    });
}

function setupAssignmentOfficerDropdowns() {
    // Setup triggers for all assignment officer dropdowns
    document.querySelectorAll('.assignment-officer-trigger').forEach(trigger => {
        const assignmentIndex = trigger.id.replace('assignment-officer-', '').replace('-trigger', '');
        const dropdown = document.getElementById(`assignment-officer-${assignmentIndex}-dropdown`);
        
        if (!dropdown) return;
        
        // Remove existing listeners by cloning
        const newTrigger = trigger.cloneNode(true);
        trigger.parentNode.replaceChild(newTrigger, trigger);
        
        newTrigger.addEventListener('click', function(e) {
            if (this.disabled) return;
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    const searchInput = document.getElementById(`assignment-officer-${assignmentIndex}-search-input`);
                    if (searchInput) {
                        searchInput.focus();
                    }
                }, 100);
            }
        });
        
        // Setup search for this assignment
        const searchInput = document.getElementById(`assignment-officer-${assignmentIndex}-search-input`);
        if (searchInput) {
            // Remove existing listeners
            const newSearchInput = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearchInput, searchInput);
            
            newSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const oicHiddenInput = document.getElementById('oic_officer_id');
                const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
                const oicId = oicHiddenInput ? oicHiddenInput.value : null;
                const secondIcId = secondIcHiddenInput ? secondIcHiddenInput.value : null;
                
                const filtered = officers.filter(officer => {
                    const officerId = officer.id;
                    // Exclude OIC and 2IC
                    if (officerId == oicId || officerId == secondIcId) {
                        return false;
                    }
                    const nameMatch = officer.name.toLowerCase().includes(searchTerm);
                    const serviceMatch = officer.service_number && officer.service_number.toLowerCase().includes(searchTerm);
                    const rankMatch = officer.rank && officer.rank.toLowerCase().includes(searchTerm);
                    return nameMatch || serviceMatch || rankMatch;
                });
                
                const optionsContainer = document.getElementById(`assignment-officer-${assignmentIndex}-options`);
                const hiddenInput = document.getElementById(`assignment-officer-${assignmentIndex}`);
                const currentValue = hiddenInput ? hiddenInput.value : null;
                
                renderAssignmentOfficerOptions(filtered, optionsContainer, hiddenInput, assignmentIndex, currentValue);
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.assignment-officer-dropdown').forEach(dropdown => {
            const assignmentIndex = dropdown.id.replace('assignment-officer-', '').replace('-dropdown', '');
            const trigger = document.getElementById(`assignment-officer-${assignmentIndex}-trigger`);
            if (dropdown && !dropdown.contains(e.target) && trigger && !trigger.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
}

// Prevent OIC from being selected as 2IC and vice versa
function updateOic2icOptions() {
    const oicHiddenInput = document.getElementById('oic_officer_id');
    const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
    
    const oicValue = oicHiddenInput ? oicHiddenInput.value : null;
    const secondIcValue = secondIcHiddenInput ? secondIcHiddenInput.value : null;
    
    // If OIC and 2IC are the same, clear 2IC
    if (oicValue && secondIcValue && oicValue === secondIcValue) {
        secondIcHiddenInput.value = '';
        const secondIcSelectText = document.getElementById('second_ic_select_text');
        if (secondIcSelectText) {
            secondIcSelectText.textContent = 'Select 2IC (Optional)';
        }
    }
    
    // Update assignment dropdowns when OIC/2IC changes
    updateAssignmentDropdowns();
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('add-assignment-btn').addEventListener('click', addAssignment);
    updateRemoveButtons();
    
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
    
    // Load officers if command is already selected
    if (initialCommandId) {
        isInitialLoad = true;
        loadOfficersByCommand(initialCommandId);
    }
    
    // Initialize assignment dropdowns
    setupAssignmentOfficerDropdowns();
    
    // Toggle dropdowns for OIC and 2IC
    document.getElementById('oic_select_trigger')?.addEventListener('click', function(e) {
        if (this.disabled) return;
        e.stopPropagation();
        const dropdown = document.getElementById('oic_dropdown');
        dropdown.classList.toggle('hidden');
        
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => {
                document.getElementById('oic_search_input').focus();
            }, 100);
        }
    });
    
    document.getElementById('second_ic_select_trigger')?.addEventListener('click', function(e) {
        if (this.disabled) return;
        e.stopPropagation();
        const dropdown = document.getElementById('second_ic_dropdown');
        dropdown.classList.toggle('hidden');
        
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => {
                document.getElementById('second_ic_search_input').focus();
            }, 100);
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const oicDropdown = document.getElementById('oic_dropdown');
        const oicTrigger = document.getElementById('oic_select_trigger');
        if (oicDropdown && !oicDropdown.contains(e.target) && !oicTrigger.contains(e.target)) {
            oicDropdown.classList.add('hidden');
        }
        
        const secondIcDropdown = document.getElementById('second_ic_dropdown');
        const secondIcTrigger = document.getElementById('second_ic_select_trigger');
        if (secondIcDropdown && !secondIcDropdown.contains(e.target) && !secondIcTrigger.contains(e.target)) {
            secondIcDropdown.classList.add('hidden');
        }
    });
    
    // Unit dropdown handling
    const unitSelectEdit = document.getElementById('unit-select-edit');
    const unitCustomInputEdit = document.getElementById('unit-custom-input-edit');
    
    if (unitSelectEdit && unitCustomInputEdit) {
        unitSelectEdit.addEventListener('change', function() {
            if (this.value === '__NEW__') {
                unitCustomInputEdit.classList.remove('hidden');
                unitCustomInputEdit.required = true;
                unitSelectEdit.required = false;
                unitCustomInputEdit.focus();
            } else {
                unitCustomInputEdit.classList.add('hidden');
                unitCustomInputEdit.required = false;
                unitSelectEdit.required = true;
                if (this.value) {
                    unitCustomInputEdit.value = '';
                }
            }
        });
        
        // Handle form submission - same logic as create form
        const form = document.getElementById('roster-edit-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (unitSelectEdit.value === '__NEW__') {
                    if (!unitCustomInputEdit.value.trim()) {
                        e.preventDefault();
                        alert('Please enter a unit name');
                        unitCustomInputEdit.focus();
                        return false;
                    }
                    // Set the custom unit value to the select
                    unitSelectEdit.value = unitCustomInputEdit.value.trim();
                }
            });
        }
    }
    
    // Form validation before submit
    document.getElementById('roster-edit-form').addEventListener('submit', function(e) {
        const commandId = document.getElementById('command_id') ? document.getElementById('command_id').value : null;
        const oicHiddenInput = document.getElementById('oic_officer_id');
        const secondIcHiddenInput = document.getElementById('second_in_command_officer_id');
        const oicValue = oicHiddenInput ? oicHiddenInput.value : null;
        const secondIcValue = secondIcHiddenInput ? secondIcHiddenInput.value : null;
        
        if (!commandId) {
            e.preventDefault();
            alert('Please select a command.');
            return false;
        }
        
        if (oicValue && secondIcValue && oicValue === secondIcValue) {
            e.preventDefault();
            alert('The Officer in Charge (OIC) cannot be the same as the Second In Command (2IC). Please select different officers.');
            return false;
        }
    });

    // Initialize searchable selects for officer assignments
    function initializeOfficerSearch() {
        document.querySelectorAll('.officer-search-input').forEach(searchInput => {
            const selectId = searchInput.getAttribute('data-select-id');
            const select = document.getElementById(selectId);
            
            if (!select) return;
            
            // Hide search input initially
            searchInput.style.display = 'none';
            
            // Prevent click on search input from bubbling to document
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                this.focus();
            });
            
            searchInput.addEventListener('mousedown', function(e) {
                e.stopPropagation();
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
            });
            
            // Show search input when select is clicked/focused
            select.addEventListener('mousedown', function(e) {
                e.preventDefault();
                searchInput.style.display = 'block';
                searchInput.style.position = 'absolute';
                searchInput.style.top = '0';
                searchInput.style.left = '0';
                searchInput.style.width = '100%';
                searchInput.style.zIndex = '10';
                setTimeout(() => {
                    searchInput.focus();
                }, 10);
            });
            
            select.addEventListener('focus', function() {
                searchInput.style.display = 'block';
                searchInput.style.position = 'absolute';
                searchInput.style.top = '0';
                searchInput.style.left = '0';
                searchInput.style.width = '100%';
                searchInput.style.zIndex = '10';
            });
            
            // Filter options based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const options = select.querySelectorAll('option');
                let visibleCount = 0;
                
                options.forEach(option => {
                    if (option.value === '') {
                        option.style.display = '';
                        visibleCount++;
                        return;
                    }
                    
                    const searchText = option.getAttribute('data-search-text') || option.textContent.toLowerCase();
                    if (searchText.includes(searchTerm)) {
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Expand select to show filtered options
                if (visibleCount > 1 && searchTerm) {
                    select.size = Math.min(visibleCount, 10);
                    select.style.position = 'relative';
                    select.style.zIndex = '11';
                } else {
                    select.size = 1;
                }
            });
            
            // Handle selection
            select.addEventListener('change', function() {
                if (this.value) {
                    // Update search input to show selected value
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption) {
                        searchInput.value = selectedOption.textContent;
                    }
                    searchInput.style.display = 'none';
                    select.size = 1;
                    select.style.position = '';
                    select.style.zIndex = '';
                    
                    // Reset all options visibility
                    select.querySelectorAll('option').forEach(opt => {
                        opt.style.display = '';
                    });
                }
            });
            
            // Allow keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    select.focus();
                    if (select.size === 1) {
                        select.size = Math.min(select.querySelectorAll('option:not([style*="display: none"])').length, 10);
                    }
                }
            });
            
            // Hide search when clicking outside
            document.addEventListener('click', function(e) {
                // Get the parent container
                const parentContainer = searchInput.parentElement;
                
                // Check if click is outside both select and searchInput and their parent
                const clickedOnSelect = select.contains(e.target);
                const clickedOnSearchInput = searchInput === e.target || searchInput.contains(e.target);
                const clickedOnParent = parentContainer && parentContainer.contains(e.target);
                
                // Only hide if click is truly outside
                if (!clickedOnSelect && !clickedOnSearchInput && !clickedOnParent) {
                    searchInput.style.display = 'none';
                    select.size = 1;
                    select.style.position = '';
                    select.style.zIndex = '';
                }
            });
        });
    }
    
    // Initialize on page load
    initializeOfficerSearch();
    
    // Re-initialize when new assignments are added
    const originalAddAssignment = window.addAssignment || addAssignment;
    window.addAssignment = function() {
        originalAddAssignment();
        setTimeout(initializeOfficerSearch, 100);
    };
});
</script>
@endpush
@endsection


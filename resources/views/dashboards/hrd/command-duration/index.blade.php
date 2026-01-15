@extends('layouts.app')

@section('title', 'Command Duration')
@section('page-title', 'Command Duration')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Command Duration</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <div class="text-sm text-danger font-medium">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Search Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Search Officers by Command Duration</h3>
        </div>
        <div class="kt-card-content">
            <form method="POST" action="{{ route($routePrefix . '.command-duration.search') }}" id="search-form" class="flex flex-col gap-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Zone (Required) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Zone <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="zone_id" id="zone_id" value="{{ isset($selected_zone_id) ? $selected_zone_id : '' }}" required>
                            <button type="button" 
                                    id="zone_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer {{ isset($zoneReadOnly) && $zoneReadOnly ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ isset($zoneReadOnly) && $zoneReadOnly ? 'disabled' : '' }}>
                                <span id="zone_select_text">{{ isset($selected_zone_id) && $zones->firstWhere('id', $selected_zone_id) ? $zones->firstWhere('id', $selected_zone_id)->name : 'Select Zone' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="zone_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="zone_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search zones..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="zone_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        @if(isset($zoneReadOnly) && $zoneReadOnly)
                            <input type="hidden" name="zone_id" value="{{ $selected_zone_id }}">
                        @endif
                    </div>

                    <!-- Command (Required) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Command <span class="text-danger">*</span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="command_id" id="command_id" value="{{ isset($selected_command_id) ? $selected_command_id : '' }}" required>
                            <button type="button" 
                                    id="command_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer {{ (!isset($selected_zone_id) || empty($selected_zone_id)) && (!isset($zoneReadOnly) || !$zoneReadOnly) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ (!isset($selected_zone_id) || empty($selected_zone_id)) && (!isset($zoneReadOnly) || !$zoneReadOnly) ? 'disabled' : '' }}>
                                <span id="command_select_text">{{ isset($selected_command_id) && $commands->firstWhere('id', $selected_command_id) ? $commands->firstWhere('id', $selected_command_id)->name : 'Select Command' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="command_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="command_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search commands..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="command_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rank (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Rank</label>
                        <div class="relative">
                            <input type="hidden" name="rank" id="rank_id" value="{{ isset($selected_rank) ? $selected_rank : '' }}">
                            <button type="button" 
                                    id="rank_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="rank_select_text">{{ isset($selected_rank) ? $selected_rank : 'All Ranks' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="rank_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="rank_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search ranks..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="rank_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sex (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Sex</label>
                        <div class="relative">
                            <input type="hidden" name="sex" id="sex_id" value="{{ isset($selected_sex) && $selected_sex != 'Any' ? $selected_sex : 'Any' }}">
                            <button type="button" 
                                    id="sex_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="sex_select_text">{{ isset($selected_sex) ? $selected_sex : 'Any' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="sex_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="sex_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="sex_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Command Duration (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Duration in Command</label>
                        <div class="relative">
                            <input type="hidden" name="duration_years" id="duration_years_id" value="{{ isset($selected_duration) ? $selected_duration : '' }}">
                            <button type="button" 
                                    id="duration_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="duration_select_text">{{ isset($selected_duration) ? ($selected_duration == '10' ? '10+ Years' : $selected_duration . ' Year' . ($selected_duration != '1' ? 's' : '')) : 'All Durations' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="duration_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="duration_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search durations..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="duration_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="resetForm()" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-cross"></i> Reset
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if(isset($officers))
        <div class="kt-card">
            <div class="kt-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="kt-card-title">Search Results ({{ $officers->count() }} officer(s))</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="print-btn" onclick="printResults()" class="kt-btn kt-btn-sm kt-btn-secondary">
                            <i class="ki-filled ki-printer"></i> Print Results
                        </button>
                        <button type="button" id="add-to-draft-btn" data-kt-modal-toggle="#add-to-draft-modal" class="kt-btn kt-btn-sm kt-btn-primary hidden" onclick="prepareAddToDraftModal()">
                            <i class="ki-filled ki-file-add"></i> Add Selected to Draft
                        </button>
                    </div>
                </div>
            </div>
            <div class="kt-card-content">
                @if($officers->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground w-12">
                                        <input type="checkbox" id="select-all" onchange="toggleAll(this)">
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Full Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Date Posted to Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Duration in Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($officers as $officer)
                                    @php
                                        $isDisabled = !$officer->is_eligible_for_movement;
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors {{ $isDisabled ? 'opacity-60' : '' }}">
                                        <td class="py-3 px-4">
                                            <input type="checkbox" 
                                                   class="officer-checkbox" 
                                                   value="{{ $officer->id }}"
                                                   {{ $isDisabled ? 'disabled' : '' }}
                                                   onchange="updateAddButton()">
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground">{{ $officer->service_number }}</td>
                                        <td class="py-3 px-4 text-sm text-foreground">{{ $officer->full_name }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->substantive_rank }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->presentStation->name ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->date_posted_to_command ? $officer->date_posted_to_command->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->duration_display }}</td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                @if($officer->current_status === 'Active')
                                                    <span class="kt-badge kt-badge-success kt-badge-sm">{{ $officer->current_status }}</span>
                                                @elseif($officer->current_status === 'Under Investigation')
                                                    <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $officer->current_status }}</span>
                                                @else
                                                    <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $officer->current_status }}</span>
                                                @endif
                                                @if($officer->is_in_draft)
                                                    <span class="kt-badge kt-badge-info kt-badge-sm" title="Officer already in draft deployment">
                                                        <i class="ki-filled ki-file-add"></i> In Draft
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4">
                            @foreach($officers as $officer)
                                @php
                                    $isDisabled = !$officer->is_eligible_for_movement;
                                @endphp
                                <div class="p-4 rounded-lg bg-muted/50 border border-input {{ $isDisabled ? 'opacity-60' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" 
                                               class="officer-checkbox mt-1" 
                                               value="{{ $officer->id }}"
                                               {{ $isDisabled ? 'disabled' : '' }}
                                               onchange="updateAddButton()">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-semibold text-foreground">{{ $officer->full_name }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if($officer->current_status === 'Active')
                                                        <span class="kt-badge kt-badge-success kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @elseif($officer->current_status === 'Under Investigation')
                                                        <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @else
                                                        <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @endif
                                                    @if($officer->is_in_draft)
                                                        <span class="kt-badge kt-badge-info kt-badge-sm">
                                                            <i class="ki-filled ki-file-add"></i> In Draft
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground">
                                                <div>Service #: <span class="font-semibold">{{ $officer->service_number }}</span></div>
                                                <div>Rank: <span class="font-semibold">{{ $officer->substantive_rank }}</span></div>
                                                <div>Command: <span class="font-semibold">{{ $officer->presentStation->name ?? 'N/A' }}</span></div>
                                                <div>Duration: <span class="font-semibold">{{ $officer->duration_display }}</span></div>
                                                <div class="col-span-2">Posted: <span class="font-semibold">{{ $officer->date_posted_to_command ? $officer->date_posted_to_command->format('d/m/Y') : 'N/A' }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-search text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-2">No officers found matching your criteria</p>
                        <p class="text-xs text-secondary-foreground">Try adjusting your filters</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Add to Draft Form (Hidden) -->
<form id="add-to-draft-form" method="POST" action="{{ route($routePrefix . '.command-duration.add-to-draft') }}" style="display: none;">
    @csrf
    <input type="hidden" name="command_id" id="draft-command-id" value="{{ $selected_command_id ?? (request('command_id') ?? '') }}">
    <input type="hidden" name="officer_ids" id="draft-officer-ids">
</form>

<!-- Add to Draft Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="add-to-draft-modal">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-file-add text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Add Officers to Draft</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground mb-4">
                Add <strong id="selected-officers-count">0</strong> officer(s) to draft deployment?
            </p>
            <div class="p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-xs text-info">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Note:</strong> Officers will be added to the draft deployment. You can review, remove, or swap officers in the draft before publishing.
                </p>
            </div>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" onclick="submitAddToDraft()">
                <i class="ki-filled ki-file-add"></i> Add to Draft
            </button>
        </div>
    </div>
</div>

<script>
const routePrefix = '{{ $routePrefix }}';

// Data for searchable selects
@php
    $zonesData = $zones->map(function($zone) {
        return ['id' => $zone->id, 'name' => $zone->name];
    })->values();
    $commandsData = $commands->map(function($command) {
        return ['id' => $command->id, 'name' => $command->name];
    })->values();
    $ranksData = collect($ranks)->map(function($rank) {
        return ['id' => $rank, 'name' => $rank];
    })->values();
    $sexOptions = [
        ['id' => 'Any', 'name' => 'Any'],
        ['id' => 'Male', 'name' => 'Male'],
        ['id' => 'Female', 'name' => 'Female']
    ];
    $durationOptions = [
        ['id' => '', 'name' => 'All Durations'],
        ['id' => '0', 'name' => '0 Years'],
        ['id' => '1', 'name' => '1 Year'],
        ['id' => '2', 'name' => '2 Years'],
        ['id' => '3', 'name' => '3 Years'],
        ['id' => '4', 'name' => '4 Years'],
        ['id' => '5', 'name' => '5 Years'],
        ['id' => '6', 'name' => '6 Years'],
        ['id' => '7', 'name' => '7 Years'],
        ['id' => '8', 'name' => '8 Years'],
        ['id' => '9', 'name' => '9 Years'],
        ['id' => '10', 'name' => '10+ Years']
    ];
@endphp
let zonesData = @json($zonesData);
let commandsData = @json($commandsData);
let ranksData = @json($ranksData);
let sexOptions = @json($sexOptions);
let durationOptions = @json($durationOptions);

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
        if (this.disabled) return;
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

function loadCommands() {
    const zoneId = document.getElementById('zone_id').value;
    const commandHiddenInput = document.getElementById('command_id');
    const commandTrigger = document.getElementById('command_select_trigger');
    const commandDisplayText = document.getElementById('command_select_text');
    const commandOptionsContainer = document.getElementById('command_options');
    
    if (!zoneId) {
        commandTrigger.disabled = true;
        commandTrigger.classList.add('opacity-50', 'cursor-not-allowed');
        commandDisplayText.textContent = 'Select Command';
        commandHiddenInput.value = '';
        commandOptionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select zone first</div>';
        return;
    }
    
    // Preserve currently selected command (if any)
    const currentSelectedCommand = commandHiddenInput.value;
    
    // Load commands via AJAX
    const indexRoute = routePrefix === 'zone-coordinator' 
        ? '{{ route("zone-coordinator.command-duration.index") }}'
        : '{{ route("hrd.command-duration.index") }}';
    
    fetch(`${indexRoute}?zone_id=${zoneId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.commands && data.commands.length > 0) {
            commandsData = data.commands.map(cmd => ({id: cmd.id, name: cmd.name}));
            
            // Re-render command options
            const commandOptions = [{id: '', name: 'Select Command'}, ...commandsData];
            commandOptionsContainer.innerHTML = commandOptions.map(cmd => {
                const display = cmd.name;
                const value = cmd.id || '';
                const isSelected = currentSelectedCommand && cmd.id == currentSelectedCommand;
                if (isSelected) {
                    commandHiddenInput.value = value;
                    commandDisplayText.textContent = display;
                }
                return `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                         data-id="${value}" 
                         data-name="${display}">
                        <div class="text-sm text-foreground">${display}</div>
                    </div>
                `;
            }).join('');
            
            // Re-add click handlers
            commandOptionsContainer.querySelectorAll('.select-option').forEach(option => {
                option.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    commandHiddenInput.value = id;
                    commandDisplayText.textContent = name;
                    document.getElementById('command_dropdown').classList.add('hidden');
                    document.getElementById('command_search_input').value = '';
                });
            });
        } else {
            commandOptionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No commands found</div>';
        }
        commandTrigger.disabled = false;
        commandTrigger.classList.remove('opacity-50', 'cursor-not-allowed');
    })
    .catch(error => {
        console.error('Error loading commands:', error);
        // Fallback: reload page
        window.location.href = `${indexRoute}?zone_id=${zoneId}`;
    });
}

function resetForm() {
    document.getElementById('search-form').reset();
    const commandTrigger = document.getElementById('command_select_trigger');
    const commandDisplayText = document.getElementById('command_select_text');
    const commandHiddenInput = document.getElementById('command_id');
    commandTrigger.disabled = true;
    commandTrigger.classList.add('opacity-50', 'cursor-not-allowed');
    commandDisplayText.textContent = 'Select Command';
    commandHiddenInput.value = '';
    document.getElementById('command_options').innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">Select zone first</div>';
    
    // Reset other selects
    document.getElementById('rank_id').value = '';
    document.getElementById('rank_select_text').textContent = 'All Ranks';
    document.getElementById('sex_id').value = 'Any';
    document.getElementById('sex_select_text').textContent = 'Any';
    document.getElementById('duration_years_id').value = '';
    document.getElementById('duration_select_text').textContent = 'All Durations';
    
    // If zone is read-only, restore the pre-selected zone
    @if(isset($zoneReadOnly) && $zoneReadOnly && isset($selected_zone_id))
        document.getElementById('zone_id').value = '{{ $selected_zone_id }}';
        document.getElementById('zone_select_text').textContent = '{{ $zones->firstWhere("id", $selected_zone_id)->name ?? "Select Zone" }}';
        loadCommands();
    @endif
}

function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.officer-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateAddButton();
}

function updateAddButton() {
    const checkboxes = document.querySelectorAll('.officer-checkbox:checked:not(:disabled)');
    const addBtn = document.getElementById('add-to-draft-btn');
    
    if (checkboxes.length > 0) {
        addBtn.classList.remove('hidden');
    } else {
        addBtn.classList.add('hidden');
    }
}

function prepareAddToDraftModal() {
    const checkboxes = document.querySelectorAll('.officer-checkbox:checked:not(:disabled)');
    const officerIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (officerIds.length === 0) {
        alert('Please select at least one eligible officer to add to draft.');
        // Hide modal if no officers selected
        const modal = document.getElementById('add-to-draft-modal');
        if (modal) {
            modal.style.display = 'none';
        }
        return;
    }
    
    // Ensure command_id is set
    const commandId = document.getElementById('command_id').value;
    if (!commandId) {
        alert('Please select a command first.');
        return;
    }
    
    // Update modal content with count
    const countElement = document.getElementById('selected-officers-count');
    if (countElement) {
        countElement.textContent = officerIds.length;
    }
    
    // Store officer IDs and command ID for form submission
    document.getElementById('draft-command-id').value = commandId;
    document.getElementById('draft-officer-ids').value = JSON.stringify(officerIds);
}

function submitAddToDraft() {
    document.getElementById('add-to-draft-form').submit();
}

function printResults() {
    // Get all form values
    const form = document.getElementById('search-form');
    const formData = new FormData(form);
    
    // Build query string
    const params = new URLSearchParams();
    params.append('zone_id', formData.get('zone_id'));
    params.append('command_id', formData.get('command_id'));
    if (formData.get('rank')) params.append('rank', formData.get('rank'));
    if (formData.get('sex') && formData.get('sex') !== 'Any') params.append('sex', formData.get('sex'));
    if (formData.get('duration_years')) params.append('duration_years', formData.get('duration_years'));
    
    // Open print page
    const printRoute = routePrefix === 'zone-coordinator' 
        ? '{{ route("zone-coordinator.command-duration.print") }}'
        : '{{ route("hrd.command-duration.print") }}';
    window.open(`${printRoute}?${params.toString()}`, '_blank');
}

// Initialize all searchable selects
document.addEventListener('DOMContentLoaded', function() {
    // Zone Select
    createSearchableSelect({
        triggerId: 'zone_select_trigger',
        hiddenInputId: 'zone_id',
        dropdownId: 'zone_dropdown',
        searchInputId: 'zone_search_input',
        optionsContainerId: 'zone_options',
        displayTextId: 'zone_select_text',
        options: zonesData,
        placeholder: 'Select Zone',
        searchPlaceholder: 'Search zones...',
        onSelect: function(selected) {
            loadCommands();
        }
    });

    // Command Select (will be populated by loadCommands)
    createSearchableSelect({
        triggerId: 'command_select_trigger',
        hiddenInputId: 'command_id',
        dropdownId: 'command_dropdown',
        searchInputId: 'command_search_input',
        optionsContainerId: 'command_options',
        displayTextId: 'command_select_text',
        options: commandsData.length > 0 ? [{id: '', name: 'Select Command'}, ...commandsData] : [{id: '', name: 'Select Command'}],
        placeholder: 'Select Command',
        searchPlaceholder: 'Search commands...'
    });

    // Rank Select
    createSearchableSelect({
        triggerId: 'rank_select_trigger',
        hiddenInputId: 'rank_id',
        dropdownId: 'rank_dropdown',
        searchInputId: 'rank_search_input',
        optionsContainerId: 'rank_options',
        displayTextId: 'rank_select_text',
        options: [{id: '', name: 'All Ranks'}, ...ranksData],
        placeholder: 'All Ranks',
        searchPlaceholder: 'Search ranks...'
    });

    // Sex Select
    createSearchableSelect({
        triggerId: 'sex_select_trigger',
        hiddenInputId: 'sex_id',
        dropdownId: 'sex_dropdown',
        searchInputId: 'sex_search_input',
        optionsContainerId: 'sex_options',
        displayTextId: 'sex_select_text',
        options: sexOptions,
        placeholder: 'Any',
        searchPlaceholder: 'Search...'
    });

    // Duration Select
    createSearchableSelect({
        triggerId: 'duration_select_trigger',
        hiddenInputId: 'duration_years_id',
        dropdownId: 'duration_dropdown',
        searchInputId: 'duration_search_input',
        optionsContainerId: 'duration_options',
        displayTextId: 'duration_select_text',
        options: durationOptions,
        placeholder: 'All Durations',
        searchPlaceholder: 'Search durations...'
    });

    // Load commands automatically on page load if zone is pre-selected
    const zoneId = document.getElementById('zone_id').value;
    const zoneField = document.getElementById('zone_id');
    const commandTrigger = document.getElementById('command_select_trigger');
    
    // Always load commands via AJAX if zone is selected
    // This ensures fresh data and consistency, especially for Zone Coordinators
    if (zoneId) {
        // If zone is read-only (Zone Coordinator) or commands aren't loaded, fetch them
        if (zoneField.hasAttribute('readonly') || commandTrigger.disabled) {
            loadCommands();
        }
    }
});
</script>
@endsection


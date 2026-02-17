@extends('layouts.app')

@section('title', 'Onboarding - Step 2: Employment Details')
@section('page-title', 'Onboarding - Step 2: Employment Details')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-4 lg:p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold text-sm">✓</div>
                    <span class="text-xs sm:text-sm text-success">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm">2</div>
                    <span class="text-xs sm:text-sm font-medium">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold text-sm">3</div>
                    <span class="text-xs sm:text-sm text-secondary-foreground">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold text-sm">4</div>
                    <span class="text-xs sm:text-sm text-secondary-foreground">Next of Kin</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Employment Details</h3>
        </div>
        <div class="kt-card-content">
            @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <div class="kt-alert-content">
                    <strong class="text-danger">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="onboarding-step2-form" method="POST" action="{{ route('onboarding.step2.save') }}" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of First Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_first_appointment" class="kt-input" value="{{ old('date_of_first_appointment', $savedData['date_of_first_appointment'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date of Present Appointment <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_present_appointment" class="kt-input" value="{{ old('date_of_present_appointment', $savedData['date_of_present_appointment'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Substantive Rank <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="substantive_rank" id="substantive_rank" value="{{ old('substantive_rank', $savedData['substantive_rank'] ?? '') }}" required>
                            <button type="button" 
                                    id="substantive_rank_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="substantive_rank_select_text">{{ old('substantive_rank', $savedData['substantive_rank'] ?? '') ? old('substantive_rank', $savedData['substantive_rank'] ?? '') : 'Select Rank...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="substantive_rank_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="substantive_rank_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search rank..."
                                           autocomplete="off">
                                </div>
                                <div id="substantive_rank_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Salary Grade Level <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="salary_grade_level" id="salary_grade_level" value="{{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') }}" required>
                            <button type="button" 
                                    id="salary_grade_level_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="salary_grade_level_select_text">{{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') ? old('salary_grade_level', $savedData['salary_grade_level'] ?? '') : 'Select Grade Level...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="salary_grade_level_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="salary_grade_level_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search grade level..."
                                           autocomplete="off">
                                </div>
                                <div id="salary_grade_level_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Zone <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="zone_id" id="zone_id" value="{{ old('zone_id', $savedData['zone_id'] ?? '') }}" required>
                            <button type="button" 
                                    id="zone_id_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="zone_id_select_text">@php
                                    $zoneId = old('zone_id', $savedData['zone_id'] ?? '');
                                    $selectedZone = $zoneId ? $zones->firstWhere('id', $zoneId) : null;
                                    echo $selectedZone ? $selectedZone->name : 'Select Zone...';
                                @endphp</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="zone_id_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="zone_id_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search zone..."
                                           autocomplete="off">
                                </div>
                                <div id="zone_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Command/Present Station <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="command_id" id="command_id" value="{{ old('command_id', $savedData['command_id'] ?? '') }}" required>
                            <button type="button" 
                                    id="command_id_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer"
                                    disabled>
                                <span id="command_id_select_text">Select zone first, then select command...</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="command_id_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="command_id_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search command..."
                                           autocomplete="off">
                                </div>
                                <div id="command_id_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Date Posted to Station <span class="text-danger">*</span></label>
                        <input type="date" name="date_posted_to_station" class="kt-input" value="{{ old('date_posted_to_station', $savedData['date_posted_to_station'] ?? '') }}" required/>
                        <span class="error-message text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Unit</label>
                        <div class="relative">
                            <input type="hidden" name="unit" id="unit_id" value="{{ old('unit', $savedData['unit'] ?? '') }}">
                            <button type="button" 
                                    id="unit_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="unit_select_text">{{ old('unit', $savedData['unit'] ?? '') ? old('unit', $savedData['unit'] ?? '') : 'Select Unit...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="unit_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="unit_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search unit..."
                                           autocomplete="off">
                                </div>
                                <div id="unit_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                            <div id="assign_to_transport_container" class="mt-3 hidden">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="assign_to_transport" value="1" class="rounded">
                                    <span class="text-sm text-foreground">Assign to Transport (rank will display with (T))</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Education Section -->
                <div class="flex flex-col gap-5 pt-5 border-t border-input">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Education</h3>
                        <button type="button" id="add-education-btn" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Add Education
                        </button>
                    </div>
                    
                    <div id="education-entries" class="flex flex-col gap-5">
                        <!-- Education entries will be added here dynamically -->
                    </div>
                </div>
                
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step1') }}'" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary w-full sm:flex-1 whitespace-nowrap">Next: Banking Information</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure all asterisks in onboarding forms are red */
    .kt-form-label span.text-danger,
    .kt-form-label .text-danger,
    label span.text-danger,
    label .text-danger {
        color: #dc3545 !important;
    }
    
    /* Error messages should be red only when visible (not hidden) */
    .error-message:not(.hidden) {
        color: #dc3545 !important;
    }
    
    /* Laravel validation errors */
    .kt-alert-danger,
    .kt-alert-danger strong,
    .kt-alert-danger li,
    .kt-alert-danger p {
        color: #dc3545 !important;
    }
</style>
@endpush

@push('scripts')
<script>
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

// Rank to Grade Level mapping
const rankToGradeMap = {
    'CGC': 'GL 17',
    'DCG': 'GL 17',
    'ACG': 'GL 16',
    'CC': 'GL 15',
    'DC': 'GL 14',
    'AC': 'GL 13',
    'CSC': 'GL 12',
    'SC': 'GL 11',
    'DSC': 'GL 10',
    'ASC I': 'GL 09',
    'ASC II': 'GL 08',
    'IC': 'GL 07',
    'AIC': 'GL 06',
    'CA I': 'GL 05',
    'CA II': 'GL 04',
    'CA III': 'GL 03'
};

document.addEventListener('DOMContentLoaded', async () => {
    // Load zones and commands
    const token = window.API_CONFIG?.token || '{{ auth()->user()?->createToken('token')->plainTextToken ?? '' }}';
    const savedZoneId = '{{ old('zone_id', $savedData['zone_id'] ?? '') }}';
    const savedCommandId = '{{ old('command_id', $savedData['command_id'] ?? '') }}';
    
    try {
        // Load zones first
        const zonesRes = await fetch('/api/v1/zones', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        // Load all commands with zone information
        const commandsRes = await fetch('/api/v1/commands', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (zonesRes.ok && commandsRes.ok) {
            const zonesData = await zonesRes.json();
            const commandsData = await commandsRes.json();
            
            // Validate that zones data contains zone objects (not commands)
            // Zones should have 'name' and 'id', and should NOT have 'zone_id' or 'zone' properties
            const zones = zonesData.data || zonesData; // Handle both response formats
            if (zones && Array.isArray(zones)) {
                // Additional validation: ensure we're not getting commands instead of zones
                const firstItem = zones[0];
                if (firstItem && (firstItem.zone_id !== undefined || firstItem.zone !== undefined)) {
                    console.error('ERROR: Zones endpoint appears to be returning commands data instead of zones!', zonesData);
                    alert('Error: Zones endpoint is returning incorrect data. Please contact support.');
                    // Don't return - continue with other initializations
                } else {
                    // Initialize zone searchable select
                const zoneOptions = [
                    {id: '', name: 'Select Zone...'},
                    ...zones.map(zone => ({id: zone.id, name: zone.name}))
                ];
                
                if (document.getElementById('zone_id_select_trigger')) {
                    createSearchableSelect({
                        triggerId: 'zone_id_select_trigger',
                        hiddenInputId: 'zone_id',
                        dropdownId: 'zone_id_dropdown',
                        searchInputId: 'zone_id_search_input',
                        optionsContainerId: 'zone_id_options',
                        displayTextId: 'zone_id_select_text',
                        options: zoneOptions,
                        placeholder: 'Select Zone...',
                        searchPlaceholder: 'Search zone...',
                        onSelect: function(option) {
                            // Load commands when zone is selected
                            if (option.id) {
                                loadCommandsForZone(option.id);
                            } else {
                                // Clear command selection when zone is cleared
                                const commandTrigger = document.getElementById('command_id_select_trigger');
                                const commandSelectText = document.getElementById('command_id_select_text');
                                const commandHiddenInput = document.getElementById('command_id');
                                if (commandTrigger && commandSelectText && commandHiddenInput) {
                                    commandTrigger.disabled = true;
                                    commandSelectText.textContent = 'Select zone first, then select command...';
                                    commandHiddenInput.value = '';
                                }
                                clearError('command_id');
                            }
                        }
                    });
                    
                    // Set initial value if saved and load commands
                    if (savedZoneId) {
                        const savedZone = zones.find(z => z.id == savedZoneId);
                        if (savedZone) {
                            document.getElementById('zone_id').value = savedZoneId;
                            document.getElementById('zone_id_select_text').textContent = savedZone.name;
                            // Load commands for the saved zone
                            setTimeout(() => {
                                loadCommandsForZone(savedZoneId, savedCommandId);
                            }, 100);
                        }
                    }
                }
                }
            } else {
                console.error('Zones data format error:', zonesData);
            }
            
            // Store all commands with zone info
            const commands = commandsData.data || commandsData; // Handle both response formats
            if (commands && Array.isArray(commands)) {
                window.allCommands = commands.map(cmd => ({
                    id: cmd.id,
                    name: cmd.name,
                    zone_id: cmd.zone_id || (cmd.zone ? cmd.zone.id : null)
                }));
                
                // Commands will be loaded when zone is selected (handled in zone onSelect callback)
                // If saved zone exists, it's already handled above in the zone initialization
            } else {
                console.error('Commands data format error:', commandsData);
            }
        } else {
            const zonesText = await zonesRes.text().catch(() => '');
            const commandsText = await commandsRes.text().catch(() => '');
            console.error('Error loading zones or commands:', {
                zonesStatus: zonesRes.status,
                commandsStatus: commandsRes.status,
                zonesText: zonesText,
                commandsText: commandsText,
                zonesUrl: zonesRes.url,
                commandsUrl: commandsRes.url
            });
            
            // Show user-friendly error message
            if (!zonesRes.ok) {
                alert('Failed to load zones. Status: ' + zonesRes.status + '. Please refresh the page or contact support if the issue persists.');
            }
        }
    } catch (error) {
        console.error('Error loading zones/commands:', error);
    }
    
    // Initialize rank select (must happen regardless of zones/commands success)
    try {
        const ranks = @json($ranks ?? []);
        if (ranks.length > 0) {
            const rankOptions = [
                {id: '', name: 'Select Rank...'},
                ...ranks.map(rank => ({id: rank, name: rank}))
            ];
            
            createSearchableSelect({
                triggerId: 'substantive_rank_select_trigger',
                hiddenInputId: 'substantive_rank',
                dropdownId: 'substantive_rank_dropdown',
                searchInputId: 'substantive_rank_search_input',
                optionsContainerId: 'substantive_rank_options',
                displayTextId: 'substantive_rank_select_text',
                options: rankOptions,
                placeholder: 'Select Rank...',
                searchPlaceholder: 'Search rank...',
                onSelect: function(option) {
                    // Auto-populate grade level when rank is selected
                    if (option.id && rankToGradeMap[option.id]) {
                        const gradeLevelHiddenInput = document.getElementById('salary_grade_level');
                        const gradeLevelDisplayText = document.getElementById('salary_grade_level_select_text');
                        if (gradeLevelHiddenInput && gradeLevelDisplayText) {
                            gradeLevelHiddenInput.value = rankToGradeMap[option.id];
                            gradeLevelDisplayText.textContent = rankToGradeMap[option.id];
                            clearError('salary_grade_level');
                        }
                    }
                }
            });
            
            // Set initial value if saved
            const savedRank = '{{ old('substantive_rank', $savedData['substantive_rank'] ?? '') }}';
            if (savedRank) {
                document.getElementById('substantive_rank').value = savedRank;
                document.getElementById('substantive_rank_select_text').textContent = savedRank;
            }
        }
        
        // Initialize grade level select
        const gradeLevels = @json($gradeLevels ?? []);
        if (gradeLevels.length > 0) {
            const gradeLevelOptions = [
                {id: '', name: 'Select Grade Level...'},
                ...gradeLevels.map(level => ({id: level, name: level}))
            ];
            
            createSearchableSelect({
                triggerId: 'salary_grade_level_select_trigger',
                hiddenInputId: 'salary_grade_level',
                dropdownId: 'salary_grade_level_dropdown',
                searchInputId: 'salary_grade_level_search_input',
                optionsContainerId: 'salary_grade_level_options',
                displayTextId: 'salary_grade_level_select_text',
                options: gradeLevelOptions,
                placeholder: 'Select Grade Level...',
                searchPlaceholder: 'Search grade level...'
            });
            
            // Set initial value if saved
            const savedGradeLevel = '{{ old('salary_grade_level', $savedData['salary_grade_level'] ?? '') }}';
            if (savedGradeLevel) {
                document.getElementById('salary_grade_level').value = savedGradeLevel;
                document.getElementById('salary_grade_level_select_text').textContent = savedGradeLevel;
            }
        }
    } catch (error) {
        console.error('Error initializing rank/grade level selects:', error);
    }
    
    // Initialize unit select (GD, SS, Transport with Assign to Transport when SS)
    try {
        const unitOptions = [
            {id: '', name: 'Select Unit...'},
            {id: 'General Duty (GD)', name: 'General Duty (GD)'},
            {id: 'Support Services (SS)', name: 'Support Services (SS)'},
            {id: 'Transport', name: 'Transport'}
        ];
        
        createSearchableSelect({
            triggerId: 'unit_select_trigger',
            hiddenInputId: 'unit_id',
            dropdownId: 'unit_dropdown',
            searchInputId: 'unit_search_input',
            optionsContainerId: 'unit_options',
            displayTextId: 'unit_select_text',
            options: unitOptions,
            placeholder: 'Select Unit...',
            searchPlaceholder: 'Search unit...',
            onSelect: function(option) {
                const container = document.getElementById('assign_to_transport_container');
                const checkbox = document.getElementById('assign_to_transport');
                const unitInput = document.getElementById('unit_id');
                if (container && checkbox && unitInput) {
                    if (option.id === 'Support Services (SS)') {
                        container.classList.remove('hidden');
                        checkbox.checked = false;
                    } else {
                        container.classList.add('hidden');
                        checkbox.checked = false;
                    }
                }
            }
        });
        
        document.getElementById('assign_to_transport')?.addEventListener('change', function() {
            const unitInput = document.getElementById('unit_id');
            const unitText = document.getElementById('unit_select_text');
            if (unitInput && unitText) {
                if (this.checked) {
                    unitInput.value = 'Transport';
                    unitText.textContent = 'Transport';
                } else {
                    unitInput.value = 'Support Services (SS)';
                    unitText.textContent = 'Support Services (SS)';
                }
            }
        });
        
        // Set initial value if saved
        const savedUnit = '{{ old('unit', $savedData['unit'] ?? '') }}';
        if (savedUnit) {
            document.getElementById('unit_id').value = savedUnit;
            document.getElementById('unit_select_text').textContent = savedUnit;
            if (savedUnit === 'Support Services (SS)') {
                document.getElementById('assign_to_transport_container')?.classList.remove('hidden');
            }
            if (savedUnit === 'Transport') {
                document.getElementById('assign_to_transport')?.checked = true;
                document.getElementById('assign_to_transport_container')?.classList.remove('hidden');
            }
        }
    } catch (error) {
        console.error('Error initializing unit select:', error);
    }
    
    // Initialize education entries (must happen regardless of other initializations)
    try {
        initializeEducationSection();
    } catch (error) {
        console.error('Error initializing education section:', error);
    }
    
    // Initialize rank to grade level auto-mapping
    try {
        initializeRankGradeMapping();
    } catch (error) {
        console.error('Error initializing rank grade mapping:', error);
    }
    
    // Form submission handler (must be inside DOMContentLoaded)
    try {
        const form = document.getElementById('onboarding-step2-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!validateStep2()) {
                    const firstError = document.querySelector('.error-message:not(.hidden)');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
                
                this.submit();
            });
            
            // Clear errors on input
            form.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('input', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
        // Clear errors for education selects (they use hidden inputs)
        if (this.name && this.name.includes('education[') && (this.name.includes('[university]') || this.name.includes('[qualification]') || this.name.includes('[discipline]'))) {
            const entryIdMatch = this.name.match(/education\[(\d+)\]/);
            if (entryIdMatch) {
                const entryId = entryIdMatch[1];
                const fieldType = this.name.includes('[university]') ? 'university' : (this.name.includes('[qualification]') ? 'qualification' : 'discipline');
                const trigger = document.getElementById(`education_${fieldType}_${entryId}_select_trigger`);
                const errorSpan = trigger?.parentElement?.querySelector('.error-message');
                if (errorSpan) {
                    errorSpan.textContent = '';
                    errorSpan.classList.add('hidden');
                    trigger?.classList.remove('border-danger');
                }
            }
        }
    });
    input.addEventListener('change', function() {
        clearError(this.name);
        // Clear education field errors
        const errorSpan = this.parentElement?.querySelector('.error-message');
        if (errorSpan && (this.classList.contains('education-year-obtained'))) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            this.classList.remove('border-danger');
        }
        // Clear errors for education selects (they use hidden inputs)
        if (this.name && this.name.includes('education[') && (this.name.includes('[university]') || this.name.includes('[qualification]') || this.name.includes('[discipline]'))) {
            const entryIdMatch = this.name.match(/education\[(\d+)\]/);
            if (entryIdMatch) {
                const entryId = entryIdMatch[1];
                const fieldType = this.name.includes('[university]') ? 'university' : (this.name.includes('[qualification]') ? 'qualification' : 'discipline');
                const trigger = document.getElementById(`education_${fieldType}_${entryId}_select_trigger`);
                const errorSpan = trigger?.parentElement?.querySelector('.error-message');
                if (errorSpan) {
                    errorSpan.textContent = '';
                    errorSpan.classList.add('hidden');
                    trigger?.classList.remove('border-danger');
                }
            }
        }
    });
            });
        }
    } catch (error) {
        console.error('Error setting up form submission handler:', error);
    }
});

// Institution master list (from DB)
const institutionMasterList = @json($institutions ?? []);

// Qualification master list (from DB)
const qualificationMasterList = @json($qualifications ?? []);

// Discipline master list (from DB)
const disciplineMasterList = @json($disciplines ?? []);

const ADD_NEW_VALUE = '__ADD_NEW__';

let educationEntryCount = 0;

function initializeEducationSection() {
    const addBtn = document.getElementById('add-education-btn');
    const entriesContainer = document.getElementById('education-entries');
    
    // Load saved education entries
    const savedEducation = @json(old('education', $savedData['education'] ?? []));
    
    if (savedEducation && Array.isArray(savedEducation) && savedEducation.length > 0) {
        // Handle both array of objects and object with numeric keys
        const educationArray = Array.isArray(savedEducation) 
            ? savedEducation 
            : Object.values(savedEducation);
            
        educationArray.forEach(edu => {
            if (edu && (edu.university || edu.qualification)) {
                addEducationEntry(edu);
            }
        });
    }
    
    // If no saved entries, add one empty entry by default
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
    
    addBtn.addEventListener('click', () => {
        addEducationEntry();
    });
}

function addEducationEntry(data = null) {
    const entriesContainer = document.getElementById('education-entries');
    const entryId = educationEntryCount++;
    
    const entryDiv = document.createElement('div');
    entryDiv.className = 'kt-card p-5 border border-input rounded-lg';
    entryDiv.dataset.entryId = entryId;
    
    const savedUniversity = data && data.university ? data.university : '';
    const savedQualification = data && data.qualification ? data.qualification : '';
    const savedDiscipline = data && data.discipline ? data.discipline : '';
    const savedYearObtained = data && data.year_obtained ? data.year_obtained : '';
    
    entryDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Institution <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][university]" id="education_university_${entryId}_id" value="${savedUniversity}" required>
                    <button type="button" 
                            id="education_university_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_university_${entryId}_select_text">${savedUniversity ? savedUniversity : '-- Select Institution --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_university_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_university_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search institution..."
                                   autocomplete="off">
                        </div>
                        <div id="education_university_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="text"
                           id="education_university_${entryId}_custom"
                           class="kt-input mt-2 hidden"
                           placeholder="Type institution name..."
                           autocomplete="off">
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Entry Qualification <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][qualification]" id="education_qualification_${entryId}_id" value="${savedQualification}" required>
                    <button type="button" 
                            id="education_qualification_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_qualification_${entryId}_select_text">${savedQualification ? savedQualification : '-- Select Qualification --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_qualification_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_qualification_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search qualification..."
                                   autocomplete="off">
                        </div>
                        <div id="education_qualification_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="text"
                           id="education_qualification_${entryId}_custom"
                           class="kt-input mt-2 hidden"
                           placeholder="Type qualification..."
                           autocomplete="off">
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Year Obtained <span class="text-danger">*</span></label>
                <input type="number" 
                       name="education[${entryId}][year_obtained]" 
                       class="kt-input education-year-obtained" 
                       value="${savedYearObtained}"
                       placeholder="e.g., 2020"
                       min="1950"
                       max="${new Date().getFullYear()}"
                       required>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Discipline <span class="text-muted">(Optional)</span></label>
                <div class="relative">
                    <input type="hidden" name="education[${entryId}][discipline]" id="education_discipline_${entryId}_id" value="${savedDiscipline}">
                    <button type="button" 
                            id="education_discipline_${entryId}_select_trigger" 
                            class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                        <span id="education_discipline_${entryId}_select_text">${savedDiscipline ? savedDiscipline : '-- Select Discipline --'}</span>
                        <i class="ki-filled ki-down text-gray-400"></i>
                    </button>
                    <div id="education_discipline_${entryId}_dropdown" 
                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                        <div class="p-3 border-b border-input">
                            <input type="text" 
                                   id="education_discipline_${entryId}_search_input" 
                                   class="kt-input w-full pl-10" 
                                   placeholder="Search discipline..."
                                   autocomplete="off">
                        </div>
                        <div id="education_discipline_${entryId}_options" class="max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="text"
                           id="education_discipline_${entryId}_custom"
                           class="kt-input mt-2 hidden"
                           placeholder="Type discipline (optional)..."
                           autocomplete="off">
                </div>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
        </div>
        <div class="flex items-center justify-end mt-3">
            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger remove-education-btn" onclick="removeEducationEntry(${entryId})">
                <i class="ki-filled ki-trash"></i> Remove
            </button>
        </div>
    `;
    
    entriesContainer.appendChild(entryDiv);
    
    // Initialize all education selects using createSearchableSelect
    if (typeof createSearchableSelect === 'function') {
        // Initialize Institution select
        const institutionOptions = [
            {id: '', name: '-- Select Institution --'},
            {id: ADD_NEW_VALUE, name: '-- Add New Institution (type below) --'},
            ...institutionMasterList.map(name => ({id: name, name: name}))
        ];
        
        createSearchableSelect({
            triggerId: `education_university_${entryId}_select_trigger`,
            hiddenInputId: `education_university_${entryId}_id`,
            dropdownId: `education_university_${entryId}_dropdown`,
            searchInputId: `education_university_${entryId}_search_input`,
            optionsContainerId: `education_university_${entryId}_options`,
            displayTextId: `education_university_${entryId}_select_text`,
            options: institutionOptions,
            placeholder: '-- Select Institution --',
            searchPlaceholder: 'Search institution...',
            onSelect: function(option) {
                const hidden = document.getElementById(`education_university_${entryId}_id`);
                const customInput = document.getElementById(`education_university_${entryId}_custom`);
                const displayText = document.getElementById(`education_university_${entryId}_select_text`);

                if (!hidden || !customInput || !displayText) return;

                if (option.id === ADD_NEW_VALUE) {
                    customInput.classList.remove('hidden');
                    customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                    hidden.value = customInput.value.trim();
                    displayText.textContent = '-- Add New Institution (type below) --';
                    setTimeout(() => customInput.focus(), 0);
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                }
            }
        });

        // Keep hidden value in sync with typed custom institution
        const institutionCustomInput = document.getElementById(`education_university_${entryId}_custom`);
        const institutionHidden = document.getElementById(`education_university_${entryId}_id`);
        if (institutionCustomInput && institutionHidden) {
            institutionCustomInput.addEventListener('input', function() {
                institutionHidden.value = this.value.trim();
            });
        }
        
        // Initialize Entry Qualification select
        const qualificationOptions = [
            {id: '', name: '-- Select Qualification --'},
            {id: ADD_NEW_VALUE, name: '-- Add New Qualification (type below) --'},
            ...qualificationMasterList.map(name => ({id: name, name: name}))
        ];
        
        createSearchableSelect({
            triggerId: `education_qualification_${entryId}_select_trigger`,
            hiddenInputId: `education_qualification_${entryId}_id`,
            dropdownId: `education_qualification_${entryId}_dropdown`,
            searchInputId: `education_qualification_${entryId}_search_input`,
            optionsContainerId: `education_qualification_${entryId}_options`,
            displayTextId: `education_qualification_${entryId}_select_text`,
            options: qualificationOptions,
            placeholder: '-- Select Qualification --',
            searchPlaceholder: 'Search qualification...',
            onSelect: function(option) {
                const hidden = document.getElementById(`education_qualification_${entryId}_id`);
                const customInput = document.getElementById(`education_qualification_${entryId}_custom`);
                const displayText = document.getElementById(`education_qualification_${entryId}_select_text`);

                if (!hidden || !customInput || !displayText) return;

                if (option.id === ADD_NEW_VALUE) {
                    customInput.classList.remove('hidden');
                    customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                    hidden.value = customInput.value.trim();
                    displayText.textContent = '-- Add New Qualification (type below) --';
                    setTimeout(() => customInput.focus(), 0);
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                }
            }
        });

        const qualificationCustomInput = document.getElementById(`education_qualification_${entryId}_custom`);
        const qualificationHidden = document.getElementById(`education_qualification_${entryId}_id`);
        if (qualificationCustomInput && qualificationHidden) {
            qualificationCustomInput.addEventListener('input', function() {
                qualificationHidden.value = this.value.trim();
            });
        }
        
        // Initialize Discipline select
        const disciplineOptions = [
            {id: '', name: '-- Select Discipline --'},
            {id: ADD_NEW_VALUE, name: '-- Add New Discipline (type below) --'},
            ...disciplineMasterList.map(name => ({id: name, name: name}))
        ];
        
        createSearchableSelect({
            triggerId: `education_discipline_${entryId}_select_trigger`,
            hiddenInputId: `education_discipline_${entryId}_id`,
            dropdownId: `education_discipline_${entryId}_dropdown`,
            searchInputId: `education_discipline_${entryId}_search_input`,
            optionsContainerId: `education_discipline_${entryId}_options`,
            displayTextId: `education_discipline_${entryId}_select_text`,
            options: disciplineOptions,
            placeholder: '-- Select Discipline --',
            searchPlaceholder: 'Search discipline...',
            onSelect: function(option) {
                const hidden = document.getElementById(`education_discipline_${entryId}_id`);
                const customInput = document.getElementById(`education_discipline_${entryId}_custom`);
                const displayText = document.getElementById(`education_discipline_${entryId}_select_text`);

                if (!hidden || !customInput || !displayText) return;

                if (option.id === ADD_NEW_VALUE) {
                    customInput.classList.remove('hidden');
                    customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                    hidden.value = customInput.value.trim();
                    displayText.textContent = '-- Add New Discipline (type below) --';
                    setTimeout(() => customInput.focus(), 0);
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                }
            }
        });

        // Keep hidden value in sync with typed custom discipline
        const disciplineCustomInput = document.getElementById(`education_discipline_${entryId}_custom`);
        const disciplineHidden = document.getElementById(`education_discipline_${entryId}_id`);
        if (disciplineCustomInput && disciplineHidden) {
            disciplineCustomInput.addEventListener('input', function() {
                disciplineHidden.value = this.value.trim();
            });
        }
    }
}

function removeEducationEntry(entryId) {
    const entry = document.querySelector(`[data-entry-id="${entryId}"]`);
    if (entry) {
        entry.remove();
    }
    
    // If no entries left, add one
    const entriesContainer = document.getElementById('education-entries');
    if (entriesContainer.children.length === 0) {
        addEducationEntry();
    }
}

// Initialize university search functionality
function initializeUniversitySearch(entryId) {
    const universityInput = document.getElementById(`university_search_${entryId}`);
    const universityDropdown = document.getElementById(`university_dropdown_${entryId}`);
    
    if (!universityInput || !universityDropdown) return;
    
    universityInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            universityDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = institutionMasterList.filter(uni =>
            String(uni).toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            universityDropdown.innerHTML = filtered.map(uni => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + uni + '">' + uni + '</div>'
            ).join('');
            universityDropdown.classList.remove('hidden');
        } else {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    universityDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            universityInput.value = selectedValue;
            universityDropdown.classList.add('hidden');
            clearError(`education[${entryId}][university]`);
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!universityInput.contains(e.target) && !universityDropdown.contains(e.target)) {
            universityDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom university)
    universityInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            universityDropdown.classList.add('hidden');
        }, 200);
    });
}

// Initialize discipline search functionality
function initializeDisciplineSearch(entryId) {
    const disciplineInput = document.getElementById(`discipline_search_${entryId}`);
    const disciplineDropdown = document.getElementById(`discipline_dropdown_${entryId}`);
    const disciplineHidden = document.getElementById(`discipline_hidden_${entryId}`);
    
    if (!disciplineInput || !disciplineDropdown || !disciplineHidden) return;
    
    disciplineInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        // Update hidden field with current value
        disciplineHidden.value = this.value.trim();
        
        if (searchTerm.length === 0) {
            disciplineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = disciplineMasterList.filter(disc =>
            String(disc).toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length > 0) {
            disciplineDropdown.innerHTML = filtered.map(disc => 
                '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0" ' +
                'data-value="' + disc + '">' + disc + '</div>'
            ).join('');
            disciplineDropdown.classList.remove('hidden');
        } else {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Handle selection from dropdown
    disciplineDropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-value]');
        if (option) {
            const selectedValue = option.dataset.value;
            disciplineInput.value = selectedValue;
            disciplineHidden.value = selectedValue;
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!disciplineInput.contains(e.target) && !disciplineDropdown.contains(e.target)) {
            disciplineDropdown.classList.add('hidden');
        }
    });
    
    // Allow free text input (user can type custom discipline)
    disciplineInput.addEventListener('blur', function() {
        // Small delay to allow dropdown click to register
        setTimeout(() => {
            disciplineDropdown.classList.add('hidden');
            // Ensure hidden field is updated
            disciplineHidden.value = this.value.trim();
        }, 200);
    });
}

function loadCommandsForZone(zoneId, savedCommandId = null) {
    // Filter commands by zone - check both zone_id and zone.id
    window.commands = window.allCommands.filter(cmd => {
        const cmdZoneId = cmd.zone?.id || cmd.zone_id;
        return cmdZoneId == zoneId;
    });
    
    // Enable command select button
    const commandTrigger = document.getElementById('command_id_select_trigger');
    const commandSelectText = document.getElementById('command_id_select_text');
    const commandHiddenInput = document.getElementById('command_id');
    
    if (!commandTrigger || !commandSelectText || !commandHiddenInput) {
        return;
    }
    
    if (zoneId && window.commands.length > 0) {
        commandTrigger.disabled = false;
        
        // Create command options
        const commandOptions = [
            {id: '', name: 'Select Command...'},
            ...window.commands.map(cmd => ({id: cmd.id, name: cmd.name}))
        ];
        
        // Remove old event listeners by removing and re-adding the select
        // First, clear any existing initialization
        if (window.commandSelectInstance) {
            // Remove old listeners by recreating elements (simpler approach)
            const oldDropdown = document.getElementById('command_id_dropdown');
            if (oldDropdown) {
                oldDropdown.innerHTML = '<div class="p-3 border-b border-input"><input type="text" id="command_id_search_input" class="kt-input w-full pl-10" placeholder="Search command..." autocomplete="off"></div><div id="command_id_options" class="max-h-60 overflow-y-auto"></div>';
            }
        }
        
        // Initialize command searchable select
        createSearchableSelect({
            triggerId: 'command_id_select_trigger',
            hiddenInputId: 'command_id',
            dropdownId: 'command_id_dropdown',
            searchInputId: 'command_id_search_input',
            optionsContainerId: 'command_id_options',
            displayTextId: 'command_id_select_text',
            options: commandOptions,
            placeholder: 'Select Command...',
            searchPlaceholder: 'Search command...'
        });
        
        window.commandSelectInstance = true;
        
        // Set initial value if saved
        if (savedCommandId) {
            const savedCmd = window.commands.find(c => c.id == savedCommandId);
            if (savedCmd) {
                commandHiddenInput.value = savedCmd.id;
                commandSelectText.textContent = savedCmd.name;
            }
        } else {
            commandHiddenInput.value = '';
            commandSelectText.textContent = 'Select Command...';
        }
    } else {
        // No zone selected or no commands available
        commandTrigger.disabled = true;
        commandSelectText.textContent = 'Select zone first, then select command...';
        commandHiddenInput.value = '';
    }
    
    clearError('command_id');
}

// Validation functions
function showError(field, message) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
        input?.classList.add('border-danger');
    }
}

function clearError(field) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.add('hidden');
        input?.classList.remove('border-danger');
    }
}

function validateStep2() {
    let isValid = true;
    
    const requiredFields = {
        'date_of_first_appointment': 'Date of First Appointment is required',
        'date_of_present_appointment': 'Date of Present Appointment is required',
        'substantive_rank': 'Substantive Rank is required',
        'salary_grade_level': 'Salary Grade Level is required',
        'zone_id': 'Zone is required',
        'command_id': 'Command/Present Station is required',
        'date_posted_to_station': 'Date Posted to Station is required'
    };
    
    // Validate education entries
    const educationCards = document.querySelectorAll('#education-entries .kt-card');
    let hasEducationError = false;
    
    educationCards.forEach((card, index) => {
        const entryId = card.dataset.entryId;
        const university = document.getElementById(`education_university_${entryId}_id`);
        const qualification = document.getElementById(`education_qualification_${entryId}_id`);
        const yearObtained = card.querySelector('.education-year-obtained');
        
        if (!university || !university.value.trim()) {
            const errorSpan = university?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Institution is required';
                errorSpan.classList.remove('hidden');
                const trigger = document.getElementById(`education_university_${entryId}_select_trigger`);
                trigger?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!qualification || !qualification.value.trim()) {
            const errorSpan = qualification?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Entry Qualification is required';
                errorSpan.classList.remove('hidden');
                const trigger = document.getElementById(`education_qualification_${entryId}_select_trigger`);
                trigger?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
        
        if (!yearObtained || !yearObtained.value.trim()) {
            const errorSpan = yearObtained?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Year Obtained is required';
                errorSpan.classList.remove('hidden');
                yearObtained?.classList.add('border-danger');
            }
            isValid = false;
            hasEducationError = true;
        }
    });

    // Clear all errors first
    Object.keys(requiredFields).forEach(field => clearError(field));

    // Validate required fields
    Object.keys(requiredFields).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = input?.value?.trim();
        
        if (!value || value === '') {
            showError(field, requiredFields[field]);
            isValid = false;
        }
    });
    
    // Validate that if zone is selected, command must be selected
    const zoneId = document.querySelector('[name="zone_id"]')?.value;
    const commandId = document.querySelector('[name="command_id"]')?.value;
    if (zoneId && !commandId) {
        showError('command_id', 'Please select a command from the selected zone');
        isValid = false;
    }

    // Validate date logic
    const dofa = document.querySelector('[name="date_of_first_appointment"]')?.value;
    const dopa = document.querySelector('[name="date_of_present_appointment"]')?.value;
    const dopts = document.querySelector('[name="date_posted_to_station"]')?.value;

    if (dofa && dopa && new Date(dofa) > new Date(dopa)) {
        showError('date_of_present_appointment', 'Date of Present Appointment must be after Date of First Appointment');
        isValid = false;
    }

    return isValid;
}

// Auto-populate grade level when rank is selected
function initializeRankGradeMapping() {
    const rankHiddenInput = document.getElementById('substantive_rank');
    const gradeLevelHiddenInput = document.getElementById('salary_grade_level');
    const gradeLevelDisplayText = document.getElementById('salary_grade_level_select_text');
    
    if (rankHiddenInput && gradeLevelHiddenInput) {
        // Listen for changes on the rank hidden input
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const selectedRank = rankHiddenInput.value;
                    if (selectedRank && rankToGradeMap[selectedRank]) {
                        gradeLevelHiddenInput.value = rankToGradeMap[selectedRank];
                        if (gradeLevelDisplayText) {
                            gradeLevelDisplayText.textContent = rankToGradeMap[selectedRank];
                        }
                        clearError('salary_grade_level');
                    } else if (!selectedRank) {
                        gradeLevelHiddenInput.value = '';
                        if (gradeLevelDisplayText) {
                            gradeLevelDisplayText.textContent = 'Select Grade Level...';
                        }
                    }
                }
            });
        });
        observer.observe(rankHiddenInput, { attributes: true, attributeFilter: ['value'] });

        // Also listen for direct value changes
        rankHiddenInput.addEventListener('input', function() {
            const selectedRank = this.value;
            if (selectedRank && rankToGradeMap[selectedRank]) {
                gradeLevelHiddenInput.value = rankToGradeMap[selectedRank];
                if (gradeLevelDisplayText) {
                    gradeLevelDisplayText.textContent = rankToGradeMap[selectedRank];
                }
                clearError('salary_grade_level');
            } else if (!selectedRank) {
                gradeLevelHiddenInput.value = '';
                if (gradeLevelDisplayText) {
                    gradeLevelDisplayText.textContent = 'Select Grade Level...';
                }
            }
        });
        
        // Auto-populate on page load if rank is already selected
        if (rankHiddenInput.value && rankToGradeMap[rankHiddenInput.value]) {
            gradeLevelHiddenInput.value = rankToGradeMap[rankHiddenInput.value];
            if (gradeLevelDisplayText) {
                gradeLevelDisplayText.textContent = rankToGradeMap[rankHiddenInput.value];
            }
        }
    }
}

// Clear errors on input - moved inside DOMContentLoaded above
</script>
@endpush
@endsection
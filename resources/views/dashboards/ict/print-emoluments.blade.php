@extends('layouts.app')

@section('title', 'Print Emoluments')
@section('page-title', 'Print Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('ict.dashboard') }}">ICT</a>
    <span>/</span>
    <span class="text-primary">Print Emoluments</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Print Emoluments Report</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('ict.emoluments.print.view') }}" target="_blank" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="filter_status_id" value="{{ request('status', 'ALL') }}">
                                <button type="button" 
                                        id="filter_status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_status_select_text">{{ request('status', 'ALL') === 'ALL' ? 'All Statuses' : (request('status') === 'RAISED' ? 'Raised' : (request('status') === 'ASSESSED' ? 'Assessed' : (request('status') === 'VALIDATED' ? 'Validated' : (request('status') === 'AUDITED' ? 'Audited' : (request('status') === 'PROCESSED' ? 'Processed' : 'Rejected'))))) }}</span>
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

                        <!-- Year Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <div class="relative">
                                <input type="hidden" name="year" id="filter_year_id" value="{{ request('year') ?? '' }}">
                                <button type="button" 
                                        id="filter_year_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_year_select_text">{{ request('year') ? request('year') : 'All Years' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_year_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_year_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search years..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_year_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Zone Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <div class="relative">
                                <input type="hidden" name="zone_id" id="filter_zone_id" value="{{ request('zone_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_zone_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_zone_select_text">{{ request('zone_id') ? (\App\Models\Zone::find(request('zone_id'))->name ?? 'All Zones') : 'All Zones' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_zone_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_zone_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search zones..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_zone_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Command Filter -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <div class="relative">
                                <input type="hidden" name="command_id" id="filter_command_id" value="{{ request('command_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_command_select_text">{{ request('command_id') ? (\App\Models\Command::find(request('command_id'))->name ?? 'All Commands') : 'All Commands' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_command_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search commands..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_command_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="kt-input w-full">
                            <p class="text-xs text-secondary-foreground mt-1">Leave empty to use year filter</p>
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="kt-input w-full">
                            <p class="text-xs text-secondary-foreground mt-1">Leave empty to use year filter</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </button>
                        <a href="{{ route('ict.emoluments.print') }}" class="kt-btn kt-btn-outline">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    // Filter options data
    @php
        $statusOptions = [
            ['id' => 'ALL', 'name' => 'All Statuses'],
            ['id' => 'RAISED', 'name' => 'Raised'],
            ['id' => 'ASSESSED', 'name' => 'Assessed'],
            ['id' => 'VALIDATED', 'name' => 'Validated'],
            ['id' => 'AUDITED', 'name' => 'Audited'],
            ['id' => 'PROCESSED', 'name' => 'Processed'],
            ['id' => 'REJECTED', 'name' => 'Rejected']
        ];
        $yearOptions = [];
        for($y = date('Y'); $y >= date('Y') - 10; $y--) {
            $yearOptions[] = ['id' => $y, 'name' => $y];
        }
        $yearOptions = array_merge([['id' => '', 'name' => 'All Years']], $yearOptions);
        $zoneOptions = \App\Models\Zone::where('is_active', true)->orderBy('name')->get()->map(function($zone) {
            return ['id' => $zone->id, 'name' => $zone->name];
        })->values();
        $zoneOptions->prepend(['id' => '', 'name' => 'All Zones']);
        $commandOptions = \App\Models\Command::where('is_active', true)->orderBy('name')->get()->map(function($command) {
            return ['id' => $command->id, 'name' => $command->name];
        })->values();
        $commandOptions->prepend(['id' => '', 'name' => 'All Commands']);
    @endphp
    const statusOptions = @json($statusOptions);
    const yearOptions = @json($yearOptions);
    const zoneOptions = @json($zoneOptions);
    const commandOptions = @json($commandOptions);

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
        createSearchableSelect({
            triggerId: 'filter_status_select_trigger',
            hiddenInputId: 'filter_status_id',
            dropdownId: 'filter_status_dropdown',
            searchInputId: 'filter_status_search_input',
            optionsContainerId: 'filter_status_options',
            displayTextId: 'filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Statuses',
            searchPlaceholder: 'Search status...'
        });

        createSearchableSelect({
            triggerId: 'filter_year_select_trigger',
            hiddenInputId: 'filter_year_id',
            dropdownId: 'filter_year_dropdown',
            searchInputId: 'filter_year_search_input',
            optionsContainerId: 'filter_year_options',
            displayTextId: 'filter_year_select_text',
            options: yearOptions,
            placeholder: 'All Years',
            searchPlaceholder: 'Search years...'
        });

        createSearchableSelect({
            triggerId: 'filter_zone_select_trigger',
            hiddenInputId: 'filter_zone_id',
            dropdownId: 'filter_zone_dropdown',
            searchInputId: 'filter_zone_search_input',
            optionsContainerId: 'filter_zone_options',
            displayTextId: 'filter_zone_select_text',
            options: zoneOptions,
            placeholder: 'All Zones',
            searchPlaceholder: 'Search zones...'
        });

        createSearchableSelect({
            triggerId: 'filter_command_select_trigger',
            hiddenInputId: 'filter_command_id',
            dropdownId: 'filter_command_dropdown',
            searchInputId: 'filter_command_search_input',
            optionsContainerId: 'filter_command_options',
            displayTextId: 'filter_command_select_text',
            options: commandOptions,
            placeholder: 'All Commands',
            searchPlaceholder: 'Search commands...'
        });
    });
</script>
@endsection


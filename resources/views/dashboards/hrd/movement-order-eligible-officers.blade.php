@extends('layouts.app')

@section('title', 'Eligible Officers')
@section('page-title', 'Eligible Officers')

@section('breadcrumbs')
    @if(isset($routePrefix) && $routePrefix === 'zone-coordinator')
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.dashboard') }}">Zone Coordinator</a>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    @endif
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders') }}">Movement Orders</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.show', $order->id) }}">Order #{{ $order->order_number }}</a>
    <span>/</span>
    <span class="text-primary">Eligible Officers</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Movement Order
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

        <!-- Order Info Card -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex flex-col gap-2">
                    <h3 class="text-lg font-semibold">Movement Order #{{ $order->order_number }}</h3>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Criteria: <span class="font-semibold">{{ $criteriaMonths }} months at station</span>
                        </span>
                        @if($order->manningRequest)
                            <span class="text-secondary-foreground">
                                Manning Request: <span class="font-semibold">#{{ $order->manningRequest->id }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-2">Search Officers</label>
                        <input type="text" 
                               id="officer-search-input" 
                               class="kt-input w-full" 
                               placeholder="Search by name, service number, rank, or current station..."
                               autocomplete="off">
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium mb-2">Filter by Rank</label>
                        <div class="relative">
                            <input type="hidden" id="rank-filter" value="">
                            <button type="button" 
                                    id="rank_filter_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="rank_filter_select_text">All Ranks</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="rank_filter_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="rank_filter_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search ranks..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="rank_filter_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium mb-2">Filter by Current Station</label>
                        <div class="relative">
                            <input type="hidden" id="station-filter" value="">
                            <button type="button" 
                                    id="station_filter_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="station_filter_select_text">All Stations</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="station_filter_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="station_filter_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search stations..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="station_filter_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" 
                                onclick="clearFilters()" 
                                class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-cross"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-sm text-secondary-foreground">
                        <span id="visible-count">{{ $officers->count() }}</span> of {{ $officers->count() }} officer(s) visible
                    </span>
                </div>
            </div>
        </div>

        <!-- Eligible Officers Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Eligible Officers</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        <span id="total-count">{{ $officers->count() }}</span> officer(s) found
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($officers->count() > 0)
                    <form action="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.post-officers', $order->id) }}" method="POST" id="post-officers-form">
                        @csrf
                        
                        <!-- Commands Selection -->
                        <div class="p-4 bg-muted/50 border-b border-border">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex-1">
                                    <label class="kt-form-label mb-2">Select Destination Commands</label>
                                    <p class="text-xs text-secondary-foreground mb-3">
                                        Assign each selected officer to a destination command. Officers will be posted to these commands.
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="kt-btn kt-btn-primary" id="post-officers-btn-top" disabled>
                                        <i class="ki-filled ki-check"></i> Post Selected Officers
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="hidden" name="default_command_id" id="default_command_id" value="">
                                <button type="button" 
                                        id="default_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="default_command_select_text">Select default command...</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="default_command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="default_command_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search commands..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="default_command_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="apply-default-command" class="kt-btn kt-btn-sm kt-btn-secondary mt-2">
                                Apply to All Selected
                            </button>
                        </div>

                        <!-- Posting Date -->
                        <div class="p-4 bg-muted/50 border-b border-border">
                            <label class="kt-form-label mb-2">Posting Date</label>
                            <input type="date" name="posting_date" id="posting_date" class="kt-input" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Officers Table -->
                        <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                            <table class="kt-table" style="min-width: 1000px; width: 100%;">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                            <input type="checkbox" id="select-all-officers" class="rounded">
                                        </th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Station</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Months at Station</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Destination Command</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($officers as $index => $officer)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors officer-row" 
                                            data-officer-name="{{ strtolower(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')) }}"
                                            data-service-number="{{ strtolower($officer->service_number ?? '') }}"
                                            data-rank="{{ strtolower($officer->substantive_rank ?? '') }}"
                                            data-station="{{ strtolower($officer->presentStation->name ?? '') }}">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" 
                                                       name="officer_ids[]" 
                                                       value="{{ $officer->id }}" 
                                                       class="officer-checkbox rounded"
                                                       data-index="{{ $index }}">
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-foreground">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->initials }} {{ $officer->surname }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->substantive_rank ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->presentStation->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                <span class="font-semibold">{{ $officer->months_at_station ?? 0 }}</span> months
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="relative">
                                                    <input type="hidden" name="to_command_ids[]" class="command-select-hidden" data-index="{{ $index }}" value="" disabled>
                                                    <button type="button" 
                                                            class="kt-input kt-input-sm w-full text-left flex items-center justify-between cursor-pointer command-select-trigger {{ $index }}"
                                                            data-index="{{ $index }}"
                                                            disabled>
                                                        <span class="command-select-text-{{ $index }}">Select command...</span>
                                                        <i class="ki-filled ki-down text-gray-400"></i>
                                                    </button>
                                                    <div class="command-select-dropdown-{{ $index }} absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden"
                                                         data-index="{{ $index }}">
                                                        <!-- Search Box -->
                                                        <div class="p-3 border-b border-input">
                                                            <div class="relative">
                                                                <input type="text" 
                                                                       class="kt-input w-full pl-10 command-select-search-{{ $index }}" 
                                                                       placeholder="Search commands..."
                                                                       autocomplete="off"
                                                                       data-index="{{ $index }}">
                                                            </div>
                                                        </div>
                                                        <!-- Options Container -->
                                                        <div class="command-select-options-{{ $index }} max-h-60 overflow-y-auto" data-index="{{ $index }}">
                                                            <!-- Options will be populated by JavaScript -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 p-4 border-t border-border">
                            <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.show', $order->id) }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary" id="post-officers-btn" disabled>
                                <i class="ki-filled ki-check"></i> Post Selected Officers
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No eligible officers found matching the criteria.</p>
                        <p class="text-xs text-secondary-foreground">
                            Criteria: Officers who have been at their current station for {{ $criteriaMonths }} months or more.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Data for searchable selects
        @php
            $allRanks = $officers->pluck('substantive_rank')->filter()->unique()->sort()->values();
            $allStations = $officers->pluck('presentStation.name')->filter()->unique()->sort()->values();
            $ranksData = $allRanks->map(function($rank) {
                return ['id' => $rank, 'name' => $rank];
            })->values();
            $stationsData = $allStations->map(function($station) {
                return ['id' => $station, 'name' => $station];
            })->values();
            $commandsData = collect($availableCommands ?? [])->map(function($command) {
                return [
                    'id' => $command->id,
                    'name' => $command->name,
                    'code' => $command->code ?? ''
                ];
            })->values();
        @endphp
        const filterRanks = @json($ranksData);
        const filterStations = @json($stationsData);
        const availableCommands = @json($commandsData);

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

        // Dynamic Search and Filter Functionality
        function filterOfficers() {
            const searchInput = document.getElementById('officer-search-input');
            const rankFilter = document.getElementById('rank-filter');
            const stationFilter = document.getElementById('station-filter');
            
            const searchTerm = (searchInput?.value || '').toLowerCase().trim();
            const selectedRank = rankFilter?.value || '';
            const selectedStation = stationFilter?.value || '';
            
            const rows = document.querySelectorAll('.officer-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const officerName = row.getAttribute('data-officer-name') || '';
                const serviceNumber = row.getAttribute('data-service-number') || '';
                const rank = row.getAttribute('data-rank') || '';
                const station = row.getAttribute('data-station') || '';
                
                // Search filter
                const matchesSearch = !searchTerm || 
                    officerName.includes(searchTerm) ||
                    serviceNumber.includes(searchTerm) ||
                    rank.includes(searchTerm) ||
                    station.includes(searchTerm);
                
                // Rank filter
                const matchesRank = !selectedRank || rank === selectedRank.toLowerCase();
                
                // Station filter
                const matchesStation = !selectedStation || station === selectedStation.toLowerCase();
                
                if (matchesSearch && matchesRank && matchesStation) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update visible count
            const visibleCountEl = document.getElementById('visible-count');
            if (visibleCountEl) {
                visibleCountEl.textContent = visibleCount;
            }
            
            // Update select all checkbox state after filtering
            updateSelectAll();
        }
        
        // Clear filters function
        window.clearFilters = function() {
            const searchInput = document.getElementById('officer-search-input');
            const rankFilter = document.getElementById('rank-filter');
            const stationFilter = document.getElementById('station-filter');
            const rankDisplayText = document.getElementById('rank_filter_select_text');
            const stationDisplayText = document.getElementById('station_filter_select_text');
            
            if (searchInput) searchInput.value = '';
            if (rankFilter) rankFilter.value = '';
            if (stationFilter) stationFilter.value = '';
            if (rankDisplayText) rankDisplayText.textContent = 'All Ranks';
            if (stationDisplayText) stationDisplayText.textContent = 'All Stations';
            filterOfficers();
        };
        
            // Initialize: Ensure all command selects start disabled
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all command select triggers start disabled
            document.querySelectorAll('.command-select-trigger').forEach(trigger => {
                trigger.disabled = true;
                trigger.classList.add('opacity-50', 'cursor-not-allowed');
            });
            // Initialize Rank Filter Select
            createSearchableSelect({
                triggerId: 'rank_filter_select_trigger',
                hiddenInputId: 'rank-filter',
                dropdownId: 'rank_filter_dropdown',
                searchInputId: 'rank_filter_search_input',
                optionsContainerId: 'rank_filter_options',
                displayTextId: 'rank_filter_select_text',
                options: [{id: '', name: 'All Ranks'}, ...filterRanks],
                placeholder: 'All Ranks',
                searchPlaceholder: 'Search ranks...',
                onSelect: function() {
                    filterOfficers();
                }
            });

            // Initialize Station Filter Select
            createSearchableSelect({
                triggerId: 'station_filter_select_trigger',
                hiddenInputId: 'station-filter',
                dropdownId: 'station_filter_dropdown',
                searchInputId: 'station_filter_search_input',
                optionsContainerId: 'station_filter_options',
                displayTextId: 'station_filter_select_text',
                options: [{id: '', name: 'All Stations'}, ...filterStations],
                placeholder: 'All Stations',
                searchPlaceholder: 'Search stations...',
                onSelect: function() {
                    filterOfficers();
                }
            });

            // Initialize Default Command Select
            createSearchableSelect({
                triggerId: 'default_command_select_trigger',
                hiddenInputId: 'default_command_id',
                dropdownId: 'default_command_dropdown',
                searchInputId: 'default_command_search_input',
                optionsContainerId: 'default_command_options',
                displayTextId: 'default_command_select_text',
                options: availableCommands,
                displayFn: (cmd) => cmd.name + (cmd.code ? ' (' + cmd.code + ')' : ''),
                placeholder: 'Select default command...',
                searchPlaceholder: 'Search commands...'
            });

            // Initialize table row command selects
            function initializeTableCommandSelects() {
                const commandSelects = document.querySelectorAll('.command-select-trigger');
                commandSelects.forEach(trigger => {
                    const index = trigger.dataset.index;
                    const hiddenInput = document.querySelector(`.command-select-hidden[data-index="${index}"]`);
                    const dropdown = document.querySelector(`.command-select-dropdown-${index}`);
                    const searchInput = document.querySelector(`.command-select-search-${index}`);
                    const optionsContainer = document.querySelector(`.command-select-options-${index}`);
                    const displayText = document.querySelector(`.command-select-text-${index}`);

                    // Render options
                    function renderCommandOptions() {
                        optionsContainer.innerHTML = availableCommands.map(cmd => {
                            const display = cmd.name + (cmd.code ? ' (' + cmd.code + ')' : '');
                            return `
                                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 table-command-option" 
                                     data-id="${cmd.id}" 
                                     data-name="${display}"
                                     data-index="${index}">
                                    <div class="text-sm text-foreground">${display}</div>
                                </div>
                            `;
                        }).join('');

                        // Add click handlers
                        optionsContainer.querySelectorAll('.table-command-option').forEach(option => {
                            option.addEventListener('click', function() {
                                const id = this.dataset.id;
                                const name = this.dataset.name;
                                hiddenInput.value = id;
                                displayText.textContent = name;
                                dropdown.classList.add('hidden');
                                searchInput.value = '';
                                updatePostButton();
                            });
                        });
                    }

                    // Initial render
                    renderCommandOptions();

                    // Search functionality
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const filtered = availableCommands.filter(cmd => {
                            const display = cmd.name + (cmd.code ? ' (' + cmd.code + ')' : '');
                            return display.toLowerCase().includes(searchTerm);
                        });
                        
                        optionsContainer.innerHTML = filtered.map(cmd => {
                            const display = cmd.name + (cmd.code ? ' (' + cmd.code + ')' : '');
                            return `
                                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 table-command-option" 
                                     data-id="${cmd.id}" 
                                     data-name="${display}"
                                     data-index="${index}">
                                    <div class="text-sm text-foreground">${display}</div>
                                </div>
                            `;
                        }).join('');

                        // Re-add click handlers
                        optionsContainer.querySelectorAll('.table-command-option').forEach(option => {
                            option.addEventListener('click', function() {
                                const id = this.dataset.id;
                                const name = this.dataset.name;
                                hiddenInput.value = id;
                                displayText.textContent = name;
                                dropdown.classList.add('hidden');
                                searchInput.value = '';
                                updatePostButton();
                            });
                        });
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
                });
            }

            initializeTableCommandSelects();

            // Setup search and filter event listeners
            const searchInput = document.getElementById('officer-search-input');
            
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(filterOfficers, 300); // Debounce for 300ms
                });
            }
            document.querySelectorAll('.command-select').forEach(select => {
                select.disabled = true;
            });
            // Initial button state update
            updatePostButton();
            
            // Also check on page load if any checkboxes are already checked (e.g., from browser back button)
            document.querySelectorAll('.officer-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    toggleCommandSelect(checkbox);
                }
            });
            updatePostButton();
        });

        // Select all checkbox
        document.getElementById('select-all-officers')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.officer-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                toggleCommandSelect(cb);
            });
            updatePostButton();
            updateSelectAll();
        });

        // Individual officer checkbox
        document.querySelectorAll('.officer-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleCommandSelect(this);
                updatePostButton();
                updateSelectAll();
            });
        });

        // Toggle command select enabled/disabled based on checkbox
        function toggleCommandSelect(checkbox) {
            const index = checkbox.dataset.index;
            const commandSelectTrigger = document.querySelector(`.command-select-trigger[data-index="${index}"]`);
            const commandSelectHidden = document.querySelector(`.command-select-hidden[data-index="${index}"]`);
            const commandSelectText = document.querySelector(`.command-select-text-${index}`);
            
            if (commandSelectTrigger && commandSelectHidden) {
                if (checkbox.checked) {
                    commandSelectTrigger.disabled = false;
                    commandSelectHidden.disabled = false;
                    commandSelectTrigger.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    commandSelectTrigger.disabled = true;
                    commandSelectHidden.disabled = true;
                    commandSelectTrigger.classList.add('opacity-50', 'cursor-not-allowed');
                    commandSelectHidden.value = '';
                    if (commandSelectText) commandSelectText.textContent = 'Select command...';
                }
            }
        }

        // Update post button state (syncs both top and bottom buttons)
        function updatePostButton() {
            const checkedBoxes = document.querySelectorAll('.officer-checkbox:checked');
            const postBtn = document.getElementById('post-officers-btn');
            const postBtnTop = document.getElementById('post-officers-btn-top');
            
            // If no officers are selected, disable buttons
            if (checkedBoxes.length === 0) {
                if (postBtn) postBtn.disabled = true;
                if (postBtnTop) postBtnTop.disabled = true;
                return;
            }

            // Check if at least one selected officer has a command assigned
            // This allows batch posting - you can post officers that have commands assigned
            let atLeastOneHasCommand = false;
            checkedBoxes.forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelectHidden = document.querySelector(`.command-select-hidden[data-index="${index}"]`);
                const commandSelectTrigger = document.querySelector(`.command-select-trigger[data-index="${index}"]`);
                // Check if select exists, is enabled (not disabled), and has a value
                if (commandSelectHidden && commandSelectTrigger && !commandSelectTrigger.disabled && commandSelectHidden.value && commandSelectHidden.value !== '') {
                    atLeastOneHasCommand = true;
                }
            });

            // Enable buttons if at least one selected officer has a command
            const shouldEnable = atLeastOneHasCommand;
            if (postBtn) {
                postBtn.disabled = !shouldEnable;
                // Update button styling
                if (shouldEnable) {
                    postBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    postBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
            if (postBtnTop) {
                postBtnTop.disabled = !shouldEnable;
                // Update button styling
                if (shouldEnable) {
                    postBtnTop.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    postBtnTop.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Update select all checkbox state
        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('.officer-checkbox');
            const selectAll = document.getElementById('select-all-officers');
            const checkedCount = document.querySelectorAll('.officer-checkbox:checked').length;
            
            if (selectAll) {
                selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        }

        // Command select change handler (for table row selects)
        document.querySelectorAll('.command-select-hidden').forEach(hiddenInput => {
            hiddenInput.addEventListener('change', function() {
                updatePostButton();
            });
        });

        // Make top button trigger form submission
        document.getElementById('post-officers-btn-top')?.addEventListener('click', function(e) {
            // Let the form handle submission naturally
            // The form's submit handler will validate
        });

        // Also trigger update when default command is applied
        document.getElementById('apply-default-command')?.addEventListener('click', function() {
            const defaultCommandId = document.getElementById('default_command_id').value;
            if (!defaultCommandId) {
                alert('Please select a default command first.');
                return;
            }

            const selectedCommand = availableCommands.find(cmd => cmd.id == defaultCommandId);
            if (!selectedCommand) {
                alert('Selected command not found.');
                return;
            }

            const displayText = selectedCommand.name + (selectedCommand.code ? ' (' + selectedCommand.code + ')' : '');

            let appliedCount = 0;
            document.querySelectorAll('.officer-checkbox:checked').forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelectHidden = document.querySelector(`.command-select-hidden[data-index="${index}"]`);
                const commandSelectText = document.querySelector(`.command-select-text-${index}`);
                const commandSelectTrigger = document.querySelector(`.command-select-trigger[data-index="${index}"]`);
                
                if (commandSelectHidden && !commandSelectTrigger.disabled) {
                    commandSelectHidden.value = defaultCommandId;
                    if (commandSelectText) commandSelectText.textContent = displayText;
                    appliedCount++;
                }
            });

            if (appliedCount === 0) {
                alert('Please select at least one officer first.');
                return;
            }

            updatePostButton();
        });


        // Form validation
        document.getElementById('post-officers-form')?.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.officer-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one officer to post.');
                return false;
            }

            // Collect officers with commands (only post those that have commands assigned)
            let officersWithCommands = [];
            let officersWithoutCommands = [];
            
            checkedBoxes.forEach(checkbox => {
                const index = checkbox.dataset.index;
                const commandSelectHidden = document.querySelector(`.command-select-hidden[data-index="${index}"]`);
                const row = checkbox.closest('tr');
                const name = row.querySelector('td:nth-child(3)').textContent.trim();
                
                if (commandSelectHidden && commandSelectHidden.value) {
                    officersWithCommands.push(name);
                } else {
                    officersWithoutCommands.push(name);
                    // Uncheck officers without commands so they won't be posted
                    checkbox.checked = false;
                }
            });

            if (officersWithCommands.length === 0) {
                e.preventDefault();
                alert('Please assign destination commands to at least one selected officer.');
                return false;
            }

            // Show warning if some officers won't be posted
            if (officersWithoutCommands.length > 0) {
                const proceed = confirm(
                    `Only officers with assigned commands will be posted (${officersWithCommands.length} officer(s)).\n\n` +
                    `The following officers will NOT be posted (no command assigned):\n` +
                    officersWithoutCommands.join(', ') +
                    `\n\nDo you want to continue?`
                );
                
                if (!proceed) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Update button state after unchecking
            updatePostButton();
        });
    </script>
    @endpush

    <style>
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection



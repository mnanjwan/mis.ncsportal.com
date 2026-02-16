@extends('layouts.app')

@section('title', 'Officers Who Did Not Submit Emoluments')
@section('page-title', 'Officers Who Did Not Submit Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('ict.dashboard') }}">ICT</a>
    <span>/</span>
    <span class="text-primary">Non-Submitters</span>
@endsection

@section('content')
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter & Print</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('ict.non-submitters') }}" class="flex flex-col gap-4" id="filter-form">
                    <!-- Filter Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       id="search-input"
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-3"
                                       autocomplete="off">
                                <div id="search-autocomplete" class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>

                        <!-- Year Select -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <div class="relative">
                                <input type="hidden" name="year" id="filter_year_id" value="{{ $selectedYear }}">
                                <button type="button" 
                                        id="filter_year_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_year_select_text">{{ $selectedYear }}</span>
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

                        <!-- Zone Select -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <div class="relative">
                                <input type="hidden" name="zone_id" id="filter_zone_id" value="{{ request('zone_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_zone_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_zone_select_text">{{ request('zone_id') ? ($zones->firstWhere('id', request('zone_id'))->name ?? 'All Zones') : 'All Zones' }}</span>
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

                        <!-- Command Select -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <div class="relative">
                                <input type="hidden" name="command_id" id="filter_command_id" value="{{ request('command_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_command_select_text">{{ request('command_id') ? ($commands->firstWhere('id', request('command_id'))->name ?? 'All Commands') : 'All Commands' }}</span>
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
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                        @if(request()->anyFilled(['search', 'zone_id', 'command_id', 'year']))
                            <a href="{{ route('ict.non-submitters') }}" class="kt-btn kt-btn-outline">
                                Clear
                            </a>
                        @endif
                        <button type="button" 
                                class="kt-btn kt-btn-success"
                                onclick="printReport()">
                            <i class="ki-filled ki-printer"></i> Print
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Non-Submitters List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officers Who Did Not Submit Emoluments for {{ $selectedYear }}</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $nonSubmitters->total() }} officers
                    </span>
                </div>
            </div>
            <div class="kt-card-content">
                @if($timeline)
                    <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded">
                        <p class="text-sm text-secondary-foreground">
                            <strong>Timeline:</strong> {{ $timeline->start_date->format('d M Y') }} - 
                            {{ ($timeline->is_extended && $timeline->extension_end_date) ? $timeline->extension_end_date->format('d M Y') : $timeline->end_date->format('d M Y') }}
                        </p>
                    </div>
                @endif

                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Service No
                                            @if(request('sort_by') === 'service_number' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ (request('sort_by') === 'service_number' && request('sort_order') === 'desc') || !request('sort_by') ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer Name
                                            @if(request('sort_by') === 'name')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'rank', 'sort_order' => request('sort_by') === 'rank' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Rank
                                            @if(request('sort_by') === 'rank')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'zone', 'sort_order' => request('sort_by') === 'zone' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Zone
                                            @if(request('sort_by') === 'zone')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'command', 'sort_order' => request('sort_by') === 'command' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Command
                                            @if(request('sort_by') === 'command')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nonSubmitters as $officer)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-foreground">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->display_rank }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->presentStation->zone->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">All officers have submitted their emoluments for {{ $selectedYear }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @forelse($nonSubmitters as $officer)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                                        <i class="ki-filled ki-user text-warning text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Rank: {{ $officer->display_rank }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $officer->presentStation->zone->name ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                | {{ $officer->presentStation->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">All officers have submitted their emoluments for {{ $selectedYear }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($nonSubmitters->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $nonSubmitters->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Filter options data
        @php
            $yearOptions = collect($years)->map(function($year) {
                return ['id' => $year, 'name' => $year];
            })->values();
            $zoneOptions = $zones->map(function($zone) {
                return ['id' => $zone->id, 'name' => $zone->name];
            })->values();
            $zoneOptions->prepend(['id' => '', 'name' => 'All Zones']);
            $commandOptions = $commands->map(function($command) {
                return ['id' => $command->id, 'name' => $command->name];
            })->values();
            $commandOptions->prepend(['id' => '', 'name' => 'All Commands']);
        @endphp
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

        // Initialize filter selects and autocomplete
        document.addEventListener('DOMContentLoaded', function() {
            createSearchableSelect({
                triggerId: 'filter_year_select_trigger',
                hiddenInputId: 'filter_year_id',
                dropdownId: 'filter_year_dropdown',
                searchInputId: 'filter_year_search_input',
                optionsContainerId: 'filter_year_options',
                displayTextId: 'filter_year_select_text',
                options: yearOptions,
                placeholder: 'Select Year',
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

            // Initialize autocomplete for search input
            const searchInput = document.getElementById('search-input');
            const autocompleteDiv = document.getElementById('search-autocomplete');
            let searchTimeout = null;

            if (searchInput && autocompleteDiv) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    
                    // Clear previous timeout
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Hide autocomplete if query is too short
                    if (query.length < 2) {
                        autocompleteDiv.classList.add('hidden');
                        autocompleteDiv.innerHTML = '';
                        return;
                    }

                    // Debounce search requests
                    searchTimeout = setTimeout(() => {
                        // Build search URL - use hrd.officers.search as it's accessible to ICT role
                        const searchUrl = `{{ route('hrd.officers.search') }}?q=${encodeURIComponent(query)}`;
                        
                        fetch(searchUrl)
                            .then(response => response.json())
                            .then(data => {
                                if (!autocompleteDiv) return;
                                
                                autocompleteDiv.innerHTML = '';
                                
                                if (data.length === 0) {
                                    autocompleteDiv.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No officers found</div>';
                                } else {
                                    data.forEach(officer => {
                                        const div = document.createElement('div');
                                        div.className = 'p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 autocomplete-option';
                                        div.innerHTML = `
                                            <div class="text-sm font-medium text-foreground">${officer.initials} ${officer.surname}</div>
                                            <div class="text-xs text-secondary-foreground">${officer.service_number} - ${officer.display_rank || officer.substantive_rank}</div>
                                        `;
                                        div.addEventListener('click', () => {
                                            // Set search input value to service number or name
                                            searchInput.value = officer.service_number || `${officer.initials} ${officer.surname}`;
                                            autocompleteDiv.classList.add('hidden');
                                            autocompleteDiv.innerHTML = '';
                                        });
                                        autocompleteDiv.appendChild(div);
                                    });
                                }
                                autocompleteDiv.classList.remove('hidden');
                            })
                            .catch(error => {
                                console.error('Autocomplete search error:', error);
                                autocompleteDiv.classList.add('hidden');
                            });
                    }, 300); // 300ms debounce
                });

                // Close autocomplete when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
                        autocompleteDiv.classList.add('hidden');
                    }
                });

                // Close autocomplete on form submit
                const filterForm = document.getElementById('filter-form');
                if (filterForm) {
                    filterForm.addEventListener('submit', function() {
                        autocompleteDiv.classList.add('hidden');
                    });
                }
            }
        });

        function printReport() {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Preserve sort parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('sort_by')) {
                params.append('sort_by', urlParams.get('sort_by'));
            }
            if (urlParams.get('sort_order')) {
                params.append('sort_order', urlParams.get('sort_order'));
            }
            
            const printUrl = '{{ route("ict.non-submitters.print") }}?' + params.toString();
            window.open(printUrl, '_blank');
        }
    </script>
    @endpush
@endsection


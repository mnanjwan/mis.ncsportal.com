@extends('layouts.app')

@section('title', 'Processed Emoluments History')
@section('page-title', 'Processed Emoluments History')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Processed History</span>
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
                <h3 class="kt-card-title">Filter & Export</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('accounts.processed-history') }}" class="flex flex-col gap-4" id="filter-form">
                    <!-- Filter Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-10">
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

                        <!-- Year Select -->
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
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date From</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}" 
                                   class="kt-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date To</label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}" 
                                   class="kt-input w-full">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                        @if(request()->anyFilled(['search', 'zone_id', 'command_id', 'year', 'date_from', 'date_to']))
                            <a href="{{ route('accounts.processed-history') }}" class="kt-btn kt-btn-outline">
                                Clear
                            </a>
                        @endif
                        <button type="button" 
                                class="kt-btn kt-btn-primary"
                                onclick="printReport()">
                            <i class="ki-filled ki-printer"></i> Print
                        </button>
                        <button type="button" 
                                class="kt-btn kt-btn-success"
                                onclick="exportReport('csv')">
                            <i class="ki-filled ki-file-down"></i> Export CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Processed Emoluments List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Processed Emoluments</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $emoluments->total() }} records
                    </span>
                </div>
            </div>
            <div class="kt-card-content">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer_id', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer
                                            @if(request('sort_by') === 'officer_id')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_by') === 'year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Year
                                            @if(request('sort_by') === 'year' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ (request('sort_by') === 'year' && request('sort_order') === 'desc') || !request('sort_by') ? 'down' : 'up' }} text-xs"></i>
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
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'processed_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Processed Date
                                            @if(request('sort_by') === 'processed_at' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($emoluments as $emolument)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $emolument->officer->initials ?? '' }}
                                                {{ $emolument->officer->surname ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-mono">
                                                {{ $emolument->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->substantive_rank ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            {{ $emolument->year }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->presentStation->zone->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->processed_at ? $emolument->processed_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No processed emoluments found</p>
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
                        @forelse($emoluments as $emolument)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                        <i class="ki-filled ki-wallet text-success text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Rank: {{ $emolument->officer->substantive_rank ?? 'N/A' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->year }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                | {{ $emolument->officer->presentStation->zone->name ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                | {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-secondary-foreground">
                                            Processed: {{ $emolument->processed_at ? $emolument->processed_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                       class="kt-btn kt-btn-ghost kt-btn-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No processed emoluments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($emoluments->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $emoluments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Filter options data
        @php
            $zoneOptions = $zones->map(function($zone) {
                return ['id' => $zone->id, 'name' => $zone->name];
            })->values();
            $zoneOptions->prepend(['id' => '', 'name' => 'All Zones']);
            $commandOptions = $commands->map(function($command) {
                return ['id' => $command->id, 'name' => $command->name];
            })->values();
            $commandOptions->prepend(['id' => '', 'name' => 'All Commands']);
            $yearOptions = collect($years)->map(function($year) {
                return ['id' => $year, 'name' => $year];
            })->values();
            $yearOptions->prepend(['id' => '', 'name' => 'All Years']);
        @endphp
        const zoneOptions = @json($zoneOptions);
        const commandOptions = @json($commandOptions);
        const yearOptions = @json($yearOptions);

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
        });

        function exportReport(format) {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            formData.append('format', format);
            
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            window.location.href = '{{ route("accounts.processed-history.export") }}?' + params.toString();
        }

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
            
            const printUrl = '{{ route("accounts.processed-history.print") }}?' + params.toString();
            window.open(printUrl, '_blank');
        }
    </script>
    @endpush
@endsection


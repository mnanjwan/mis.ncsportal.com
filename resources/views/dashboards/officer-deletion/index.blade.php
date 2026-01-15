@extends('layouts.app')

@section('title', 'Delete Officer')
@section('page-title', 'Delete Officer')

@section('breadcrumbs')
    @if(auth()->user()->hasRole('HRD'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    @elseif(auth()->user()->hasRole('Establishment'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    @endif
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.index') : route('establishment.officers.delete.index') }}">Delete Officer</a>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Warning Banner -->
        <div class="kt-card border-red-500 bg-red-50 dark:bg-red-950/20">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-red-600 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-semibold text-red-900 dark:text-red-100 mb-1">⚠️ Destructive Action</h3>
                        <p class="text-sm text-red-800 dark:text-red-200">
                            This feature allows permanent deletion of officers and all associated records. This action cannot be undone.
                            Only use this feature when absolutely necessary and after proper authorization.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Officers</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.index') : route('establishment.officers.delete.index') }}" class="flex flex-col gap-4" id="filterForm">
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Zone Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <div class="relative">
                                <input type="hidden" name="zone_id" id="zone_id" value="{{ $selectedZoneId ?? '' }}">
                                <button type="button" 
                                        id="zone_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="zone_select_text">{{ $selectedZoneId ? ($zones->firstWhere('id', $selectedZoneId) ? $zones->firstWhere('id', $selectedZoneId)->name : 'All Zones') : 'All Zones' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="zone_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="zone_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search zone..."
                                               autocomplete="off">
                                    </div>
                                    <div id="zone_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Command Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <div class="relative">
                                <input type="hidden" name="command_id" id="command_id" value="{{ $selectedCommandId ?? '' }}">
                                <button type="button" 
                                        id="command_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="command_select_text">{{ $selectedCommandId ? ($commands->firstWhere('id', $selectedCommandId) ? $commands->firstWhere('id', $selectedCommandId)->name : 'All Commands') : 'All Commands' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="command_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="command_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search command..."
                                               autocomplete="off">
                                    </div>
                                    <div id="command_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="flex-1 w-full md:min-w-[250px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}"
                                   class="kt-input w-full" 
                                   placeholder="Service Number, Name...">
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if($selectedZoneId || $selectedCommandId || $search)
                                <a href="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.index') : route('establishment.officers.delete.index') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Officers List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officers</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Service Number
                                        @if(request('sort_by') === 'service_number')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Name
                                        @if(request('sort_by') === 'name')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
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
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'command', 'sort_order' => request('sort_by') === 'command' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Unit
                                        @if(request('sort_by') === 'command')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Status
                                        @if(request('sort_by') === 'status')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($officers as $officer)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-foreground">
                                            {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm font-medium text-foreground">
                                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->substantive_rank ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->presentStation->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($officer->is_active)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.show', $officer->id) : route('establishment.officers.delete.show', $officer->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-4 text-center text-secondary-foreground">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="ki-filled ki-information-2 text-3xl text-gray-400"></i>
                                            <p>No officers found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($officers->hasPages())
                    <div class="p-4 border-t border-border">
                        {{ $officers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

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

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            
            // Zone options
            const zoneOptions = [
                {id: '', name: 'All Zones'},
                @foreach($zones as $zone)
                {id: '{{ $zone->id }}', name: '{{ $zone->name }}'},
                @endforeach
            ];

            // Command options
            const commandOptions = [
                {id: '', name: 'All Commands'},
                @foreach($commands as $command)
                {id: '{{ $command->id }}', name: '{{ $command->name }}'},
                @endforeach
            ];

            // Initialize zone select
            createSearchableSelect({
                triggerId: 'zone_select_trigger',
                hiddenInputId: 'zone_id',
                dropdownId: 'zone_dropdown',
                searchInputId: 'zone_search_input',
                optionsContainerId: 'zone_options',
                displayTextId: 'zone_select_text',
                options: zoneOptions,
                placeholder: 'All Zones',
                searchPlaceholder: 'Search zone...',
                onSelect: function() {
                    // Clear command selection when zone changes
                    document.getElementById('command_id').value = '';
                    document.getElementById('command_select_text').textContent = 'All Commands';
                    
                    // Clear search when zone changes
                    const searchInput = form.querySelector('input[name="search"]');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    
                    // Submit form to reload with commands for selected zone
                    form.submit();
                }
            });

            // Initialize command select
            createSearchableSelect({
                triggerId: 'command_select_trigger',
                hiddenInputId: 'command_id',
                dropdownId: 'command_dropdown',
                searchInputId: 'command_search_input',
                optionsContainerId: 'command_options',
                displayTextId: 'command_select_text',
                options: commandOptions,
                placeholder: 'All Commands',
                searchPlaceholder: 'Search command...'
            });
        });
    </script>
    @endpush
@endsection


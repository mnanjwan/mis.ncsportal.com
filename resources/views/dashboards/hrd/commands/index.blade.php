@extends('layouts.app')

@section('title', 'Commands')
@section('page-title', 'Commands Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Settings</a>
    <span>/</span>
    <span class="text-primary">Commands</span>
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

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Commands</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('hrd.commands.index') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="w-full md:flex-1">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   class="kt-input w-full" 
                                   placeholder="Search by name, code, or location...">
                        </div>

                        <!-- Zone Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <div class="relative">
                                <input type="hidden" name="zone_id" id="filter_zone_id" value="{{ request('zone_id') ?? '' }}">
                                <button type="button" 
                                        id="filter_zone_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_zone_select_text">{{ request('zone_id') ? ($zones->firstWhere('id', request('zone_id')) ? $zones->firstWhere('id', request('zone_id'))->name : 'All Zones') : 'All Zones' }}</span>
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

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'zone_id']))
                                <a href="{{ route('hrd.commands.index') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Commands Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">All Commands</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.commands.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Command
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Mobile scroll hint -->
                <div class="block md:hidden px-4 py-3 bg-muted/50 border-b border-border">
                    <div class="flex items-center gap-2 text-xs text-secondary-foreground">
                        <i class="ki-filled ki-arrow-left-right"></i>
                        <span>Swipe left to view more columns</span>
                    </div>
                </div>

                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Command Name
                                            @if(request('sort_by') === 'name' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_order' => request('sort_by') === 'code' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Code
                                            @if(request('sort_by') === 'code')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
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
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'location', 'sort_order' => request('sort_by') === 'location' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Location
                                            @if(request('sort_by') === 'location')
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
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($commands as $command)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $command->name }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-secondary-foreground">{{ $command->code }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($command->zone)
                                                <span class="text-sm text-foreground">{{ $command->zone->name }}</span>
                                            @else
                                                <span class="text-xs text-secondary-foreground italic">No Zone</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $command->location ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $command->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $command->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('hrd.commands.show', $command->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('hrd.commands.edit', $command->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="Edit">
                                                    <i class="ki-filled ki-notepad-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No commands found</p>
                                            <a href="{{ route('hrd.commands.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">
                                                <i class="ki-filled ki-plus"></i> Create First Command
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>

                <!-- Pagination -->
                <x-pagination :paginator="$commands->withQueryString()" item-name="commands" />
            </div>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
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

    <script>
        // Data for searchable select
        @php
            $zonesData = $zones->map(function($zone) {
                return ['id' => $zone->id, 'name' => $zone->name];
            })->values();
        @endphp
        const filterZones = @json($zonesData);

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

        // Initialize filter select
        document.addEventListener('DOMContentLoaded', function() {
            // Filter Zone Select
            createSearchableSelect({
                triggerId: 'filter_zone_select_trigger',
                hiddenInputId: 'filter_zone_id',
                dropdownId: 'filter_zone_dropdown',
                searchInputId: 'filter_zone_search_input',
                optionsContainerId: 'filter_zone_options',
                displayTextId: 'filter_zone_select_text',
                options: [{id: '', name: 'All Zones'}, ...filterZones],
                placeholder: 'All Zones',
                searchPlaceholder: 'Search zones...'
            });
        });
    </script>
@endsection


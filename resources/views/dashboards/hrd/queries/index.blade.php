@extends('layouts.app')

@section('title', 'Accepted Queries')
@section('page-title', 'Accepted Queries')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Queries</span>
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
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-foreground">Accepted Queries (Disciplinary Record)</h2>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query List</h3>
        </div>
        <div class="kt-card-content p-5">
            <form method="GET" action="{{ route('hrd.queries.index') }}" class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- Search Input -->
                    <div class="w-full md:flex-1 md:min-w-[250px]">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               class="kt-input w-full" 
                               placeholder="Search by officer name, service number, reason...">
                    </div>

                    <!-- Command Select -->
                    <div class="w-full md:w-48 flex-shrink-0">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                        <div class="relative">
                            <input type="hidden" name="command_id" id="filter_command_id" value="{{ request('command_id') ?? '' }}">
                            <button type="button" 
                                    id="filter_command_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="filter_command_select_text">{{ request('command_id') ? ($commands->firstWhere('id', request('command_id')) ? $commands->firstWhere('id', request('command_id'))->name : 'All Commands') : 'All Commands' }}</span>
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

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary whitespace-nowrap">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                        @if(request('search') || request('command_id'))
                            <a href="{{ route('hrd.queries.index') }}" class="kt-btn kt-btn-sm kt-btn-outline whitespace-nowrap">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden pt-0">
            @if($queries->count() > 0)
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Reason</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Issued By</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Issued Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Reviewed Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queries as $query)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $query->officer->initials }} {{ $query->officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $query->officer->service_number }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $query->officer->presentStation->name ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="max-w-xs truncate" title="{{ $query->reason }}">
                                            {{ Str::limit($query->reason, 50) }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issuedBy->name ?? $query->issuedBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->reviewed_at ? $query->reviewed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('hrd.queries.show', $query->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 md:px-5 py-4 border-t border-border">
                    {{ $queries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No accepted queries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Data for searchable select
    @php
        $commandsData = $commands->map(function($command) {
            return [
                'id' => $command->id,
                'name' => $command->name,
                'code' => $command->code ?? ''
            ];
        })->values();
    @endphp
    const filterCommands = @json($commandsData);

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
        // Filter Command Select
        createSearchableSelect({
            triggerId: 'filter_command_select_trigger',
            hiddenInputId: 'filter_command_id',
            dropdownId: 'filter_command_dropdown',
            searchInputId: 'filter_command_search_input',
            optionsContainerId: 'filter_command_options',
            displayTextId: 'filter_command_select_text',
            options: [{id: '', name: 'All Commands'}, ...filterCommands],
            displayFn: (cmd) => cmd.name + (cmd.code ? ' (' + cmd.code + ')' : ''),
            placeholder: 'All Commands',
            searchPlaceholder: 'Search commands...'
        });
    });
</script>
@endsection


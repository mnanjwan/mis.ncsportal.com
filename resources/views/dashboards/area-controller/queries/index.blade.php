@extends('layouts.app')

@section('title', 'Accepted Queries')
@section('page-title', 'Accepted Queries')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
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
            <div class="kt-card-toolbar">
                <form method="GET" action="{{ route('area-controller.queries.index') }}" id="officer-filter-form" class="inline">
                    <div class="relative">
                        <input type="hidden" name="officer_id" id="officer_id" value="{{ request('officer_id') ?? '' }}">
                        <button type="button" 
                                id="officer_select_trigger" 
                                class="kt-input text-left flex items-center justify-between cursor-pointer">
                            <span id="officer_select_text">{{ request('officer_id') ? ($officers->firstWhere('id', request('officer_id')) ? $officers->firstWhere('id', request('officer_id'))->initials . ' ' . $officers->firstWhere('id', request('officer_id'))->surname . ' (' . $officers->firstWhere('id', request('officer_id'))->service_number . ')' : 'All Officers') : 'All Officers' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="officer_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="officer_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search officers..."
                                       autocomplete="off">
                            </div>
                            <div id="officer_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($queries->count() > 0)
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Officer</th>
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
                                        <a href="{{ route('area-controller.queries.show', $query->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Officer options
        const officerOptions = [
            {id: '', name: 'All Officers'},
            @foreach($officers as $officer)
            {id: '{{ $officer->id }}', name: '{{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }})'},
            @endforeach
        ];

        // Initialize officer select
        createSearchableSelect({
            triggerId: 'officer_select_trigger',
            hiddenInputId: 'officer_id',
            dropdownId: 'officer_dropdown',
            searchInputId: 'officer_search_input',
            optionsContainerId: 'officer_options',
            displayTextId: 'officer_select_text',
            options: officerOptions,
            placeholder: 'All Officers',
            searchPlaceholder: 'Search officers...',
            onSelect: function() {
                // Auto-submit form on selection
                document.getElementById('officer-filter-form').submit();
            }
        });
    });
</script>
@endpush
@endsection


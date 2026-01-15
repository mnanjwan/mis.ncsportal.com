@extends('layouts.app')

@section('title', 'Query Management')
@section('page-title', 'Query Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
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
        <h2 class="text-xl font-semibold text-foreground">Queries</h2>
        <a href="{{ route('staff-officer.queries.create') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i> Issue Query
        </a>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query List</h3>
            <div class="kt-card-toolbar">
                <form method="GET" action="{{ route('staff-officer.queries.index') }}" id="status-filter-form" class="inline">
                    <div class="relative">
                        <input type="hidden" name="status" id="filter_status_id" value="{{ request('status') ?? '' }}">
                        <button type="button" 
                                id="filter_status_select_trigger" 
                                class="kt-input text-left flex items-center justify-between cursor-pointer">
                            <span id="filter_status_select_text">{{ request('status') ? (request('status') === 'PENDING_RESPONSE' ? 'Pending Response' : (request('status') === 'PENDING_REVIEW' ? 'Pending Review' : (request('status') === 'ACCEPTED' ? 'Accepted' : (request('status') === 'DISAPPROVAL' ? 'Disapproval' : (request('status') === 'REJECTED' ? 'Rejected' : 'All Status'))))) : 'All Status' }}</span>
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
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($queries->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 800px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Reason</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Issued Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
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
                                        @if($query->status === 'PENDING_RESPONSE')
                                            <span class="kt-badge kt-badge-warning">Pending Response</span>
                                        @elseif($query->status === 'PENDING_REVIEW')
                                            <span class="kt-badge kt-badge-info">Pending Review</span>
                                        @elseif($query->status === 'ACCEPTED')
                                            <span class="kt-badge kt-badge-success">Accepted</span>
                                        @elseif($query->status === 'DISAPPROVAL')
                                            <span class="kt-badge kt-badge-danger">Disapproval</span>
                                        @else
                                            <span class="kt-badge kt-badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('staff-officer.queries.show', $query->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
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
                    <p class="text-secondary-foreground">No queries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Status options
    const statusOptions = [
        {id: '', name: 'All Status'},
        {id: 'PENDING_RESPONSE', name: 'Pending Response'},
        {id: 'PENDING_REVIEW', name: 'Pending Review'},
        {id: 'ACCEPTED', name: 'Accepted'},
        {id: 'DISAPPROVAL', name: 'Disapproval'},
        {id: 'REJECTED', name: 'Rejected'}
    ];

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

    // Initialize status filter select
    document.addEventListener('DOMContentLoaded', function() {
        createSearchableSelect({
            triggerId: 'filter_status_select_trigger',
            hiddenInputId: 'filter_status_id',
            dropdownId: 'filter_status_dropdown',
            searchInputId: 'filter_status_search_input',
            optionsContainerId: 'filter_status_options',
            displayTextId: 'filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                // Auto-submit form on selection
                document.getElementById('status-filter-form').submit();
            }
        });
    });
</script>
@endsection


@extends('layouts.app')

@section('title', 'Investigations')
@section('page-title', 'Investigations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <span class="text-primary">Investigations</span>
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
        <h2 class="text-xl font-semibold text-foreground">Investigation Records</h2>
        <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-magnifier"></i> Search Officers
        </a>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">All Investigations</h3>
            <div class="kt-card-toolbar flex items-center gap-3">
                <form method="GET" action="{{ route('investigation.index') }}" class="flex items-center gap-3">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by officer name or service number..."
                           class="kt-input">
                    <div class="relative">
                        <input type="hidden" name="status" id="status_id" value="{{ request('status') ?? '' }}">
                        <button type="button" 
                                id="status_select_trigger" 
                                class="kt-input text-left flex items-center justify-between cursor-pointer">
                            <span id="status_select_text">{{ request('status') ? (request('status') === 'INVITED' ? 'Invited' : (request('status') === 'ONGOING_INVESTIGATION' ? 'Ongoing Investigation' : (request('status') === 'INTERDICTED' ? 'Interdicted' : (request('status') === 'SUSPENDED' ? 'Suspended' : (request('status') === 'DISMISSED' ? 'Dismissed' : (request('status') === 'RESOLVED' ? 'Resolved' : 'All Status')))))) : 'All Status' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="status_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="status_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search status..."
                                       autocomplete="off">
                            </div>
                            <div id="status_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                    <button type="submit" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($investigations->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Invited Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status Changed</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Investigation Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($investigations as $investigation)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $investigation->officer->service_number }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($investigation->status === 'INVITED')
                                            <span class="kt-badge kt-badge-info">Invited</span>
                                        @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                                            <span class="kt-badge kt-badge-warning">Ongoing Investigation</span>
                                        @elseif($investigation->status === 'INTERDICTED')
                                            <span class="kt-badge kt-badge-danger">Interdicted</span>
                                        @elseif($investigation->status === 'SUSPENDED')
                                            <span class="kt-badge kt-badge-danger">Suspended</span>
                                        @elseif($investigation->status === 'DISMISSED')
                                            <span class="kt-badge kt-badge-danger">Dismissed</span>
                                        @else
                                            <span class="kt-badge kt-badge-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->invited_at ? $investigation->invited_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->status_changed_at ? $investigation->status_changed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $investigation->investigationOfficer->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('investigation.show', $investigation->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-border">
                    {{ $investigations->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No investigations found</p>
                    <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-primary mt-4">
                        <i class="ki-filled ki-magnifier"></i> Search Officers
                    </a>
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
        // Status options
        const statusOptions = [
            {id: '', name: 'All Status'},
            {id: 'INVITED', name: 'Invited'},
            {id: 'ONGOING_INVESTIGATION', name: 'Ongoing Investigation'},
            {id: 'INTERDICTED', name: 'Interdicted'},
            {id: 'SUSPENDED', name: 'Suspended'},
            {id: 'DISMISSED', name: 'Dismissed'},
            {id: 'RESOLVED', name: 'Resolved'}
        ];

        // Initialize status select
        createSearchableSelect({
            triggerId: 'status_select_trigger',
            hiddenInputId: 'status_id',
            dropdownId: 'status_dropdown',
            searchInputId: 'status_search_input',
            optionsContainerId: 'status_options',
            displayTextId: 'status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                document.querySelector('form[method="GET"]').submit();
            }
        });
    });
</script>
@endpush
@endsection



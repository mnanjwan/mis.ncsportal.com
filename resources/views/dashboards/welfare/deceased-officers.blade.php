@extends('layouts.app')

@section('title', 'Deceased Officers')
@section('page-title', 'Deceased Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('welfare.dashboard') }}">Welfare</a>
    <span>/</span>
    <span class="text-primary">Deceased Officers</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Deceased</span>
                            <span class="text-2xl font-semibold text-mono">{{ $totalCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-secondary/10">
                            <i class="ki-filled ki-heart text-2xl text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Validation</span>
                            <span class="text-2xl font-semibold text-mono">{{ $pendingCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-time text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Validated</span>
                            <span class="text-2xl font-semibold text-mono">{{ $validatedCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-check text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filters</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('welfare.deceased-officers') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="status_id" value="{{ request('status') ?? '' }}">
                                <button type="button" 
                                        id="status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="status_select_text">{{ request('status') ? (request('status') === 'pending' ? 'Pending Validation' : (request('status') === 'validated' ? 'Validated' : 'All')) : 'All' }}</span>
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
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request('status'))
                                <a href="{{ route('welfare.deceased-officers') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deceased Officers Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Deceased Officers</h3>
            </div>
            <div class="kt-card-content">
                @if($deceasedOfficers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Reported Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Date of Death</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Reported By</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deceasedOfficers as $deceased)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-foreground">
                                            {{ $deceased->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-foreground">
                                                    {{ ($deceased->officer->initials ?? '') . ' ' . ($deceased->officer->surname ?? '') }}
                                                </span>
                                                <span class="text-xs text-secondary-foreground">
                                                    {{ $deceased->officer->presentStation->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-foreground">
                                                {{ $deceased->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $deceased->date_of_death->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $deceased->reportedBy->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($deceased->validated_at)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Validated</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('welfare.deceased-officers.show', $deceased->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost"
                                               title="View Details">
                                                <i class="ki-filled ki-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($deceasedOfficers->hasPages())
                        <div class="mt-6 pt-4 border-t border-border">
                            {{ $deceasedOfficers->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-heart text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No deceased officers found</p>
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
            {id: '', name: 'All'},
            {id: 'pending', name: 'Pending Validation'},
            {id: 'validated', name: 'Validated'}
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
            placeholder: 'All',
            searchPlaceholder: 'Search status...'
        });
    });
</script>
@endpush
@endsection

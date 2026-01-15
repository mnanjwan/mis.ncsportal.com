@extends('layouts.app')

@section('title', 'All Emoluments')
@section('page-title', 'All Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('assessor.dashboard') }}">Assessor</a>
    <span>/</span>
    <span class="text-primary">Emoluments</span>
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
                <h3 class="kt-card-title">Filter Emoluments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('assessor.emoluments') }}" class="flex flex-col gap-4">
                    <!-- Preserve sort params -->
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif

                    <!-- Filter Controls -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <div class="relative">
                                <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-10">
                            </div>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full sm:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" name="status" id="status_id" value="{{ request('status') ?? '' }}">
                                <button type="button" 
                                        id="status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="status_select_text">{{ request('status') ? (request('status') === 'RAISED' ? 'Raised' : (request('status') === 'ASSESSED' ? 'Assessed' : (request('status') === 'VALIDATED' ? 'Validated' : (request('status') === 'PROCESSED' ? 'Processed' : (request('status') === 'REJECTED' ? 'Rejected' : 'All Statuses'))))) : 'All Statuses' }}</span>
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

                        <!-- Year Select -->
                        <div class="w-full sm:w-32">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <div class="relative">
                                <input type="hidden" name="year" id="year_id" value="{{ request('year') ?? '' }}">
                                <button type="button" 
                                        id="year_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="year_select_text">{{ request('year') ? request('year') : 'All Years' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="year_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="year_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search year..."
                                               autocomplete="off">
                                    </div>
                                    <div id="year_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status', 'year']))
                                <a href="{{ route('assessor.emoluments') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Emoluments List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emoluments List</h3>
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
                                        Officer
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Year
                                            @if(request('sort_by') === 'year')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Status
                                            @if(request('sort_by') === 'status')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'submitted_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Submitted
                                            @if(request('sort_by') === 'submitted_at' || !request('sort_by'))
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
                                    @php
                                        $statusClass = match ($emolument->status) {
                                            'RAISED' => 'info',
                                            'ASSESSED' => 'primary',
                                            'VALIDATED' => 'success',
                                            'PROCESSED' => 'success',
                                            'REJECTED' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
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
                                        <td class="py-3 px-4 text-sm">
                                            {{ $emolument->year }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->submitted_at ? $emolument->submitted_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($emolument->status === 'RAISED')
                                                <a href="{{ route('assessor.emoluments.assess', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                                    Assess
                                                </a>
                                            @else
                                                <a href="{{ route('assessor.emoluments.show', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No emoluments found</p>
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
                            @php
                                $statusClass = match ($emolument->status) {
                                    'RAISED' => 'info',
                                    'ASSESSED' => 'primary',
                                    'VALIDATED' => 'success',
                                    'PROCESSED' => 'success',
                                    'REJECTED' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-wallet text-primary text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $emolument->submitted_at ? $emolument->submitted_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    @if($emolument->status === 'RAISED')
                                        <a href="{{ route('assessor.emoluments.assess', $emolument->id) }}"
                                           class="kt-btn kt-btn-primary kt-btn-sm">
                                            Assess
                                        </a>
                                    @else
                                        <a href="{{ route('assessor.emoluments.show', $emolument->id) }}"
                                           class="kt-btn kt-btn-ghost kt-btn-sm">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No emoluments found</p>
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
            {id: '', name: 'All Statuses'},
            {id: 'RAISED', name: 'Raised'},
            {id: 'ASSESSED', name: 'Assessed'},
            {id: 'VALIDATED', name: 'Validated'},
            {id: 'PROCESSED', name: 'Processed'},
            {id: 'REJECTED', name: 'Rejected'}
        ];

        // Year options
        const yearOptions = [
            {id: '', name: 'All Years'},
            @foreach($years as $year)
            {id: '{{ $year }}', name: '{{ $year }}'},
            @endforeach
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
            placeholder: 'All Statuses',
            searchPlaceholder: 'Search status...'
        });

        // Initialize year select
        createSearchableSelect({
            triggerId: 'year_select_trigger',
            hiddenInputId: 'year_id',
            dropdownId: 'year_dropdown',
            searchInputId: 'year_search_input',
            optionsContainerId: 'year_options',
            displayTextId: 'year_select_text',
            options: yearOptions,
            placeholder: 'All Years',
            searchPlaceholder: 'Search year...'
        });
    });
</script>
@endpush
@endsection
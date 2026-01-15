@extends('layouts.app')

@section('title', 'Emoluments for Audit')
@section('page-title', 'Emoluments for Audit')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('auditor.dashboard') }}">Auditor</a>
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
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-sm text-secondary-foreground">Pending Audit</span>
                    <span class="text-2xl font-semibold text-warning">{{ $pendingAudit ?? 0 }}</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-sm text-secondary-foreground">Audited Today</span>
                    <span class="text-2xl font-semibold text-success">{{ $auditedToday ?? 0 }}</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-5">
                    <span class="text-sm text-secondary-foreground">Total Audited</span>
                    <span class="text-2xl font-semibold text-primary">{{ $totalAudited ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Emoluments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('auditor.emoluments') }}" class="flex flex-col gap-4">
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
                            <div class="relative">
                                <input type="hidden" name="status" id="filter_status_id" value="{{ request('status') ?? '' }}">
                                <button type="button" 
                                        id="filter_status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_status_select_text">{{ request('status') ? request('status') : 'All Statuses' }}</span>
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
                        </div>

                        <!-- Year Select -->
                        <div class="w-full sm:w-32">
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

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status', 'year']))
                                <a href="{{ route('auditor.emoluments') }}" class="kt-btn kt-btn-outline">
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
                <h3 class="kt-card-title">Emoluments for Audit</h3>
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'validated_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Validated
                                            @if(request('sort_by') === 'validated_at' || !request('sort_by'))
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
                                            'VALIDATED' => 'warning',
                                            'AUDITED' => 'success',
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
                                            {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($emolument->status === 'VALIDATED')
                                                <a href="{{ route('auditor.emoluments.audit', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                                    Audit
                                                </a>
                                            @else
                                                <a href="{{ route('auditor.emoluments.show', $emolument->id) }}"
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
                                    'VALIDATED' => 'warning',
                                    'AUDITED' => 'success',
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
                                        {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    @if($emolument->status === 'VALIDATED')
                                        <a href="{{ route('auditor.emoluments.audit', $emolument->id) }}"
                                           class="kt-btn kt-btn-primary kt-btn-sm">
                                            Audit
                                        </a>
                                    @else
                                        <a href="{{ route('auditor.emoluments.show', $emolument->id) }}"
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

<script>
    // Filter options data
    @php
        $statusOptions = [
            ['id' => '', 'name' => 'All Statuses'],
            ['id' => 'VALIDATED', 'name' => 'Validated'],
            ['id' => 'AUDITED', 'name' => 'Audited']
        ];
        $yearOptions = collect($years)->map(function($year) {
            return ['id' => $year, 'name' => $year];
        })->values();
        $yearOptions->prepend(['id' => '', 'name' => 'All Years']);
    @endphp
    const statusOptions = @json($statusOptions);
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
            triggerId: 'filter_status_select_trigger',
            hiddenInputId: 'filter_status_id',
            dropdownId: 'filter_status_dropdown',
            searchInputId: 'filter_status_search_input',
            optionsContainerId: 'filter_status_options',
            displayTextId: 'filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Statuses',
            searchPlaceholder: 'Search status...'
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
</script>
@endsection


@extends('layouts.app')

@section('title', 'Application History')
@section('page-title', 'Application History')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Home</a>
    <span>/</span>
    <span class="text-primary">Application History</span>
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
                <h3 class="kt-card-title">Filter Applications</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('officer.application-history') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Type Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Type</label>
                            <div class="relative">
                                <input type="hidden" name="type" id="filter_type_id" value="{{ request('type') ?? '' }}">
                                <button type="button" 
                                        id="filter_type_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="filter_type_select_text">{{ request('type') ? ucfirst(request('type')) : 'All Types' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="filter_type_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <!-- Search Box -->
                                    <div class="p-3 border-b border-input">
                                        <div class="relative">
                                            <input type="text" 
                                                   id="filter_type_search_input" 
                                                   class="kt-input w-full pl-10" 
                                                   placeholder="Search types..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <!-- Options Container -->
                                    <div id="filter_type_options" class="max-h-60 overflow-y-auto">
                                        <!-- Options will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
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
                        <div class="w-full md:w-36">
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

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['type', 'status', 'year']))
                                <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Application History</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $applications->total() }} records
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Leave/Pass Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Period
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Days
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Submitted
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $application)
                                    @php
                                        $statusClass = match ($application->status) {
                                            'APPROVED' => 'success',
                                            'REJECTED' => 'danger',
                                            'CANCELLED' => 'secondary',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $application->application_type }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $application->type_name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $application->start_date->format('d/m/Y') }} - 
                                            {{ $application->end_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $application->number_of_days }} days
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $application->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $application->submitted_date ? $application->submitted_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            @if($application->application_type === 'Leave')
                                                <a href="{{ route('officer.leave-applications.show', $application->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @else
                                                <a href="{{ route('officer.pass-applications.show', $application->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No applications found</p>
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
                        @forelse($applications as $application)
                            @php
                                $statusClass = match ($application->status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                    'CANCELLED' => 'secondary',
                                    default => 'warning'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full {{ $application->application_type === 'Leave' ? 'bg-info/10' : 'bg-primary/10' }}">
                                        <i class="ki-filled ki-{{ $application->application_type === 'Leave' ? 'calendar' : 'wallet' }} {{ $application->application_type === 'Leave' ? 'text-info' : 'text-primary' }} text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $application->application_type }} - {{ $application->type_name }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $application->number_of_days }} days
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Submitted: {{ $application->submitted_date ? $application->submitted_date->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                        {{ $application->status }}
                                    </span>
                                    @if($application->application_type === 'Leave')
                                        <a href="{{ route('officer.leave-applications.show', $application->id) }}"
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    @else
                                        <a href="{{ route('officer.pass-applications.show', $application->id) }}"
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No applications found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($applications->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $applications->links() }}
                    </div>
                @endif
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
    // Filter options data
    @php
        $typeOptions = [
            ['id' => '', 'name' => 'All Types'],
            ['id' => 'leave', 'name' => 'Leave'],
            ['id' => 'pass', 'name' => 'Pass']
        ];
        $statusOptions = [
            ['id' => '', 'name' => 'All Statuses'],
            ['id' => 'PENDING', 'name' => 'Pending'],
            ['id' => 'APPROVED', 'name' => 'Approved'],
            ['id' => 'REJECTED', 'name' => 'Rejected'],
            ['id' => 'CANCELLED', 'name' => 'Cancelled']
        ];
        $yearOptions = collect($years)->map(function($year) {
            return ['id' => $year, 'name' => $year];
        })->values();
        $yearOptions->prepend(['id' => '', 'name' => 'All Years']);
    @endphp
    const typeOptions = @json($typeOptions);
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
            triggerId: 'filter_type_select_trigger',
            hiddenInputId: 'filter_type_id',
            dropdownId: 'filter_type_dropdown',
            searchInputId: 'filter_type_search_input',
            optionsContainerId: 'filter_type_options',
            displayTextId: 'filter_type_select_text',
            options: typeOptions,
            placeholder: 'All Types',
            searchPlaceholder: 'Search types...'
        });

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


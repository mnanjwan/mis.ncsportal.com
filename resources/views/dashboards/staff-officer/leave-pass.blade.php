@extends('layouts.app')

@section('title', 'Leave & Pass Management')
@section('page-title', 'Leave & Pass Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Leave & Pass</span>
@endsection

@section('content')
@if(!$command)
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command assignment.</p>
            </div>
        </div>
    </div>
@else
<div class="grid gap-5 lg:gap-7.5">
    <!-- Tabs -->
    <div class="kt-card">
        <div class="kt-card-header">
                <ul class="flex gap-2 border-b border-input">
                    <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'leave' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                        <a href="{{ route('staff-officer.leave-pass', ['type' => 'leave', 'status' => request('status')]) }}">
                    Leave Applications
                        </a>
                </li>
                    <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'pass' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                        <a href="{{ route('staff-officer.leave-pass', ['type' => 'pass', 'status' => request('status')]) }}">
                    Pass Applications
                        </a>
                </li>
            </ul>
        </div>
        <div class="kt-card-content">
                @if($type === 'leave')
            <!-- Leave Applications Tab -->
                <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-mono">Leave Applications</h3>
                    <div class="flex items-center gap-3">
                            <form method="GET" action="{{ route('staff-officer.leave-pass') }}" id="leave-status-filter-form" class="inline">
                                <input type="hidden" name="type" value="leave">
                                <div class="relative">
                                    <input type="hidden" name="status" id="leave_filter_status_id" value="{{ request('status') ?? '' }}">
                                    <button type="button" 
                                            id="leave_filter_status_select_trigger" 
                                            class="kt-input text-left flex items-center justify-between cursor-pointer">
                                        <span id="leave_filter_status_select_text">{{ request('status') ? (request('status') === 'PENDING' ? 'Pending' : (request('status') === 'APPROVED' ? 'Approved' : 'Rejected')) : 'All Status' }}</span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="leave_filter_status_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <!-- Search Box -->
                                        <div class="p-3 border-b border-input">
                                            <div class="relative">
                                                <input type="text" 
                                                       id="leave_filter_status_search_input" 
                                                       class="kt-input w-full pl-10" 
                                                       placeholder="Search status..."
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                        <!-- Options Container -->
                                        <div id="leave_filter_status_options" class="max-h-60 overflow-y-auto">
                                            <!-- Options will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </form>
            </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        @forelse($leaveApplications as $app)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                            <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                                        <i class="ki-filled ki-calendar text-warning text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->leaveType->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                            {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }} ({{ $app->number_of_days }} days)
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                            Applied: {{ $app->submitted_at ? $app->submitted_at->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                                        {{ $app->status }}
                                    </span>
                                    @if($app->status === 'PENDING' && is_null($app->minuted_at))
                                        <form action="{{ route('staff-officer.leave-applications.minute', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary" onclick="return confirm('Minute this application to DC Admin?')">
                                                <i class="ki-filled ki-file-edit"></i> Minute
                                            </button>
                                        </form>
                                    @elseif($app->minuted_at)
                                        <span class="kt-badge kt-badge-info kt-badge-sm">
                                            Minuted
                                        </span>
                                    @endif
                                    @if($app->status === 'APPROVED')
                                        <a href="{{ route('staff-officer.leave-applications.print', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-success">
                                            <i class="ki-filled ki-printer"></i> Print
                                        </a>
                                    @endif
                                    <a href="{{ route('staff-officer.leave-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-8">No leave applications found</p>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        {{ $leaveApplications->links() }}
                            </div>
                @else
                    <!-- Pass Applications Tab -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-mono">Pass Applications</h3>
                        <div class="flex items-center gap-3">
                            <form method="GET" action="{{ route('staff-officer.leave-pass') }}" id="pass-status-filter-form" class="inline">
                                <input type="hidden" name="type" value="pass">
                                <div class="relative">
                                    <input type="hidden" name="status" id="pass_filter_status_id" value="{{ request('status') ?? '' }}">
                                    <button type="button" 
                                            id="pass_filter_status_select_trigger" 
                                            class="kt-input text-left flex items-center justify-between cursor-pointer">
                                        <span id="pass_filter_status_select_text">{{ request('status') ? (request('status') === 'PENDING' ? 'Pending' : (request('status') === 'APPROVED' ? 'Approved' : 'Rejected')) : 'All Status' }}</span>
                                        <i class="ki-filled ki-down text-gray-400"></i>
                                    </button>
                                    <div id="pass_filter_status_dropdown" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                        <!-- Search Box -->
                                        <div class="p-3 border-b border-input">
                                            <div class="relative">
                                                <input type="text" 
                                                       id="pass_filter_status_search_input" 
                                                       class="kt-input w-full pl-10" 
                                                       placeholder="Search status..."
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                        <!-- Options Container -->
                                        <div id="pass_filter_status_options" class="max-h-60 overflow-y-auto">
                                            <!-- Options will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        @forelse($passApplications as $app)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                            <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                        <i class="ki-filled ki-calendar-tick text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->number_of_days }} days
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                            {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                            Applied: {{ $app->submitted_at ? $app->submitted_at->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                                        {{ $app->status }}
                                    </span>
                                    @if($app->status === 'PENDING' && is_null($app->minuted_at))
                                        <form action="{{ route('staff-officer.pass-applications.minute', $app->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary" onclick="return confirm('Minute this application to DC Admin?')">
                                                <i class="ki-filled ki-file-edit"></i> Minute
                                            </button>
                                        </form>
                                    @elseif($app->minuted_at)
                                        <span class="kt-badge kt-badge-info kt-badge-sm">
                                            Minuted
                                        </span>
                                    @endif
                                    @if($app->status === 'APPROVED')
                                        <a href="{{ route('staff-officer.pass-applications.print', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-success">
                                            <i class="ki-filled ki-printer"></i> Print
                                        </a>
                                    @endif
                                    <a href="{{ route('staff-officer.pass-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-8">No pass applications found</p>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        {{ $passApplications->links() }}
                    </div>
                @endif
            </div>
                            </div>
                        </div>
@endif

<script>
    // Status options
    const statusOptions = [
        {id: '', name: 'All Status'},
        {id: 'PENDING', name: 'Pending'},
        {id: 'APPROVED', name: 'Approved'},
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

    // Initialize status filter selects
    document.addEventListener('DOMContentLoaded', function() {
        // Leave status filter
        @if($type === 'leave')
        createSearchableSelect({
            triggerId: 'leave_filter_status_select_trigger',
            hiddenInputId: 'leave_filter_status_id',
            dropdownId: 'leave_filter_status_dropdown',
            searchInputId: 'leave_filter_status_search_input',
            optionsContainerId: 'leave_filter_status_options',
            displayTextId: 'leave_filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                document.getElementById('leave-status-filter-form').submit();
            }
        });
        @endif

        // Pass status filter
        @if($type === 'pass')
        createSearchableSelect({
            triggerId: 'pass_filter_status_select_trigger',
            hiddenInputId: 'pass_filter_status_id',
            dropdownId: 'pass_filter_status_dropdown',
            searchInputId: 'pass_filter_status_search_input',
            optionsContainerId: 'pass_filter_status_options',
            displayTextId: 'pass_filter_status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                document.getElementById('pass-status-filter-form').submit();
            }
        });
        @endif
    });
</script>
@endsection

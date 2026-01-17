@extends('layouts.app')

@section('title', 'APER Forms Management')
@section('page-title', 'APER Forms Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">APER Forms</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filters</h3>
        </div>
        <div class="kt-card-content">
            <form method="GET" action="{{ route('hrd.aper-forms') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if(request('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                @endif
                @if(request('sort_order'))
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif
                <div>
                    <label class="kt-form-label text-sm">Search</label>
                    <input type="text" 
                           name="search" 
                           class="kt-input" 
                           placeholder="Service number, name..."
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label class="kt-form-label text-sm">Status</label>
                    <div class="relative">
                        <input type="hidden" name="status" id="filter_status_id" value="{{ request('status') ?? 'all' }}">
                        <button type="button" 
                                id="filter_status_select_trigger" 
                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                            <span id="filter_status_select_text">
                                @php
                                    $statusLabels = [
                                        'all' => 'All Statuses',
                                        'DRAFT' => 'Draft',
                                        'SUBMITTED' => 'Submitted',
                                        'REPORTING_OFFICER' => 'With Reporting Officer',
                                        'COUNTERSIGNING_OFFICER' => 'With Countersigning Officer',
                                        'OFFICER_REVIEW' => 'Officer Review',
                                        'ACCEPTED' => 'Accepted',
                                        'REJECTED' => 'Rejected',
                                        'FINALIZED' => 'Finalized'
                                    ];
                                    $currentStatus = request('status') ?? 'all';
                                @endphp
                                {{ $statusLabels[$currentStatus] ?? 'All Statuses' }}
                            </span>
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
                <div>
                    <label class="kt-form-label text-sm">Year</label>
                    <input type="number" 
                           name="year" 
                           class="kt-input" 
                           placeholder="Year"
                           value="{{ request('year') }}"
                           min="2020"
                           max="2100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                        <i class="ki-filled ki-magnifier"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Forms List Card -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header flex items-center justify-between">
            <h3 class="kt-card-title">APER Forms</h3>
            <button type="button" 
                    class="kt-btn kt-btn-primary" 
                    data-kt-modal-toggle="#kpi-report-modal">
                <i class="ki-filled ki-file-down"></i> KPI Report
            </button>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($forms->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 1000px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_by') === 'year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
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
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer', 'sort_order' => request('sort_by') === 'officer' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Officer
                                        @if(request('sort_by') === 'officer')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Service Number
                                        @if(request('sort_by') === 'service_number')
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'hrd_score', 'sort_order' => request('sort_by') === 'hrd_score' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        HRD Score
                                        @if(request('sort_by') === 'hrd_score')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'reporting_officer', 'sort_order' => request('sort_by') === 'reporting_officer' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Reporting Officer
                                        @if(request('sort_by') === 'reporting_officer')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'submitted_at', 'sort_order' => request('sort_by') === 'submitted_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Submitted
                                        @if(request('sort_by') === 'submitted_at')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $form)
                                @php
                                    $statusConfig = match($form->status) {
                                        'DRAFT' => ['class' => 'secondary', 'label' => 'Draft'],
                                        'SUBMITTED' => ['class' => 'info', 'label' => 'Submitted'],
                                        'REPORTING_OFFICER' => ['class' => 'warning', 'label' => 'With Reporting Officer'],
                                        'COUNTERSIGNING_OFFICER' => ['class' => 'warning', 'label' => 'With Countersigning Officer'],
                                        'OFFICER_REVIEW' => ['class' => 'primary', 'label' => 'Officer Review'],
                                        'ACCEPTED' => ['class' => 'success', 'label' => 'Accepted'],
                                        'REJECTED' => ['class' => 'danger', 'label' => 'Rejected'],
                                        'FINALIZED' => ['class' => 'info', 'label' => 'Finalized'],
                                        default => ['class' => 'secondary', 'label' => $form->status]
                                    };
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">{{ $form->year }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm text-foreground">{{ $form->officer->initials }} {{ $form->officer->surname }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm text-secondary-foreground">{{ $form->officer->service_number ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $statusConfig['class'] }} kt-badge-sm">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                        @if($form->is_rejected)
                                            <span class="kt-badge kt-badge-danger kt-badge-sm ml-1">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($form->hrd_score !== null)
                                            <span class="text-sm font-semibold text-foreground">{{ number_format($form->hrd_score, 2) }}</span>
                                        @else
                                            <span class="text-sm text-secondary-foreground italic">Not Graded</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $form->submitted_at ? $form->submitted_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('hrd.aper-forms.show', $form->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                        @if(in_array($form->status, ['ACCEPTED', 'FINALIZED']))
                                            <a href="{{ route('hrd.aper-forms.grade', $form->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-primary">
                                                {{ $form->hrd_score !== null ? 'Update Grade' : 'Grade' }}
                                            </a>
                                        @endif
                                        @if($form->canBeReassigned())
                                            <button type="button" 
                                                    class="kt-btn kt-btn-sm kt-btn-warning"
                                                    onclick="showReassignModal({{ $form->id }}, '{{ $form->status }}')">
                                                Reassign
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-pagination :paginator="$forms" item-name="forms" />
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No APER forms found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reassign Modal -->
<div id="reassign-modal" class="kt-modal hidden" data-kt-modal="true">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Reassign Officer</h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon" onclick="closeReassignModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="reassign-form" method="POST">
            @csrf
            <div class="kt-modal-body">
                <label class="kt-form-label">Select Officer</label>
                <input type="text" 
                       id="officer_search" 
                       class="kt-input" 
                       placeholder="Search officer by email..."
                       autocomplete="off">
                <input type="hidden" name="reporting_officer_id" id="reporting_officer_id">
                <input type="hidden" name="countersigning_officer_id" id="countersigning_officer_id">
                <div id="officer_results" class="mt-2 max-h-40 overflow-y-auto hidden"></div>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-secondary" onclick="closeReassignModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Reassign</button>
            </div>
        </form>
    </div>
</div>

<!-- KPI Report Modal -->
<div class="kt-modal" data-kt-modal="true" id="kpi-report-modal">
    <div class="kt-modal-content max-w-[600px]">
        <div class="kt-modal-header py-4 px-5">
            <h3 class="text-lg font-semibold text-foreground">KPI Report - High Performers</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="kpi-report-form" method="GET" action="{{ route('hrd.aper-forms.kpi.print') }}" target="_blank">
            <div class="kt-modal-body py-5 px-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="kt-form-label mb-2">Minimum Score</label>
                        <input type="number" 
                               name="min_score" 
                               id="min_score"
                               class="kt-input" 
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               max="100">
                    </div>
                    <div>
                        <label class="kt-form-label mb-2">Maximum Score</label>
                        <input type="number" 
                               name="max_score" 
                               id="max_score"
                               class="kt-input" 
                               placeholder="20.00"
                               step="0.01"
                               min="0"
                               max="100">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="kt-form-label mb-2">Year</label>
                        <input type="number" 
                               name="year" 
                               id="kpi_year"
                               class="kt-input" 
                               placeholder="Year"
                               min="2020"
                               max="2100"
                               value="{{ request('year') ?? date('Y') }}">
                    </div>
                    <div>
                        <label class="kt-form-label mb-2">Sort By</label>
                        <div class="relative">
                            <input type="hidden" name="sort_by" id="kpi_sort_by" value="hrd_score">
                            <button type="button" 
                                    id="kpi_sort_by_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="kpi_sort_by_select_text">Performance Rating (HRD Score)</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="kpi_sort_by_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <!-- Search Box -->
                                <div class="p-3 border-b border-input">
                                    <div class="relative">
                                        <input type="text" 
                                               id="kpi_sort_by_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search..."
                                               autocomplete="off">
                                    </div>
                                </div>
                                <!-- Options Container -->
                                <div id="kpi_sort_by_options" class="max-h-60 overflow-y-auto">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="kt-form-label mb-2">Sort Order</label>
                    <div class="relative">
                        <input type="hidden" name="sort_order" id="kpi_sort_order" value="desc">
                        <button type="button" 
                                id="kpi_sort_order_select_trigger" 
                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                            <span id="kpi_sort_order_select_text">Descending (Highest First)</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="kpi_sort_order_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <!-- Search Box -->
                            <div class="p-3 border-b border-input">
                                <div class="relative">
                                    <input type="text" 
                                           id="kpi_sort_order_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search..."
                                           autocomplete="off">
                                </div>
                            </div>
                            <!-- Options Container -->
                            <div id="kpi_sort_order_options" class="max-h-60 overflow-y-auto">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex flex-wrap items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" name="format" value="print" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-printer"></i> Print
                </button>
                <button type="submit" name="format" value="pdf" class="kt-btn kt-btn-sm kt-btn-success">
                    <i class="ki-filled ki-file-down"></i> Download PDF
                </button>
                <button type="submit" name="format" value="csv" class="kt-btn kt-btn-sm kt-btn-info">
                    <i class="ki-filled ki-file-down"></i> Download CSV
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentFormId = null;
let currentStatus = null;

function showReassignModal(formId, status) {
    currentFormId = formId;
    currentStatus = status;
    const form = document.getElementById('reassign-form');
    
    if (status === 'REPORTING_OFFICER') {
        form.action = `/hrd/aper-forms/${formId}/reassign-reporting-officer`;
        document.getElementById('countersigning_officer_id').disabled = true;
    } else {
        form.action = `/hrd/aper-forms/${formId}/reassign-countersigning-officer`;
        document.getElementById('reporting_officer_id').disabled = true;
    }
    
    document.getElementById('reassign-modal').classList.remove('hidden');
}

function closeReassignModal() {
    document.getElementById('reassign-modal').classList.add('hidden');
    document.getElementById('officer_search').value = '';
    document.getElementById('officer_results').classList.add('hidden');
}

// Officer search functionality (simplified - you may want to implement AJAX search)
document.getElementById('officer_search')?.addEventListener('input', function(e) {
    // Implement AJAX search for officers/users here
    // This is a placeholder
});

// Data for searchable selects
const statusOptions = [
    {id: 'all', name: 'All Statuses'},
    {id: 'DRAFT', name: 'Draft'},
    {id: 'SUBMITTED', name: 'Submitted'},
    {id: 'REPORTING_OFFICER', name: 'With Reporting Officer'},
    {id: 'COUNTERSIGNING_OFFICER', name: 'With Countersigning Officer'},
    {id: 'OFFICER_REVIEW', name: 'Officer Review'},
    {id: 'ACCEPTED', name: 'Accepted'},
    {id: 'REJECTED', name: 'Rejected'},
    {id: 'FINALIZED', name: 'Finalized'}
];

const sortByOptions = [
    {id: 'hrd_score', name: 'Performance Rating (HRD Score)'},
    {id: 'rank', name: 'Rank'},
    {id: 'name', name: 'Name'},
    {id: 'service_number', name: 'Service Number'},
    {id: 'command', name: 'Command'}
];

const sortOrderOptions = [
    {id: 'desc', name: 'Descending (Highest First)'},
    {id: 'asc', name: 'Ascending (Lowest First)'}
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

// Initialize searchable selects
document.addEventListener('DOMContentLoaded', function() {
    // Filter Status Select
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

    // KPI Sort By Select
    createSearchableSelect({
        triggerId: 'kpi_sort_by_select_trigger',
        hiddenInputId: 'kpi_sort_by',
        dropdownId: 'kpi_sort_by_dropdown',
        searchInputId: 'kpi_sort_by_search_input',
        optionsContainerId: 'kpi_sort_by_options',
        displayTextId: 'kpi_sort_by_select_text',
        options: sortByOptions,
        placeholder: 'Performance Rating (HRD Score)',
        searchPlaceholder: 'Search...'
    });

    // KPI Sort Order Select
    createSearchableSelect({
        triggerId: 'kpi_sort_order_select_trigger',
        hiddenInputId: 'kpi_sort_order',
        dropdownId: 'kpi_sort_order_dropdown',
        searchInputId: 'kpi_sort_order_search_input',
        optionsContainerId: 'kpi_sort_order_options',
        displayTextId: 'kpi_sort_order_select_text',
        options: sortOrderOptions,
        placeholder: 'Descending (Highest First)',
        searchPlaceholder: 'Search...'
    });
});
</script>
@endsection


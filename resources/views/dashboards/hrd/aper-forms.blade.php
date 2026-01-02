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
                    <select name="status" class="kt-input">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                        <option value="REPORTING_OFFICER" {{ request('status') == 'REPORTING_OFFICER' ? 'selected' : '' }}>With Reporting Officer</option>
                        <option value="COUNTERSIGNING_OFFICER" {{ request('status') == 'COUNTERSIGNING_OFFICER' ? 'selected' : '' }}>With Countersigning Officer</option>
                        <option value="OFFICER_REVIEW" {{ request('status') == 'OFFICER_REVIEW' ? 'selected' : '' }}>Officer Review</option>
                        <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>Accepted</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        <option value="FINALIZED" {{ request('status') == 'FINALIZED' ? 'selected' : '' }}>Finalized</option>
                    </select>
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
                @if($forms->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $forms->links() }}
                    </div>
                @endif
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
                        <select name="sort_by" id="kpi_sort_by" class="kt-input">
                            <option value="hrd_score">Performance Rating (HRD Score)</option>
                            <option value="rank">Rank</option>
                            <option value="name">Name</option>
                            <option value="service_number">Service Number</option>
                            <option value="command">Command</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="kt-form-label mb-2">Sort Order</label>
                    <select name="sort_order" id="kpi_sort_order" class="kt-input">
                        <option value="desc">Descending (Highest First)</option>
                        <option value="asc">Ascending (Lowest First)</option>
                    </select>
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
</script>
@endsection


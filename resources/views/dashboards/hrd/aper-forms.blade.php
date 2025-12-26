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
        <div class="kt-card-header">
            <h3 class="kt-card-title">APER Forms</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($forms->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 1000px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Year</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Reporting Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Submitted</th>
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
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $form->submitted_at ? $form->submitted_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('officer.aper-forms.show', $form->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
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


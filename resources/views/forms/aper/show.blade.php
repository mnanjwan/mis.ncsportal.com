@extends('layouts.app')

@section('title', 'View APER Form')
@section('page-title', 'View APER Form - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.aper-forms') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">View</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Status Card -->
    @php
        $statusConfig = match($form->status) {
            'DRAFT' => ['class' => 'secondary', 'label' => 'Draft'],
            'SUBMITTED' => ['class' => 'info', 'label' => 'Submitted'],
            'REPORTING_OFFICER' => ['class' => 'warning', 'label' => 'With Reporting Officer'],
            'COUNTERSIGNING_OFFICER' => ['class' => 'warning', 'label' => 'With Countersigning Officer'],
            'OFFICER_REVIEW' => ['class' => 'primary', 'label' => 'Pending Your Review'],
            'ACCEPTED' => ['class' => 'success', 'label' => 'Accepted'],
            'REJECTED' => ['class' => 'danger', 'label' => 'Rejected'],
            default => ['class' => 'secondary', 'label' => $form->status]
        };
    @endphp
    
    <div class="kt-card bg-{{ $statusConfig['class'] }}/10 border border-{{ $statusConfig['class'] }}/20">
        <div class="kt-card-content p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">APER Form {{ $form->year }}</h3>
                    <p class="text-sm text-secondary-foreground">
                        Status: <span class="kt-badge kt-badge-{{ $statusConfig['class'] }} kt-badge-sm">{{ $statusConfig['label'] }}</span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('officer.aper-forms.export', $form->id) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-file-down"></i> Export PDF
                    </a>
                    @if($form->status === 'OFFICER_REVIEW' && $form->officer->user_id === auth()->id())
                        <button type="button" onclick="showRejectModal()" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-cross"></i> Reject
                        </button>
                        <form action="{{ route('officer.aper-forms.accept', $form->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to accept this form?')" class="inline">
                            @csrf
                            <button type="submit" class="kt-btn kt-btn-success">
                                <i class="ki-filled ki-check"></i> Accept
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($form->is_rejected && $form->rejection_reason)
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-danger">Form Rejected</p>
                        <p class="text-xs text-secondary-foreground mt-1">{{ $form->rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Details -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Form Details</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="kt-form-label text-sm">Officer</label>
                    <p class="text-sm text-foreground">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                </div>
                <div>
                    <label class="kt-form-label text-sm">Service Number</label>
                    <p class="text-sm text-foreground">{{ $form->service_number ?? $form->officer->service_number }}</p>
                </div>
                <div>
                    <label class="kt-form-label text-sm">Reporting Officer</label>
                    <p class="text-sm text-foreground">{{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}</p>
                </div>
                <div>
                    <label class="kt-form-label text-sm">Countersigning Officer</label>
                    <p class="text-sm text-foreground">{{ $form->countersigningOfficer ? $form->countersigningOfficer->email : 'Not Assigned' }}</p>
                </div>
                @if($form->submitted_at)
                    <div>
                        <label class="kt-form-label text-sm">Submitted At</label>
                        <p class="text-sm text-foreground">{{ $form->submitted_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
                @if($form->accepted_at)
                    <div>
                        <label class="kt-form-label text-sm">Accepted At</label>
                        <p class="text-sm text-foreground">{{ $form->accepted_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Content Preview -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Form Content</h3>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground">
                This is a preview of the APER form. The full form content is available to authorized personnel.
            </p>
            @if($form->main_duties)
                <div class="mt-4">
                    <label class="kt-form-label text-sm">Main Duties</label>
                    <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->main_duties }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="kt-modal hidden" data-kt-modal="true">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Reject APER Form</h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon" onclick="closeRejectModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form action="{{ route('officer.aper-forms.reject', $form->id) }}" method="POST">
            @csrf
            <div class="kt-modal-body">
                <label class="kt-form-label">Reason for Rejection</label>
                <textarea name="rejection_reason" class="kt-input" rows="4" required></textarea>
            </div>
            <div class="kt-modal-footer">
                <button type="button" class="kt-btn kt-btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-danger">Reject Form</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}
</script>
@endsection


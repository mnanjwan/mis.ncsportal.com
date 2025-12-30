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
                    @if($form->status === 'DRAFT' && $form->officer->user_id === auth()->id())
                        <a href="{{ route('officer.aper-forms.edit', $form->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-notepad-edit"></i> Edit Form
                        </a>
                    @endif
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

    <!-- Form Content - Full Details -->
    @if($form->status !== 'DRAFT')
        <!-- Part 1: Personal Records -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">PART 1: PERSONAL RECORDS OF OFFICER</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="kt-form-label text-sm">Service Number</label>
                        <p class="text-sm text-foreground">{{ $form->service_number ?? $form->officer->service_number }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Title</label>
                        <p class="text-sm text-foreground">{{ $form->title ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Surname</label>
                        <p class="text-sm text-foreground">{{ $form->surname ?? $form->officer->surname }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Forenames</label>
                        <p class="text-sm text-foreground">{{ $form->forenames ?? $form->officer->initials }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Rank</label>
                        <p class="text-sm text-foreground">{{ $form->rank ?? $form->officer->substantive_rank }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Date of Birth</label>
                        <p class="text-sm text-foreground">{{ $form->date_of_birth ? $form->date_of_birth->format('d/m/Y') : ($form->officer->date_of_birth ? $form->officer->date_of_birth->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Date of First Appointment</label>
                        <p class="text-sm text-foreground">{{ $form->date_of_first_appointment ? $form->date_of_first_appointment->format('d/m/Y') : ($form->officer->date_of_first_appointment ? $form->officer->date_of_first_appointment->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Date of Present Appointment</label>
                        <p class="text-sm text-foreground">{{ $form->date_of_present_appointment ? $form->date_of_present_appointment->format('d/m/Y') : ($form->officer->date_of_present_appointment ? $form->officer->date_of_present_appointment->format('d/m/Y') : 'N/A') }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">State of Origin</label>
                        <p class="text-sm text-foreground">{{ $form->state_of_origin ?? $form->officer->state_of_origin ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Department/Area</label>
                        <p class="text-sm text-foreground">{{ $form->department_area ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Cadre</label>
                        <p class="text-sm text-foreground">{{ $form->cadre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Unit</label>
                        <p class="text-sm text-foreground">{{ $form->unit ?? $form->officer->unit ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Zone</label>
                        <p class="text-sm text-foreground">{{ $form->zone ?? $form->officer->geopolitical_zone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="kt-form-label text-sm">Qualifications</label>
                        <p class="text-sm text-foreground">
                            @if($form->qualifications)
                                @if(is_array($form->qualifications))
                                    {{ implode(', ', $form->qualifications) }}
                                @else
                                    {{ $form->qualifications }}
                                @endif
                            @else
                                {{ $form->officer->entry_qualification ?? 'N/A' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Part 2: Leave Records, Targets, Job Description -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">PART 2: Leave Records, Target Setting & Job Description</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 gap-4">
                    @if($form->main_duties)
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Main Duties</label>
                            <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->main_duties }}</p>
                        </div>
                    @endif
                    @if($form->joint_discussion)
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Joint Discussion</label>
                            <p class="text-sm text-foreground">{{ $form->joint_discussion }}</p>
                        </div>
                    @endif
                    @if($form->final_evaluation)
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Final Evaluation</label>
                            <p class="text-sm text-foreground">{{ $form->final_evaluation }}</p>
                        </div>
                    @endif
                    @if($form->difficulties_encountered)
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Difficulties Encountered</label>
                            <p class="text-sm text-foreground">{{ $form->difficulties_encountered }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Part 3: Performance Assessment -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">PART 3: Performance Assessment</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($form->job_understanding_grade)
                        <div>
                            <label class="kt-form-label text-sm">Job Understanding</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->job_understanding_grade }}</strong> - {{ $form->job_understanding_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->knowledge_application_grade)
                        <div>
                            <label class="kt-form-label text-sm">Knowledge Application</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->knowledge_application_grade }}</strong> - {{ $form->knowledge_application_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->accomplishment_grade)
                        <div>
                            <label class="kt-form-label text-sm">Accomplishment</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->accomplishment_grade }}</strong> - {{ $form->accomplishment_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->judgement_grade)
                        <div>
                            <label class="kt-form-label text-sm">Judgement</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->judgement_grade }}</strong> - {{ $form->judgement_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->staff_relations_grade)
                        <div>
                            <label class="kt-form-label text-sm">Staff Relations</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->staff_relations_grade }}</strong> - {{ $form->staff_relations_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->public_relations_grade)
                        <div>
                            <label class="kt-form-label text-sm">Public Relations</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->public_relations_grade }}</strong> - {{ $form->public_relations_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->quality_of_work_grade)
                        <div>
                            <label class="kt-form-label text-sm">Quality of Work</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->quality_of_work_grade }}</strong> - {{ $form->quality_of_work_comment ?? '' }}</p>
                        </div>
                    @endif
                    @if($form->punctuality_grade)
                        <div>
                            <label class="kt-form-label text-sm">Punctuality</label>
                            <p class="text-sm text-foreground"><strong>{{ $form->punctuality_grade }}</strong> - {{ $form->punctuality_comment ?? '' }}</p>
                        </div>
                    @endif
                </div>
                
                @if($form->overall_assessment)
                    <div class="mt-4">
                        <label class="kt-form-label text-sm font-semibold">Overall Assessment</label>
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->overall_assessment }}</p>
                    </div>
                @endif
                
                @if($form->general_remarks)
                    <div class="mt-4">
                        <label class="kt-form-label text-sm font-semibold">General Remarks</label>
                        <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->general_remarks }}</p>
                    </div>
                @endif
                
                @if($form->promotability)
                    <div class="mt-4">
                        <label class="kt-form-label text-sm font-semibold">Promotability</label>
                        <p class="text-sm text-foreground">{{ $form->promotability }}</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Draft Form Preview -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Form Content</h3>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground">
                    This form is still in draft status. Complete the form to see full details.
                </p>
            </div>
        </div>
    @endif

    <!-- Declarations Section -->
    @include('forms.aper.partials.declarations', ['form' => $form])
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


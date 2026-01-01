@extends('layouts.app')

@section('title', 'View APER Form')
@section('page-title', 'View APER Form - ' . $form->year)

@section('breadcrumbs')
    @if(auth()->user()->hasRole('HRD'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.aper-forms') }}">APER Forms</a>
        <span>/</span>
        <span class="text-primary">View</span>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.aper-forms') }}">APER Forms</a>
        <span>/</span>
        <span class="text-primary">View</span>
    @endif
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
                    @if(auth()->user()->hasRole('HRD') && in_array($form->status, ['ACCEPTED', 'FINALIZED']))
                        <a href="{{ route('hrd.aper-forms.grade', $form->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-star"></i> {{ $form->hrd_score !== null ? 'Update Grade' : 'Grade' }}
                        </a>
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
                @if($form->hrd_score !== null && (auth()->user()->hasRole('HRD') || auth()->user()->hasRole('Staff Officer')))
                    <div>
                        <label class="kt-form-label text-sm">HRD Score</label>
                        <p class="text-sm font-semibold text-success">{{ number_format($form->hrd_score, 2) }}</p>
                        @if($form->hrd_graded_at)
                            <p class="text-xs text-secondary-foreground mt-1">
                                Graded on {{ $form->hrd_graded_at->format('d/m/Y H:i') }}
                                @if($form->hrdGradedBy)
                                    by {{ $form->hrdGradedBy->email }}
                                @endif
                            </p>
                        @endif
                        @if($form->hrd_score_notes)
                            <p class="text-xs text-secondary-foreground mt-1 italic">{{ $form->hrd_score_notes }}</p>
                        @endif
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
                <div class="flex flex-col gap-6">
                    <!-- Period of Report -->
                    @if($form->period_from || $form->period_to)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-muted/50 rounded-lg">
                            <div>
                                <label class="kt-form-label text-sm font-semibold">Period of Report - From</label>
                                <p class="text-sm text-foreground">{{ $form->period_from ? \Carbon\Carbon::parse($form->period_from)->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="kt-form-label text-sm font-semibold">Period of Report - To</label>
                                <p class="text-sm text-foreground">{{ $form->period_to ? \Carbon\Carbon::parse($form->period_to)->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Leave Records -->
                    <div class="flex flex-col gap-4">
                        <h4 class="text-lg font-semibold">4. Leave Records</h4>
                        
                        <!-- Sick Leave Records -->
                        <div class="p-4 bg-muted/50 rounded-lg">
                            <label class="kt-form-label font-semibold mb-3">(A) Sick Leave Records</label>
                            @if($form->sick_leave_records && is_array($form->sick_leave_records) && count(array_filter($form->sick_leave_records, function($r) { return !empty($r['type']) || !empty($r['from']) || !empty($r['to']); })) > 0)
                                <div class="overflow-x-auto">
                                    <table class="kt-table w-full">
                                        <thead>
                                            <tr class="border-b border-border">
                                                <th class="text-left py-2 px-3 text-sm">Type</th>
                                                <th class="text-left py-2 px-3 text-sm">From</th>
                                                <th class="text-left py-2 px-3 text-sm">To</th>
                                                <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($form->sick_leave_records as $record)
                                                @if(!empty($record['type']) || !empty($record['from']) || !empty($record['to']))
                                                    <tr>
                                                        <td class="py-2 px-3 text-sm">{{ $record['type'] ?? 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['from']) ? \Carbon\Carbon::parse($record['from'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['to']) ? \Carbon\Carbon::parse($record['to'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ $record['days'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-secondary-foreground italic">N/A</p>
                            @endif
                        </div>
                        
                        <!-- Maternity Leave Records -->
                        <div class="p-4 bg-muted/50 rounded-lg">
                            <label class="kt-form-label font-semibold mb-3">(B) Maternity Leave Records</label>
                            @if($form->maternity_leave_records && is_array($form->maternity_leave_records) && count(array_filter($form->maternity_leave_records, function($r) { return !empty($r['from']) || !empty($r['to']); })) > 0)
                                <div class="overflow-x-auto">
                                    <table class="kt-table w-full">
                                        <thead>
                                            <tr class="border-b border-border">
                                                <th class="text-left py-2 px-3 text-sm">From</th>
                                                <th class="text-left py-2 px-3 text-sm">To</th>
                                                <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($form->maternity_leave_records as $record)
                                                @if(!empty($record['from']) || !empty($record['to']))
                                                    <tr>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['from']) ? \Carbon\Carbon::parse($record['from'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['to']) ? \Carbon\Carbon::parse($record['to'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ $record['days'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-secondary-foreground italic">N/A</p>
                            @endif
                        </div>
                        
                        <!-- Annual/Casual Leave Records -->
                        <div class="p-4 bg-muted/50 rounded-lg">
                            <label class="kt-form-label font-semibold mb-3">(C) Annual/Casual Leave Records</label>
                            @if($form->annual_casual_leave_records && is_array($form->annual_casual_leave_records) && count(array_filter($form->annual_casual_leave_records, function($r) { return !empty($r['from']) || !empty($r['to']); })) > 0)
                                <div class="overflow-x-auto">
                                    <table class="kt-table w-full">
                                        <thead>
                                            <tr class="border-b border-border">
                                                <th class="text-left py-2 px-3 text-sm">From</th>
                                                <th class="text-left py-2 px-3 text-sm">To</th>
                                                <th class="text-left py-2 px-3 text-sm">No. of Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($form->annual_casual_leave_records as $record)
                                                @if(!empty($record['from']) || !empty($record['to']))
                                                    <tr>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['from']) ? \Carbon\Carbon::parse($record['from'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ !empty($record['to']) ? \Carbon\Carbon::parse($record['to'])->format('d/m/Y') : 'N/A' }}</td>
                                                        <td class="py-2 px-3 text-sm">{{ $record['days'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-secondary-foreground italic">N/A</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Targets -->
                    @if($form->division_targets || $form->individual_targets || $form->project_cost)
                        <div class="flex flex-col gap-4">
                            <h4 class="text-lg font-semibold">Targets</h4>
                            
                            @if($form->division_targets && is_array($form->division_targets) && count($form->division_targets) > 0)
                                <div class="p-4 bg-muted/50 rounded-lg">
                                    <label class="kt-form-label font-semibold mb-3">Division Targets</label>
                                    <div class="flex flex-col gap-2">
                                        @foreach($form->division_targets as $target)
                                            <p class="text-sm text-foreground">{{ $target ?? 'N/A' }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if($form->individual_targets && is_array($form->individual_targets) && count($form->individual_targets) > 0)
                                <div class="p-4 bg-muted/50 rounded-lg">
                                    <label class="kt-form-label font-semibold mb-3">Individual Targets</label>
                                    <div class="flex flex-col gap-2">
                                        @foreach($form->individual_targets as $target)
                                            <p class="text-sm text-foreground">{{ $target ?? 'N/A' }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if($form->project_cost)
                                <div class="p-4 bg-muted/50 rounded-lg">
                                    <label class="kt-form-label font-semibold mb-3">Project Cost</label>
                                    <p class="text-sm text-foreground">{{ $form->project_cost }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Job Description and Other Details -->
                    <div class="flex flex-col gap-4">
                        <h4 class="text-lg font-semibold">Job Description</h4>
                        
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Main Duties</label>
                            <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->main_duties ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Joint Discussion</label>
                            <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->joint_discussion ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Final Evaluation</label>
                            <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->final_evaluation ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="kt-form-label text-sm font-semibold">Difficulties Encountered</label>
                            <p class="text-sm text-foreground whitespace-pre-wrap">{{ $form->difficulties_encountered ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Part 3: Performance Assessment - Comprehensive Reporting Officer Assessment -->
        @if($form->status !== 'DRAFT')
            @include('forms.aper.partials.reporting-officer-display', ['form' => $form])
        @endif
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
<div id="reject-modal" class="kt-modal hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeRejectModal()"></div>
    
    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="kt-modal-content max-w-[500px] relative bg-background rounded-lg shadow-xl w-full">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Reject APER Form</h3>
                <button type="button" class="kt-btn kt-btn-sm kt-btn-icon" onclick="closeRejectModal()">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form action="{{ route('officer.aper-forms.reject', $form->id) }}" method="POST">
                @csrf
                <div class="kt-modal-body">
                    <label class="kt-form-label">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="kt-input" rows="4" placeholder="Please provide a reason for rejecting this form..." required></textarea>
                    <p class="text-xs text-secondary-foreground mt-1">Maximum 1000 characters</p>
                    @error('rejection_reason')
                        <span class="text-sm text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="kt-modal-footer">
                    <button type="button" class="kt-btn kt-btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger">
                        <i class="ki-filled ki-cross"></i> Reject Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.body.style.overflow = ''; // Restore scrolling
    // Clear form
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
    }
}
</script>
@endsection


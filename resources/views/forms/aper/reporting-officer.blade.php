@extends('layouts.app')

@section('title', 'APER Form - Reporting Officer Assessment')
@section('page-title', 'APER Form Assessment - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary"
        href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.search-officers') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Assessment</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="kt-card bg-success/10 border border-success/20 mb-5">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-success text-xl"></i>
                        <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                        <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Officer Info Card -->
        <div class="kt-card bg-info/10 border border-info/20 shadow-sm">
            <div class="kt-card-content p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Officer Name</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->initials }} {{ $officer->surname }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Service Number</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->service_number }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Assessment Year</p>
                        <p class="text-base font-semibold text-foreground">{{ $form->year }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Rank</p>
                        <p class="text-base font-semibold text-foreground">{{ $officer->rank ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.update-reporting-officer', $form->id) : route('officer.aper-forms.update-reporting-officer', $form->id) }}" method="POST"
            id="reporting-officer-form">
            @csrf

            <!-- Section 9: Assessment of Performance -->
            @include('forms.aper.partials.reporting-officer.section9-assessment', ['form' => $form])

            <!-- Section 10: Aspects of Performance -->
            @include('forms.aper.partials.reporting-officer.section10-aspects', ['form' => $form])

            <!-- Section 11: Overall Assessment -->
            @include('forms.aper.partials.reporting-officer.section11-overall', ['form' => $form])

            <!-- Section 12-15: Training Needs, Remarks, Suggestions, Promotability -->
            @include('forms.aper.partials.reporting-officer.section12-15-final', ['form' => $form])

            <!-- Form Actions -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <a href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.search-officers') }}"
                            class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-arrow-left"></i> Back
                        </a>
                        <div class="flex gap-3">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Save Assessment
                            </button>
                            <button type="button" id="complete-forward-btn" class="kt-btn kt-btn-success">
                                <i class="ki-filled ki-send"></i> Complete & Forward
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Separate form for completion -->
        <form id="complete-forward-form"
            action="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.complete-reporting-officer', $form->id) : route('officer.aper-forms.complete-reporting-officer', $form->id) }}" method="POST"
            style="display: none;">
            @csrf
        </form>
    </div>

    <script>
        // Grade selection visual feedback
        document.addEventListener('DOMContentLoaded', function () {
            // Handle grade radio button changes
            document.querySelectorAll('.grade-group').forEach(group => {
                const radios = group.querySelectorAll('.grade-radio');
                const labels = group.querySelectorAll('.grade-option');
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Remove active styling from all labels in this group
                        labels.forEach(label => {
                            label.classList.remove('border-primary', 'bg-primary/10', 'text-primary', 'font-semibold');
                            label.classList.add('border-border/50', 'bg-background', 'text-foreground');
                        });
                        
                        // Add active styling to selected label
                        const selectedLabel = this.closest('.grade-option');
                        if (selectedLabel) {
                            selectedLabel.classList.remove('border-border/50', 'bg-background', 'text-foreground');
                            selectedLabel.classList.add('border-primary', 'bg-primary/10', 'text-primary', 'font-semibold');
                        }
                    });
                });
            });

            // Complete & Forward confirmation with validation
            const completeForwardBtn = document.getElementById('complete-forward-btn');
            const completeForwardForm = document.getElementById('complete-forward-form');

            if (completeForwardBtn) {
                completeForwardBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Check for required fields
                    const requiredFields = {
                        'job_understanding_grade': 'Job Understanding',
                        'knowledge_application_grade': 'Knowledge Application',
                        'accomplishment_grade': 'Accomplishment',
                        'judgement_grade': 'Judgement',
                        'work_speed_accuracy_grade': 'Work Speed & Accuracy',
                        'written_expression_grade': 'Written Expression',
                        'oral_expression_grade': 'Oral Expression',
                        'staff_relations_grade': 'Staff Relations',
                        'public_relations_grade': 'Public Relations',
                        'overall_assessment': 'Overall Assessment',
                        'promotability': 'Promotability'
                    };

                    const missingFields = [];
                    for (const [fieldName, fieldLabel] of Object.entries(requiredFields)) {
                        const field = document.querySelector(`input[name="${fieldName}"]:checked`);
                        if (!field) {
                            missingFields.push(fieldLabel);
                        }
                    }

                    if (missingFields.length > 0) {
                        const fieldsList = missingFields.join(', ');
                        const message = missingFields.length === 1
                            ? `Please complete the following required field: ${fieldsList}`
                            : `Please complete the following required fields: ${fieldsList}`;

                        Swal.fire({
                            title: 'Incomplete Assessment',
                            html: `<p>${message}</p><p class="text-sm mt-2 text-secondary-foreground">Please fill in all required assessment grades before forwarding the form.</p>`,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }

                    // Show confirmation dialog using SweetAlert2
                    Swal.fire({
                        title: 'Complete & Forward Assessment?',
                        text: 'Are you sure you want to complete and forward this assessment to Countersigning Officer? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Complete & Forward',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            completeForwardForm.submit();
                        }
                    });
                });
            }

            // Form submission validation
            const form = document.getElementById('reporting-officer-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // You can add validation here if needed
                    // For now, just show a loading indicator
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Saving...';
                    }
                });
            }
        });
    </script>
@endsection
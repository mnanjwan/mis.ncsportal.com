@extends('layouts.app')

@section('title', 'APER Form - Countersigning Officer')
@section('page-title', 'APER Form Countersigning - ' . $form->year)

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.countersigning.search') }}">APER Forms</a>
    <span>/</span>
    <span class="text-primary">Countersigning</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Officer Passport Picture -->
    @php
        $profilePictureUrl = $form->officer->getProfilePictureUrlFull();
    @endphp
    <div class="kt-card bg-background border border-border shadow-sm">
        <div class="kt-card-content p-5">
            <div class="flex items-center gap-6">
                @if($profilePictureUrl)
                    <div class="flex-shrink-0">
                        <img src="{{ $profilePictureUrl }}" 
                             alt="Officer Passport Photo" 
                             class="w-40 h-40 object-cover rounded-lg border-2 border-border shadow-md"
                             style="width: 160px; height: 160px;">
                    </div>
                @else
                    <div class="flex-shrink-0 w-40 h-40 bg-muted rounded-lg border-2 border-border flex items-center justify-center">
                        <div class="text-center">
                            <i class="ki-filled ki-user text-4xl text-secondary-foreground"></i>
                            <p class="text-xs text-secondary-foreground mt-2">No Photo</p>
                        </div>
                    </div>
                @endif
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-foreground mb-2">
                        {{ $form->officer->initials }} {{ $form->officer->surname }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Service Number</p>
                            <p class="text-base font-semibold text-foreground">{{ $form->officer->service_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Rank</p>
                            <p class="text-base font-semibold text-foreground">{{ $form->officer->rank ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Assessment Year</p>
                            <p class="text-base font-semibold text-foreground">{{ $form->year }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Form Status</p>
                            <p class="text-base font-semibold text-foreground">Countersigning Officer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <p class="text-base font-semibold text-foreground">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                </div>
                <div class="flex flex-col">
                    <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Service Number</p>
                    <p class="text-base font-semibold text-foreground">{{ $form->officer->service_number }}</p>
                </div>
                <div class="flex flex-col">
                    <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Assessment Year</p>
                    <p class="text-base font-semibold text-foreground">{{ $form->year }}</p>
                </div>
                <div class="flex flex-col">
                    <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Rank</p>
                    <p class="text-base font-semibold text-foreground">{{ $form->officer->rank ?? 'N/A' }}</p>
                </div>
            </div>
            @if($form->reportingOfficer)
                <div class="mt-4 pt-4 border-t border-border">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Reporting Officer</p>
                            <p class="text-sm font-semibold text-foreground">{{ $form->reportingOfficer->email }}</p>
                        </div>
                        @if($form->reporting_officer_completed_at)
                        <div>
                            <p class="text-xs text-secondary-foreground font-medium uppercase tracking-wide mb-1">Completed At</p>
                            <p class="text-sm font-semibold text-foreground">{{ $form->reporting_officer_completed_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Countersigning Form (Fixed at Top) -->
    <form action="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.update-countersigning-officer', $form->id) : route('officer.aper-forms.update-countersigning-officer', $form->id) }}" method="POST" id="countersigning-form">
        @csrf
        
        <div class="kt-card shadow-sm">
            <div class="kt-card-header bg-primary/10 border-b border-border">
                <h3 class="kt-card-title text-lg font-semibold">Countersigning Officer Section</h3>
                <p class="text-sm text-secondary-foreground italic mt-2">Review the assessment below and provide your countersigning declaration</p>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="kt-form-label font-semibold">Countersigning Officer Declaration</label>
                        <p class="text-sm text-secondary-foreground mb-2">
                            Please review the assessment above and provide your declaration as the Countersigning Officer.
                        </p>
                        <textarea name="countersigning_officer_declaration" 
                                  class="kt-input" 
                                  rows="6" 
                                  placeholder="Enter your declaration statement (minimum 20 characters)..."
                                  required>{{ old('countersigning_officer_declaration', $form->countersigning_officer_declaration) }}</textarea>
                        <p class="text-xs text-secondary-foreground mt-1">
                            Minimum 20 characters required. Current: <span id="char-count">0</span> characters
                        </p>
                        @error('countersigning_officer_declaration')
                            <span class="text-sm text-danger">{{ $message }}</span>
                        @enderror
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const textarea = document.querySelector('textarea[name="countersigning_officer_declaration"]');
                                const charCount = document.getElementById('char-count');
                                if (textarea && charCount) {
                                    function updateCharCount() {
                                        const length = textarea.value.trim().length;
                                        charCount.textContent = length;
                                        charCount.style.color = length >= 20 ? '#10b981' : '#dc3545';
                                    }
                                    textarea.addEventListener('input', updateCharCount);
                                    updateCharCount(); // Initial count
                                }
                            });
                        </script>
                    </div>

                    <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-information text-warning text-xl mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-warning">Important Notice</p>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    By completing this form, you are confirming that you have reviewed the assessment provided by the Reporting Officer 
                                    and agree with the evaluation. This will forward the form to the officer for final review.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <a href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.countersigning.search') }}" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-arrow-left"></i> Back
                    </a>
                    <div class="flex gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Save Declaration
                        </button>
                        <button type="button" id="complete-forward-btn" class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-send"></i> Complete & Forward to Officer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <!-- Divider with Label -->
    <div class="flex items-center gap-4 my-6">
        <div class="flex-1 border-t border-border"></div>
        <div class="px-4 py-2 bg-muted rounded-lg border border-border">
            <h3 class="text-lg font-semibold text-foreground">Reporting Officer's Assessment (Read-Only)</h3>
            <p class="text-xs text-secondary-foreground mt-1">Review the complete assessment below before completing your countersigning declaration above</p>
        </div>
        <div class="flex-1 border-t border-border"></div>
    </div>

    <!-- Display All Reporting Officer Assessments -->
    @include('forms.aper.partials.reporting-officer-display', ['form' => $form])
</div>

<script>
    // Complete & Forward confirmation
    document.addEventListener('DOMContentLoaded', function () {
        const completeForwardBtn = document.getElementById('complete-forward-btn');
        const mainForm = document.getElementById('countersigning-form');

        if (completeForwardBtn && mainForm) {
            completeForwardBtn.addEventListener('click', function (e) {
                e.preventDefault();

                // First, validate the declaration length client-side
                const declarationField = mainForm.querySelector('textarea[name="countersigning_officer_declaration"]');
                if (declarationField) {
                    const declarationValue = declarationField.value.trim();
                    if (!declarationValue || declarationValue.length < 20) {
                        Swal.fire({
                            title: 'Incomplete Declaration',
                            html: `<p>You must provide a valid countersigning declaration with at least 20 characters.</p><p class="text-sm mt-2 text-secondary-foreground">Current length: ${declarationValue.length} characters</p>`,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                }

                // Show confirmation dialog using SweetAlert2
                Swal.fire({
                    title: 'Complete & Forward Assessment?',
                    text: 'Are you sure you want to complete and forward this assessment to the Officer for review? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Complete & Forward',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Add hidden input to indicate complete and forward action
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'complete_and_forward';
                        hiddenInput.value = '1';
                        mainForm.appendChild(hiddenInput);
                        
                        // Submit the main form with all data
                        mainForm.submit();
                    }
                });
            });
        }
    });
</script>
@endsection


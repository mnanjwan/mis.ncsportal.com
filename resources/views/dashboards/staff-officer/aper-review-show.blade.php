@extends('layouts.app')

@section('title', 'Review APER Form')
@section('page-title', 'Review APER Form')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.aper-forms.review') }}">APER Forms Review</a>
    <span>/</span>
    <span class="text-primary">Review Form</span>
@endsection

@section('content')
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Officer Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officer Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Name</p>
                        <p class="text-sm font-medium">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Service Number</p>
                        <p class="text-sm font-medium">{{ $form->officer->service_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Year</p>
                        <p class="text-sm font-medium">{{ $form->year }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Information -->
        <div class="kt-card bg-warning/10 border border-warning/20">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-warning">Officer's Rejection Reason</h3>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-foreground">{{ $form->rejection_reason }}</p>
                <p class="text-xs text-secondary-foreground mt-2">
                    Rejected on: {{ $form->rejected_at ? $form->rejected_at->format('d/m/Y H:i') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Form Details -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">APER Form Details</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Reporting Officer</p>
                        <p class="text-sm">{{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Countersigning Officer</p>
                        <p class="text-sm">{{ $form->countersigningOfficer ? $form->countersigningOfficer->email : 'Not Assigned' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('staff-officer.aper-forms.review') }}" class="kt-btn kt-btn-ghost">
                        <i class="ki-filled ki-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('staff-officer.aper-forms.export', $form->id) }}" class="kt-btn kt-btn-primary" target="_blank">
                        <i class="ki-filled ki-file-down"></i> View Full Form
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4">
                    <!-- Reassign Option -->
                    <div class="p-4 border border-border rounded-lg">
                        <h4 class="text-sm font-semibold mb-2">Option 1: Reassign</h4>
                        <p class="text-xs text-secondary-foreground mb-4">
                            Reassign the form to a different Reporting Officer or Countersigning Officer to restart the process.
                        </p>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('staff-officer.aper-forms.reassign-reporting-officer', $form->id) }}" class="inline">
                                @csrf
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-warning" onclick="showReassignModal('reporting')">
                                    <i class="ki-filled ki-user-edit"></i> Reassign Reporting Officer
                                </button>
                            </form>
                            <form method="POST" action="{{ route('staff-officer.aper-forms.reassign-countersigning-officer', $form->id) }}" class="inline">
                                @csrf
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-warning" onclick="showReassignModal('countersigning')">
                                    <i class="ki-filled ki-user-edit"></i> Reassign Countersigning Officer
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Finalize Option -->
                    <div class="p-4 border border-danger/20 rounded-lg bg-danger/5">
                        <h4 class="text-sm font-semibold mb-2 text-danger">Option 2: Finalize (Reject)</h4>
                        <p class="text-xs text-secondary-foreground mb-4">
                            Finalize this form. HRD will be able to access it and marks will be awarded. This action cannot be undone.
                        </p>
                        <form method="POST" action="{{ route('staff-officer.aper-forms.staff-officer-reject', $form->id) }}" 
                              onsubmit="return confirm('Are you sure you want to finalize this form? HRD will be able to access it and marks will be awarded.');">
                            @csrf
                            <div class="mb-3">
                                <label class="kt-label">Rejection Reason (Optional)</label>
                                <textarea name="staff_officer_rejection_reason" 
                                          class="kt-input" 
                                          rows="3" 
                                          placeholder="Optional reason for finalizing this form..."></textarea>
                            </div>
                            <button type="submit" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-cross-circle"></i> Finalize Form
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reassign Modal (simplified - you may want to create a proper modal) -->
    <script>
        function showReassignModal(type) {
            alert('Reassign functionality - Please implement modal to select new ' + type + ' officer');
        }
    </script>
@endsection


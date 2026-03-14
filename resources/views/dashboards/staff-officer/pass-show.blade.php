@extends('layouts.app')

@section('title', 'Pass Application Details')
@section('page-title', 'Pass Application Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.leave-pass', ['type' => 'pass']) }}">Leave & Pass</a>
    <span>/</span>
    <span class="text-primary">Pass Application</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-foreground">Pass Application Details</h1>
                <p class="text-sm text-secondary-foreground mt-1">
                    Application ID: #{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div class="flex-shrink-0 flex gap-2">
                @if($application->status === 'PENDING' && is_null($application->minuted_at))
                    <form action="{{ route('staff-officer.pass-applications.minute', $application->id) }}" method="POST" class="inline minute-form">
                        @csrf
                        <button type="button" class="kt-btn kt-btn-primary handle-minute" data-text="Minute this application to 2iC Unit Head for Approval?">
                            <i class="ki-filled ki-file-edit"></i> Minute for Approval
                        </button>
                    </form>
                    <button type="button" class="kt-btn kt-btn-danger handle-reject" 
                            data-action="{{ route('staff-officer.pass-applications.reject', $application->id) }}"
                            data-name="{{ $application->officer->initials ?? '' }} {{ $application->officer->surname ?? '' }}">
                        <i class="ki-filled ki-trash"></i> Reject
                    </button>
                @elseif($application->minuted_at)
                    <span class="kt-badge kt-badge-info kt-badge-sm flex items-center gap-2">
                        <i class="ki-filled ki-check-circle"></i> Minuted to 2iC Unit Head
                    </span>
                @endif
                @if($application->status === 'APPROVED')
                    <a href="{{ route('staff-officer.pass-applications.print', $application->id) }}" 
                       class="kt-btn kt-btn-success" target="_blank">
                        <i class="ki-filled ki-printer"></i> Print
                    </a>
                @endif
                <a href="{{ route('staff-officer.leave-pass', ['type' => 'pass']) }}" class="kt-btn kt-btn-outline">
                    <i class="ki-filled ki-left"></i> Back
                </a>
            </div>
        </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Minute for Approval Confirmation
        document.querySelectorAll('.handle-minute').forEach(button => {
            button.addEventListener('click', function() {
                const text = this.dataset.text || 'Minute this application for Approval?';
                const form = this.closest('.minute-form');
                
                Swal.fire({
                    title: 'Confirm Minuting',
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Minute it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Reject Confirmation with Reason
        document.querySelectorAll('.handle-reject').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const name = this.dataset.name;
                
                Swal.fire({
                    title: 'Reject Application',
                    text: `Please provide a reason for rejecting the application from ${name}:`,
                    input: 'textarea',
                    inputPlaceholder: 'Type your reason here...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Reject Application',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You must provide a reason for rejection!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = action;
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        
                        const reasonInput = document.createElement('input');
                        reasonInput.type = 'hidden';
                        reasonInput.name = 'rejection_reason';
                        reasonInput.value = result.value;
                        
                        form.appendChild(csrfInput);
                        form.appendChild(reasonInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Application Status</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex items-center gap-4 flex-wrap">
                            @php
                                $statusClass = match ($application->status) {
                                    'APPROVED' => 'success',
                                    'PENDING' => 'warning',
                                    'REJECTED' => 'danger',
                                    'CANCELLED' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-lg">
                                {{ $application->status }}
                            </span>
                            @if($application->minuted_at)
                                <span class="text-sm text-secondary-foreground">
                                    Minuted: {{ $application->minuted_at->format('d M Y') }}
                                </span>
                            @endif
                            @if($application->approved_at)
                                <span class="text-sm text-secondary-foreground">
                                    Approved: {{ $application->approved_at->format('d M Y') }}
                                </span>
                            @endif
                            @if($application->rejected_at)
                                <span class="text-sm text-secondary-foreground">
                                    Rejected: {{ $application->rejected_at->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Application Details</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Number of Days</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->number_of_days }} days</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Start Date</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->start_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">End Date</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->end_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Submitted Date</label>
                                <p class="text-sm text-foreground mt-1">
                                    {{ $application->submitted_at ? $application->submitted_at->format('d M Y') : $application->created_at->format('d M Y') }}
                                </p>
                            </div>
                            @if($application->reason)
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-secondary-foreground">Reason</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->reason }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Officer Info -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Officer Information</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Service Number</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->officer->service_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Name</label>
                                <p class="text-sm text-foreground mt-1">
                                    {{ $application->officer->initials ?? '' }} {{ $application->officer->surname ?? '' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Rank</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->officer->substantive_rank ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Leave Application Details')
@section('page-title', 'Leave Application Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">2iC Unit Head</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.leave-pass', ['type' => 'leave']) }}">Leave & Pass</a>
    <span>/</span>
    <span class="text-primary">Leave Application</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-foreground">Leave Application Details</h1>
                <p class="text-sm text-secondary-foreground mt-1">
                    Application ID: #{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div class="flex-shrink-0 flex gap-2">
                @if($application->status === 'PENDING')
                    <form action="{{ route('dc-admin.leave-applications.approve', $application->id) }}" method="POST" class="inline approve-form">
                        @csrf
                        <button type="button" class="kt-btn kt-btn-success handle-approve">
                            <i class="ki-filled ki-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="kt-btn kt-btn-danger handle-reject" 
                            data-action="{{ route('dc-admin.leave-applications.reject', $application->id) }}"
                            data-name="{{ $application->officer->initials ?? '' }} {{ $application->officer->surname ?? '' }}">
                        <i class="ki-filled ki-trash"></i> Reject
                    </button>
                @endif
                <a href="{{ route('dc-admin.leave-pass', ['type' => 'leave']) }}" class="kt-btn kt-btn-outline">
                    <i class="ki-filled ki-left"></i> Back
                </a>
            </div>
        </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Approve Confirmation
        document.querySelectorAll('.handle-approve').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.approve-form');
                
                Swal.fire({
                    title: 'Confirm Approval',
                    text: 'Are you sure you want to approve this application?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#10b981'
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
                                <label class="text-sm font-medium text-secondary-foreground">Leave Type</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->leaveType->name ?? 'N/A' }}</p>
                            </div>
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
                            @if($application->expected_date_of_delivery)
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Expected Date of Delivery</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->expected_date_of_delivery->format('d M Y') }}</p>
                            </div>
                            @endif
                            @if($application->reason)
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-secondary-foreground">Reason</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->reason }}</p>
                            </div>
                            @endif
                            @if($application->medical_certificate_url)
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-secondary-foreground">Medical Certificate</label>
                                <p class="text-sm text-foreground mt-1">
                                    <a href="{{ asset('storage/' . $application->medical_certificate_url) }}" target="_blank" class="text-primary hover:underline">
                                        View Certificate
                                    </a>
                                </p>
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
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Command</label>
                                <p class="text-sm text-foreground mt-1">{{ $application->officer->presentStation->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

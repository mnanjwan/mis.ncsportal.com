@extends('layouts.app')

@section('title', 'Leave Application Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Home</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.application-history') }}">Application History</a>
    <span>/</span>
    <span class="text-primary">Leave Application</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-gray-900">Leave Application Details</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Application ID: #{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                    <i class="ki-filled ki-left"></i> Back
                </a>
            </div>
        </div>

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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


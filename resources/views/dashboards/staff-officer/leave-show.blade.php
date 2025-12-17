@extends('layouts.app')

@section('title', 'Leave Application Details')
@section('page-title', 'Leave Application Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.leave-pass') }}">Leave & Pass</a>
    <span>/</span>
    <span class="text-primary">Leave Application</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

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
                @if($application->status === 'PENDING' && is_null($application->minuted_at))
                    <form action="{{ route('staff-officer.leave-applications.minute', $application->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-primary" onclick="return confirm('Minute this application to DC Admin for approval?')">
                            <i class="ki-filled ki-file-edit"></i> Minute to DC Admin
                        </button>
                    </form>
                @elseif($application->minuted_at)
                    <span class="kt-badge kt-badge-info kt-badge-sm flex items-center gap-2">
                        <i class="ki-filled ki-check-circle"></i> Minuted to DC Admin
                    </span>
                @endif
                @if($application->status === 'APPROVED')
                    <a href="{{ route('staff-officer.leave-applications.print', $application->id) }}" 
                       class="kt-btn kt-btn-success" target="_blank">
                        <i class="ki-filled ki-printer"></i> Print
                    </a>
                @endif
                <a href="{{ route('staff-officer.leave-pass') }}" class="kt-btn kt-btn-outline">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Officer Dashboard')

@section('breadcrumbs')
    <span class="text-primary">Officer Dashboard</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Quick Actions Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Emolument Status</span>
                            <span class="text-2xl font-semibold text-mono" id="emolument-status">
                                {{ $emolumentStatus }}
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-wallet text-2xl text-primary"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground" id="timeline-info">
                            @if($activeTimeline)
                                Timeline: {{ $activeTimeline->start_date->format('d/m/Y') }} -
                                {{ $activeTimeline->end_date->format('d/m/Y') }}
                            @else
                                No Active Timeline
                            @endif
                        </span>
                        <a class="kt-btn kt-btn-primary justify-center" href="{{ route('emolument.raise') }}">Raise
                            Emolument</a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Leave Balance</span>
                            <span class="text-2xl font-semibold text-mono" id="leave-balance">
                                {{ $leaveBalance }} Days
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-calendar text-2xl text-success"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground">Annual Leave Available</span>
                        <a class="kt-btn kt-btn-success justify-center" href="{{ route('leave.apply') }}">Apply for
                            Leave</a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pass Eligibility</span>
                            <span class="text-2xl font-semibold text-mono" id="pass-status">
                                {{ $passStatus }}
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground">Max 5 days per application</span>
                        <a class="kt-btn kt-btn-info justify-center" href="{{ route('pass.apply') }}">Apply for Pass</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Quick Actions -->

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Recent Applications</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="recent-applications">
                        @forelse($recentApplications as $app)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-calendar text-primary"></i>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-mono">{{ $app->type }}</span>
                                        <span class="text-xs text-secondary-foreground">Submitted:
                                            {{ $app->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                @php
                                    $statusClass = match ($app->status) {
                                        'APPROVED' => 'success',
                                        'PENDING' => 'warning',
                                        'REJECTED' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">{{ $app->status }}</span>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-4">No recent applications</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-4 border-t border-border">
                        <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-outline w-full justify-center">
                            <i class="ki-filled ki-calendar-tick"></i> View Application History
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Service Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="service-info">
                        @if($officer)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Service Number</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->service_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Rank</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Present Station</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                            </div>
                        @else
                            <p class="text-secondary-foreground text-center py-4">Officer profile not found</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Recent Activities -->
    </div>
@endsection
@extends('layouts.app')

@section('title', 'HRD Dashboard')
@section('page-title', 'HRD Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Officers</span>
                        <span class="text-2xl font-semibold text-mono">{{ $totalOfficers ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Emoluments</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingEmoluments ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-wallet text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Active Timeline</span>
                        <span class="text-sm font-semibold text-mono">
                            @if($activeTimeline)
                                {{ $activeTimeline->start_date->format('d/m/Y') }} - {{ $activeTimeline->end_date->format('d/m/Y') }}
                            @else
                                No active timeline
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-calendar-2 text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Staff Orders</span>
                        <span class="text-2xl font-semibold text-mono">{{ $staffOrdersCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-file-up text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Statistics Cards -->
    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Officer Registrations</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @forelse($recentOfficers as $officer)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-profile-circle text-primary"></i>
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-medium text-mono">
                                        {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        SVC: {{ $officer->service_number ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-secondary-foreground">
                                {{ $officer->created_at ? $officer->created_at->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-secondary-foreground text-center py-4">No recent officers</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emolument Status</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <span class="text-sm font-medium text-foreground">Raised</span>
                        <span class="kt-badge kt-badge-info kt-badge-sm">{{ $emolumentStatus['RAISED'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <span class="text-sm font-medium text-foreground">Assessed</span>
                        <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $emolumentStatus['ASSESSED'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <span class="text-sm font-medium text-foreground">Validated</span>
                        <span class="kt-badge kt-badge-success kt-badge-sm">{{ $emolumentStatus['VALIDATED'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <span class="text-sm font-medium text-foreground">Processed</span>
                        <span class="kt-badge kt-badge-success kt-badge-sm">{{ $emolumentStatus['PROCESSED'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Recent Activities -->
</div>

@endsection

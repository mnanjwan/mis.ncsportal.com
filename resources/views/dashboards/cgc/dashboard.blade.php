@extends('layouts.app')

@section('title', 'CGC Dashboard')
@section('page-title', 'CGC Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">On Preretirement Leave</span>
                        <span class="text-2xl font-semibold text-mono">{{ $preretirementLeaveCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-calendar-tick text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Approaching (3 Months)</span>
                        <span class="text-2xl font-semibold text-mono">{{ $approachingCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-calendar text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">CGC Approved (In Office)</span>
                        <span class="text-2xl font-semibold text-mono">{{ $cgcApprovedCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-check-circle text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Auto Placed</span>
                        <span class="text-2xl font-semibold text-mono">{{ $autoPlacedCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar-2 text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Fleet Inbox</span>
                        <span class="text-2xl font-semibold text-mono">{{ $fleetInboxCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-file-up text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Fleet Approvals</span>
                        <span class="text-2xl font-semibold text-mono">{{ $fleetApprovalCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-check-circle text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Statistics Cards -->

    <!-- Quick Actions -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('cgc.preretirement-leave.index') }}" class="kt-btn kt-btn-primary w-full">
                    <i class="ki-filled ki-calendar-tick"></i>
                    View Preretirement Leave
                </a>
                <a href="{{ route('cgc.preretirement-leave.approaching') }}" class="kt-btn kt-btn-warning w-full">
                    <i class="ki-filled ki-calendar"></i>
                    Officers Approaching
                </a>
                <a href="{{ route('cgc.preretirement-leave.index', ['status' => 'CGC_APPROVED_IN_OFFICE']) }}" class="kt-btn kt-btn-success w-full">
                    <i class="ki-filled ki-check-circle"></i>
                    Approved (In Office)
                </a>
                <a href="{{ route('fleet.requests.index') }}" class="kt-btn kt-btn-primary w-full">
                    <i class="ki-filled ki-file-up"></i>
                    Fleet Requests
                </a>
            </div>
        </div>
    </div>

    <!-- Fleet Requests Inbox -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Fleet Requests Inbox</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('fleet.requests.index') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-col gap-4">
                @forelse($fleetInboxItems as $fleetRequest)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                <i class="ki-filled ki-file-up text-primary"></i>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-mono">
                                    Request #{{ $fleetRequest->id }} • {{ $fleetRequest->originCommand->name ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    Status: {{ $fleetRequest->status }} | Step: {{ $fleetRequest->current_step_order ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('fleet.requests.show', $fleetRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            Review
                        </a>
                    </div>
                @empty
                    <div class="text-center py-8 text-secondary-foreground">
                        <i class="ki-filled ki-inbox text-4xl mb-2"></i>
                        <p>No fleet requests waiting.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Preretirement Leave Placements -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Recent Preretirement Leave Placements</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('cgc.preretirement-leave.index') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-col gap-4">
                @forelse($recentItems as $item)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                <i class="ki-filled ki-profile-circle text-primary"></i>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-mono">
                                    {{ $item->officer->full_name ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    SVC: {{ $item->officer->service_number ?? 'N/A' }} | 
                                    Rank: {{ $item->rank ?? 'N/A' }} |
                                    Status: 
                                    @if($item->preretirement_leave_status === 'AUTO_PLACED')
                                        <span class="text-info">Auto Placed</span>
                                    @elseif($item->preretirement_leave_status === 'CGC_APPROVED_IN_OFFICE')
                                        <span class="text-success">Approved (In Office)</span>
                                    @else
                                        {{ $item->preretirement_leave_status }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-xs text-secondary-foreground">
                                Preretirement: {{ $item->date_of_pre_retirement_leave ? $item->date_of_pre_retirement_leave->format('d/m/Y') : 'N/A' }}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Retirement: {{ $item->retirement_date ? $item->retirement_date->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-secondary-foreground">
                        <i class="ki-filled ki-information text-4xl mb-2"></i>
                        <p>No preretirement leave placements yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection


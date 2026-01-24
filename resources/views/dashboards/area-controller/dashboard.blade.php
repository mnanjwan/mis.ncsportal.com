@extends('layouts.app')

@section('title', 'Area Controller Dashboard')
@section('page-title', 'Area Controller Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Area Controller Operational Summary -->
    <div class="kt-card bg-primary/10 border border-primary/20">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-wrap items-center gap-4 lg:gap-6 text-sm font-semibold">
                <div class="flex items-center gap-2">
                    <span class="text-primary uppercase tracking-wide">{{ $officerRank ?? 'N/A' }}</span>
                    <span class="text-foreground">|</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-secondary-foreground">Area:</span>
                    <span class="text-foreground">{{ $areaName ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-foreground">|</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-secondary-foreground">Officers:</span>
                    <span class="text-foreground">{{ number_format($officersCount ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-foreground">|</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-secondary-foreground">Units:</span>
                    <span class="text-foreground">{{ $unitsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Emoluments</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingEmoluments }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-wallet text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Manning Requests</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingManningRequests }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-people text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Duty Rosters</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingRosters }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Fleet Requests (Inbox)</span>
                        <span class="text-2xl font-semibold text-mono">{{ $fleetInboxCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-file-up text-2xl text-primary"></i>
                    </div>
                </div>
                <a href="{{ route('fleet.requests.index') }}" class="text-xs text-primary font-semibold">
                    View Fleet Requests
                </a>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Receipts</span>
                        <span class="text-2xl font-semibold text-mono">{{ $fleetPendingReceiptsCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-check-square text-2xl text-success"></i>
                    </div>
                </div>
                <span class="text-xs text-secondary-foreground">
                    Awaiting acknowledgement
                </span>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <!-- Fleet Requests Inbox -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Fleet Requests Inbox</h3>
            </div>
            <div class="kt-card-content">
                @if(isset($fleetInboxItems) && $fleetInboxItems->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($fleetInboxItems as $fleetRequest)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        Request #{{ $fleetRequest->id }} • {{ $fleetRequest->originCommand->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Status: {{ $fleetRequest->status }} | Step: {{ $fleetRequest->current_step_order ?? 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('fleet.requests.show', $fleetRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('fleet.requests.index') }}" class="kt-btn kt-btn-outline w-full">
                            View All Fleet Requests
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-inbox text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No fleet requests waiting.</p>
                    </div>
                @endif
            </div>
        </div>
        <!-- Recent Manning Requests -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Manning Requests</h3>
            </div>
            <div class="kt-card-content">
                @if(isset($recentManningRequests) && $recentManningRequests->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentManningRequests as $request)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $request->command->name ?? 'N/A' }} - {{ $request->command->zone->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Requested by: {{ $request->requestedBy->email ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Submitted: {{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('area-controller.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('area-controller.manning-level') }}" class="kt-btn kt-btn-outline w-full">
                            View All Manning Requests
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending manning requests</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Duty Rosters -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Duty Rosters</h3>
            </div>
            <div class="kt-card-content">
                @if($recentRosters->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentRosters as $roster)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $roster->command->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Period: {{ $roster->roster_period_start->format('M d') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Prepared by: {{ $roster->preparedBy->email ?? 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('area-controller.roster.show', $roster->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Review
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('area-controller.roster') }}" class="kt-btn kt-btn-outline w-full">
                            View All Duty Rosters
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No pending duty rosters</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Units -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Active Units</h3>
        </div>
        <div class="kt-card-content">
            @if(isset($units) && $units->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($units as $index => $unit)
                        @php
                            $unitId = 'unit-' . str_replace([' ', '/', '\\'], '-', strtolower($unit['name'])) . '-' . $index;
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-foreground">
                                    {{ $unit['name'] }}
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    Officers: {{ $unit['officers_count'] }}
                                </span>
                            </div>
                            <button type="button" 
                                    class="kt-btn kt-btn-sm kt-btn-primary" 
                                    data-kt-modal-toggle="#{{ $unitId }}">
                                <i class="ki-filled ki-eye"></i> View
                            </button>
                        </div>

                        <!-- Unit Modal -->
                        <div class="kt-modal hidden" data-kt-modal="true" id="{{ $unitId }}">
                            <div class="kt-modal-content max-w-[800px]">
                                <div class="kt-modal-header py-4 px-5">
                                    <h3 class="kt-modal-title">Unit: {{ $unit['name'] }}</h3>
                                    <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                                        <i class="ki-filled ki-cross"></i>
                                    </button>
                                </div>
                                <div class="kt-modal-body py-5 px-5">
                                    <div class="flex flex-col gap-6">
                                        <!-- OIC Section -->
                                        <div>
                                            <h4 class="text-sm font-semibold text-foreground mb-3 uppercase tracking-wide">Officer in Charge (OIC)</h4>
                                            @if($unit['oic'])
                                                <div class="p-3 rounded-lg bg-primary/10 border border-primary/20">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-semibold text-foreground">
                                                            {{ $unit['oic']->surname }}, {{ $unit['oic']->initials }}
                                                        </span>
                                                        <span class="text-xs text-secondary-foreground">
                                                            Service Number: {{ $unit['oic']->service_number ?? 'N/A' }}
                                                        </span>
                                                        <span class="text-xs text-secondary-foreground">
                                                            Rank: {{ $unit['oic']->substantive_rank ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-sm text-secondary-foreground italic">No OIC assigned</p>
                                            @endif
                                        </div>

                                        <!-- 2IC Section -->
                                        <div>
                                            <h4 class="text-sm font-semibold text-foreground mb-3 uppercase tracking-wide">Second in Command (2IC)</h4>
                                            @if($unit['second_ic'])
                                                <div class="p-3 rounded-lg bg-info/10 border border-info/20">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-semibold text-foreground">
                                                            {{ $unit['second_ic']->surname }}, {{ $unit['second_ic']->initials }}
                                                        </span>
                                                        <span class="text-xs text-secondary-foreground">
                                                            Service Number: {{ $unit['second_ic']->service_number ?? 'N/A' }}
                                                        </span>
                                                        <span class="text-xs text-secondary-foreground">
                                                            Rank: {{ $unit['second_ic']->substantive_rank ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-sm text-secondary-foreground italic">No 2IC assigned</p>
                                            @endif
                                        </div>

                                        <!-- Members Section -->
                                        <div>
                                            <h4 class="text-sm font-semibold text-foreground mb-3 uppercase tracking-wide">
                                                Members ({{ $unit['members']->count() }})
                                            </h4>
                                            @if($unit['members']->count() > 0)
                                                <div class="flex flex-col gap-2 max-h-[400px] overflow-y-auto">
                                                    @foreach($unit['members'] as $member)
                                                        <div class="p-3 rounded-lg bg-muted/50 border border-input">
                                                            <div class="flex flex-col gap-1">
                                                                <span class="text-sm font-semibold text-foreground">
                                                                    {{ $member->surname }}, {{ $member->initials }}
                                                                </span>
                                                                <span class="text-xs text-secondary-foreground">
                                                                    Service Number: {{ $member->service_number ?? 'N/A' }}
                                                                </span>
                                                                <span class="text-xs text-secondary-foreground">
                                                                    Rank: {{ $member->substantive_rank ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-secondary-foreground italic">No members assigned</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Close</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No active units found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection



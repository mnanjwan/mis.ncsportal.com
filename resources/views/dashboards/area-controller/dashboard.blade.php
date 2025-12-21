@extends('layouts.app')

@section('title', 'Area Controller Dashboard')
@section('page-title', 'Area Controller Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
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
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
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

    <!-- Quick Actions -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <a href="{{ route('area-controller.emoluments') }}" class="kt-btn kt-btn-primary justify-start">
                    <i class="ki-filled ki-wallet"></i> Emoluments
                </a>
                <a href="{{ route('area-controller.leave-pass') }}" class="kt-btn kt-btn-info justify-start">
                    <i class="ki-filled ki-calendar"></i> Leave & Pass
                </a>
                <a href="{{ route('area-controller.manning-level') }}" class="kt-btn kt-btn-success justify-start">
                    <i class="ki-filled ki-people"></i> Manning Requests
                </a>
                <a href="{{ route('area-controller.roster') }}" class="kt-btn kt-btn-warning justify-start">
                    <i class="ki-filled ki-calendar-tick"></i> Duty Rosters
                </a>
            </div>
        </div>
    </div>
</div>
@endsection



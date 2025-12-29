@extends('layouts.app')

@section('title', 'Investigation Unit Dashboard')
@section('page-title', 'Investigation Unit Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <a href="{{ route('investigation.index') }}?status=INVITED" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Invited</span>
                        <span class="text-2xl font-semibold text-mono">{{ $invitedCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-send text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </a>
        
        <a href="{{ route('investigation.index') }}?status=ONGOING_INVESTIGATION" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Ongoing Investigation</span>
                        <span class="text-2xl font-semibold text-mono">{{ $ongoingCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-file-search text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('investigation.index') }}?status=INTERDICTED" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Interdicted</span>
                        <span class="text-2xl font-semibold text-mono">{{ $interdictedCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                        <i class="ki-filled ki-shield-cross text-2xl text-danger"></i>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('investigation.index') }}?status=SUSPENDED" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Suspended</span>
                        <span class="text-2xl font-semibold text-mono">{{ $suspendedCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                        <i class="ki-filled ki-pause-circle text-2xl text-danger"></i>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('investigation.index') }}?status=RESOLVED" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Resolved</span>
                        <span class="text-2xl font-semibold text-mono">{{ $resolvedCount }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-check-circle text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ route('investigation.index') }}" class="kt-card hover:shadow-lg transition-shadow">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Investigations</span>
                        <span class="text-2xl font-semibold text-mono">{{ $totalInvestigations }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-file-search text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-magnifier"></i> Search Officers
                </a>
                <a href="{{ route('investigation.index') }}" class="kt-btn kt-btn-secondary">
                    <i class="ki-filled ki-file-search"></i> View All Investigations
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Investigations -->
    @if($recentInvestigations->count() > 0)
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Recent Investigations</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('investigation.index') }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                    View All
                </a>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <div class="table-scroll-wrapper overflow-x-auto">
                <table class="kt-table" style="min-width: 800px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm">Officer</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentInvestigations as $investigation)
                            <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                <td class="py-3 px-4">
                                    <div>
                                        <span class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</span>
                                        <div class="text-xs text-muted-foreground">{{ $investigation->officer->service_number }}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($investigation->status === 'INVITED')
                                        <span class="kt-badge kt-badge-info">Invited</span>
                                    @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                                        <span class="kt-badge kt-badge-warning">Ongoing</span>
                                    @elseif($investigation->status === 'INTERDICTED')
                                        <span class="kt-badge kt-badge-danger">Interdicted</span>
                                    @elseif($investigation->status === 'SUSPENDED')
                                        <span class="kt-badge kt-badge-danger">Suspended</span>
                                    @else
                                        <span class="kt-badge kt-badge-success">Resolved</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    {{ $investigation->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('investigation.show', $investigation->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection



@extends('layouts.app')

@section('title', 'Staff Officer Dashboard')
@section('page-title', 'Staff Officer Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if(!$command)
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command assignment.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
            <a href="{{ route('staff-officer.leave-pass') }}?status=PENDING" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Leave</span>
                            <span class="text-2xl font-semibold text-mono">{{ $pendingLeaveCount }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-calendar text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('staff-officer.leave-pass') }}?status=PENDING&type=pass" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Pass</span>
                            <span class="text-2xl font-semibold text-mono">{{ $pendingPassCount }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                        </div>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('staff-officer.manning-level') }}" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Manning Requests</span>
                            <span class="text-2xl font-semibold text-mono">{{ $manningLevelCount }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-people text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('staff-officer.roster') }}" class="kt-card hover:shadow-lg transition-shadow">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Duty Roster</span>
                            <span class="text-sm font-semibold text-mono {{ $dutyRosterActive ? 'text-success' : 'text-secondary-foreground' }}">
                                {{ $dutyRosterActive ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full {{ $dutyRosterActive ? 'bg-success/10' : 'bg-muted/10' }}">
                            <i class="ki-filled ki-calendar-tick text-2xl {{ $dutyRosterActive ? 'text-success' : 'text-muted-foreground' }}"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif
    
    <!-- Matched Officers from Approved Requests Section -->
    @if($command && isset($approvedManningRequestsWithMatches) && $approvedManningRequestsWithMatches->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Matched Officers Ready for Review</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-success kt-badge-sm">{{ $approvedManningRequestsWithMatches->count() }} Request(s)</span>
                </div>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground mb-4">
                    HRD has matched officers for your approved manning requests. Review the matches and proceed with posting.
                </p>
                <div class="overflow-x-auto">
                    @foreach($approvedManningRequestsWithMatches as $request)
                        <div class="mb-6 pb-6 border-b border-border last:border-0 last:mb-0 last:pb-0">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground">Manning Request #{{ $request->id }}</h4>
                                    <p class="text-xs text-secondary-foreground mt-1">
                                        Approved: {{ $request->approved_at ? $request->approved_at->format('M d, Y') : 'N/A' }}
                                        <span class="kt-badge kt-badge-success kt-badge-xs ml-2">Officers Matched</span>
                                    </p>
                                </div>
                                <a href="{{ route('staff-officer.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View Details
                                </a>
                            </div>
                            
                            <table class="kt-table w-full">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-2 px-4 font-semibold text-xs text-secondary-foreground">Rank Required</th>
                                        <th class="text-left py-2 px-4 font-semibold text-xs text-secondary-foreground">Matched Officer</th>
                                        <th class="text-left py-2 px-4 font-semibold text-xs text-secondary-foreground">Service Number</th>
                                        <th class="text-left py-2 px-4 font-semibold text-xs text-secondary-foreground">Current Station</th>
                                        <th class="text-left py-2 px-4 font-semibold text-xs text-secondary-foreground">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $item)
                                        @if($item->matchedOfficer)
                                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                                <td class="py-2 px-4 text-xs text-secondary-foreground">
                                                    {{ $item->rank }}
                                                </td>
                                                <td class="py-2 px-4">
                                                    <span class="text-xs font-medium text-foreground">
                                                        {{ $item->matchedOfficer->initials }} {{ $item->matchedOfficer->surname }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-4">
                                                    <span class="text-xs font-mono text-secondary-foreground">{{ $item->matchedOfficer->service_number }}</span>
                                                </td>
                                                <td class="py-2 px-4 text-xs text-secondary-foreground">
                                                    {{ $item->matchedOfficer->presentStation->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-2 px-4">
                                                    <a href="{{ route('staff-officer.manning-level.show', $request->id) }}" class="kt-btn kt-btn-xs kt-btn-primary">
                                                        Review
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Newly Posted Officers Section -->
    @if($command && $newlyPostedOfficers->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Newly Posted Officers</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $newlyPostedOfficers->count() }} New</span>
                </div>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground mb-4">
                    The following officers have been posted to <strong>{{ $command->name }}</strong> and need to be documented.
                </p>
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Posted Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($newlyPostedOfficers as $officer)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $officer->initials }} {{ $officer->surname }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-mono text-secondary-foreground">{{ $officer->service_number }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->substantive_rank }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->date_posted_to_station ? $officer->date_posted_to_station->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="{{ route('staff-officer.officers.document', $officer->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary" onclick="return confirm('Document this officer? This confirms their arrival at the command.')">
                                                <i class="ki-filled ki-file-check"></i> Document
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($command)
        <!-- Recent Activities -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Recent Leave Applications</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('staff-officer.leave-pass') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($recentLeaveApplications->count() > 0)
                        <div class="flex flex-col gap-4">
                            @foreach($recentLeaveApplications as $app)
                                <a href="{{ route('staff-officer.leave-applications.show', $app->id) }}" 
                                   class="flex items-center justify-between p-3 rounded-lg bg-muted/50 hover:bg-muted transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                                            <i class="ki-filled ki-calendar text-warning"></i>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $app->leaveType->name ?? 'N/A' }} - {{ $app->number_of_days ?? 0 }} days
                                            </span>
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                                        {{ $app->status }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-secondary-foreground text-center py-4">No recent leave applications</p>
                    @endif
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Recent Pass Applications</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('staff-officer.leave-pass') }}?type=pass" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($recentPassApplications->count() > 0)
                        <div class="flex flex-col gap-4">
                            @foreach($recentPassApplications as $app)
                                <a href="{{ route('staff-officer.pass-applications.show', $app->id) }}" 
                                   class="flex items-center justify-between p-3 rounded-lg bg-muted/50 hover:bg-muted transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                                            <i class="ki-filled ki-calendar-tick text-info"></i>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $app->number_of_days ?? 0 }} days
                                            </span>
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                                        {{ $app->status }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-secondary-foreground text-center py-4">No recent pass applications</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection


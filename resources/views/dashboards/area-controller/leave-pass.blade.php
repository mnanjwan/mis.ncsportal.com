@extends('layouts.app')

@section('title', 'Leave & Pass Management')
@section('page-title', 'Leave & Pass Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    <span>/</span>
    <span class="text-primary">Leave & Pass</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Tabs -->
    <div class="kt-card">
        <div class="kt-card-header">
            <ul class="flex gap-2 border-b border-input">
                <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'leave' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                    <a href="{{ route('area-controller.leave-pass', ['type' => 'leave', 'status' => request('status')]) }}">
                        Leave Applications
                    </a>
                </li>
                <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'pass' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                    <a href="{{ route('area-controller.leave-pass', ['type' => 'pass', 'status' => request('status')]) }}">
                        Pass Applications
                    </a>
                </li>
            </ul>
        </div>
        <div class="kt-card-content">
            @if($type === 'leave')
            <!-- Leave Applications Tab -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mono">Leave Applications</h3>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('area-controller.leave-pass') }}" class="inline">
                        <input type="hidden" name="type" value="leave">
                        <select name="status" class="kt-input" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                @forelse($leaveApplications as $app)
                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-calendar text-warning text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">
                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->leaveType->name ?? 'N/A' }}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }} ({{ $app->number_of_days }} days)
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Applied: {{ $app->submitted_at ? $app->submitted_at->format('M d, Y') : 'N/A' }}
                                @if($app->minuted_at)
                                    | Minuted: {{ $app->minuted_at->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                            {{ $app->status }}
                        </span>
                        @if($app->minuted_at)
                            <span class="kt-badge kt-badge-info kt-badge-sm">
                                Minuted
                            </span>
                        @endif
                        <a href="{{ route('area-controller.leave-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
                @empty
                    <p class="text-secondary-foreground text-center py-8">No leave applications found</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $leaveApplications->links() }}
            </div>
            @else
            <!-- Pass Applications Tab -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mono">Pass Applications</h3>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('area-controller.leave-pass') }}" class="inline">
                        <input type="hidden" name="type" value="pass">
                        <select name="status" class="kt-input" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="flex flex-col gap-4">
                @forelse($passApplications as $app)
                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-calendar-tick text-info text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">
                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->number_of_days }} days
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Applied: {{ $app->submitted_at ? $app->submitted_at->format('M d, Y') : 'N/A' }}
                                @if($app->minuted_at)
                                    | Minuted: {{ $app->minuted_at->format('M d, Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                            {{ $app->status }}
                        </span>
                        @if($app->minuted_at)
                            <span class="kt-badge kt-badge-info kt-badge-sm">
                                Minuted
                            </span>
                        @endif
                        <a href="{{ route('area-controller.pass-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
                @empty
                    <p class="text-secondary-foreground text-center py-8">No pass applications found</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $passApplications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

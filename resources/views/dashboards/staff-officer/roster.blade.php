@extends('layouts.app')

@section('title', 'Duty Roster')
@section('page-title', 'Duty Roster')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Duty Roster</span>
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
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(!$command)
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command
                        assignment.</p>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Actions -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-foreground">Duty Roster</h2>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('staff-officer.roster') }}" class="inline">
                        <input type="month" name="month" value="{{ $month }}" class="kt-input" onchange="this.form.submit()" />
                    </form>
                    <a href="{{ route('staff-officer.roster.create') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Roster
                    </a>
                </div>
            </div>

            <!-- Roster List -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Duty Rosters for {{ date('F Y', strtotime($month . '-01')) }}</h3>
                </div>
                <div class="kt-card-content">
                    @forelse($rosters as $roster)
                        <div
                            class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted mb-4 last:mb-0">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                    <i class="ki-filled ki-calendar-tick text-success text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        @if($roster->unit)
                                            {{ $roster->unit }} -
                                        @endif
                                        Roster Period: {{ $roster->roster_period_start->format('M d') }} -
                                        {{ $roster->roster_period_end->format('M d, Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $roster->assignments->count() }} assignment(s) |
                                        Status: {{ $roster->status }}
                                        @if($roster->oicOfficer)
                                            | OIC: {{ $roster->oicOfficer->initials }} {{ $roster->oicOfficer->surname }}
                                            ({{ $roster->oicOfficer->service_number }})
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="kt-badge kt-badge-{{ strtolower($roster->status) === 'active' ? 'success' : 'warning' }} kt-badge-sm">
                                    {{ $roster->status }}
                                </span>
                                <a href="{{ route('staff-officer.roster.show', $roster->id) }}"
                                    class="kt-btn kt-btn-sm kt-btn-ghost">
                                    <i class="ki-filled ki-eye"></i> View
                                </a>
                                <a href="{{ route('staff-officer.roster.edit', $roster->id) }}"
                                    class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No duty roster found for this month</p>
                            <a href="{{ route('staff-officer.roster.create') }}" class="kt-btn kt-btn-primary">
                                Create Roster
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
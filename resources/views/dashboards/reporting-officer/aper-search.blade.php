@extends('layouts.app')

@section('title', 'Search Officers for APER Forms')
@section('page-title', 'Search Officers for APER Forms')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <span class="text-primary">APER Forms - Search Officers</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if(isset($isOICOr2IC) && !$isOICOr2IC && !auth()->user()->hasRole('HRD') && !auth()->user()->hasRole('Staff Officer'))
        <div class="kt-card bg-warning/10 border border-warning/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-warning text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-warning">You must be an Officer in Charge (OIC) or Second In Command (2IC) in an approved duty roster to create APER forms.</p>
                        <p class="text-xs text-secondary-foreground mt-1">Only officers with OIC or 2IC roles in approved duty rosters can create APER forms for officers in their command.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($rosterRole))
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-info text-xl"></i>
                    <p class="text-sm font-medium text-info">Your Role: <strong>{{ $rosterRole }}</strong> - You can create APER forms for officers in your command.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Search Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Search Officers</h3>
            <div class="kt-card-toolbar">
                <p class="text-xs text-secondary-foreground">
                    <i class="ki-filled ki-information"></i> 
                    Only officers in your command are shown
                </p>
            </div>
        </div>
        <div class="kt-card-content">
            <form method="GET" action="{{ auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD') ? route('staff-officer.aper-forms.reporting-officer.search') : route('officer.aper-forms.search-officers') }}" class="flex gap-3">
                <input type="text" 
                       name="search" 
                       class="kt-input flex-1" 
                       placeholder="Search by service number, name, or email..."
                       value="{{ request('search') }}">
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-magnifier"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- Officers List -->
    @if($officers->count() > 0)
        <div class="kt-card overflow-hidden">
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 800px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Roster Role</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($officers as $officer)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">{{ $officer->service_number ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm text-foreground">{{ $officer->initials }} {{ $officer->surname }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm text-secondary-foreground">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if(isset($officer->roster_role) && $officer->roster_role)
                                            <span class="kt-badge kt-badge-info kt-badge-sm">{{ $officer->roster_role }}</span>
                                        @else
                                            <span class="text-xs text-muted-foreground">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ (auth()->user()->hasRole('Staff Officer') || auth()->user()->hasRole('HRD')) ? route('staff-officer.aper-forms.access', $officer->id) : route('officer.aper-forms.access', $officer->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-document"></i> Create/Access Form
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($officers->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $officers->links() }}
                    </div>
                @endif
            </div>
        </div>
    @elseif(request('search'))
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers found matching your search.</p>
                </div>
            </div>
        </div>
    @else
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="text-center py-12">
                    <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">Use the search above to find officers and access their APER forms.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection


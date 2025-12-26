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
    <!-- Search Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Search Officers</h3>
        </div>
        <div class="kt-card-content">
            <form method="GET" action="{{ route('staff-officer.aper-forms.reporting-officer.search') }}" class="flex gap-3">
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
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('staff-officer.aper-forms.access', $officer->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-primary">
                                            Access APER Form
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


@extends('layouts.app')

@section('title', 'Search Forms for Countersigning')
@section('page-title', 'Search Forms for Countersigning')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <span class="text-primary">APER Forms - Countersigning</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Info Card -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-info text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-info">Countersigning Officer Pool</p>
                        <p class="text-xs text-secondary-foreground mt-1">
                            You become a Countersigning Officer for any form in your command where you are senior to the
                            Reporting Officer.
                            These forms are awaiting a countersignature.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Search Pending Forms</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('officer.aper-forms.countersigning.search') }}" class="flex gap-3">
                    <input type="text" name="search" class="kt-input flex-1"
                        placeholder="Search by officer service number or name..." value="{{ request('search') }}">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Forms List -->
        @if($forms->count() > 0)
            <div class="kt-card overflow-hidden">
                <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                    <div class="table-scroll-wrapper overflow-x-auto">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Reporting
                                        Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Forwarded
                                        Date</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forms as $form)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-foreground">{{ $form->officer->initials }}
                                                    {{ $form->officer->surname }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground">{{ $form->officer->service_number }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground">{{ $form->officer->substantive_rank }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($form->reportingOfficer && $form->reportingOfficer->officer)
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm text-foreground">{{ $form->reportingOfficer->officer->initials }}
                                                        {{ $form->reportingOfficer->officer->surname }}</span>
                                                    <span
                                                        class="text-xs text-muted-foreground">{{ $form->reportingOfficer->officer->substantive_rank }}</span>
                                                </div>
                                            @else
                                                <span class="text-sm text-muted-foreground">Unknown</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $form->reporting_officer_completed_at ? $form->reporting_officer_completed_at->format('d M Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('officer.aper-forms.countersigning', $form->id) }}"
                                                class="kt-btn kt-btn-sm kt-btn-primary">
                                                <i class="ki-filled ki-pen"></i> Countersign Form
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($forms->hasPages())
                        <div class="mt-6 pt-4 border-t border-border">
                            {{ $forms->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @elseif(request('search'))
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="text-center py-12">
                        <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No forms found matching your search.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">
                            No forms are currently awaiting countersigning in your command for which you are eligible (Rank >=
                            Reporting Officer).
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Pending Release Letters')
@section('page-title', 'Pending Release Letters')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Pending Release Letters</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Pending Release Letters</h2>
                <p class="text-sm text-secondary-foreground mt-1">
                    Officers in {{ $command->name ?? 'your command' }} who need release letters printed before transfer
                </p>
            </div>
            <a href="{{ route('staff-officer.dashboard') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Search Filter Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Search Officers</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('staff-officer.postings.pending-release-letters') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 min-w-[250px] w-full md:w-auto">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="kt-input w-full" 
                                       placeholder="Search by name, service number, rank, or department...">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-magnifier"></i> Search
                            </button>
                            @if(request()->filled('search'))
                                <a href="{{ route('staff-officer.postings.pending-release-letters') }}" class="kt-btn kt-btn-outline">
                                    <i class="ki-filled ki-cross"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pending Release Letters List -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officers Awaiting Release Letters</h3>
                <div class="kt-card-toolbar">
                    @if(request()->filled('search'))
                        <span class="kt-badge kt-badge-info kt-badge-sm">
                            {{ $pendingReleasePostings->total() }} result(s) found
                        </span>
                    @else
                        <span class="kt-badge kt-badge-secondary kt-badge-sm">
                            {{ $pendingReleasePostings->total() }} total
                        </span>
                    @endif
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5">
                @if($pendingReleasePostings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Posting Date</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingReleasePostings as $posting)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="text-sm font-medium">
                                                {{ $posting->officer->initials ?? '' }} {{ $posting->officer->surname ?? '' }}
                                            </div>
                                            <div class="text-xs text-secondary-foreground">
                                                {{ $posting->officer->service_number ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $posting->officer->substantive_rank ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $posting->command->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            @if($posting->movementOrder)
                                                <span class="kt-badge kt-badge-sm">MO: {{ $posting->movementOrder->order_number }}</span>
                                            @elseif($posting->staffOrder)
                                                <span class="kt-badge kt-badge-sm">SO: {{ $posting->staffOrder->order_number }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $posting->posting_date ? $posting->posting_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('staff-officer.postings.print-release-letter', $posting->id) }}" 
                                                   target="_blank"
                                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                                    <i class="ki-filled ki-printer"></i> Print Release Letter
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pendingReleasePostings->hasPages())
                        <div class="border-t border-border pt-4 mt-4 px-4 md:px-0">
                            {{ $pendingReleasePostings->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        @if(request()->filled('search'))
                            <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No officers found matching your search criteria.</p>
                            <a href="{{ route('staff-officer.postings.pending-release-letters') }}" class="kt-btn kt-btn-ghost mt-4">
                                Clear Search
                            </a>
                        @else
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No pending release letters. All officers have been released.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


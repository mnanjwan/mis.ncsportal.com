@extends('layouts.app')

@section('title', 'Query Management')
@section('page-title', 'Query Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Queries</span>
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
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-foreground">Queries</h2>
        <a href="{{ route('staff-officer.queries.create') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-plus"></i> Issue Query
        </a>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query List</h3>
            <div class="kt-card-toolbar">
                <form method="GET" action="{{ route('staff-officer.queries.index') }}" class="inline">
                    <select name="status" class="kt-input" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="PENDING_RESPONSE" {{ request('status') === 'PENDING_RESPONSE' ? 'selected' : '' }}>Pending Response</option>
                        <option value="PENDING_REVIEW" {{ request('status') === 'PENDING_REVIEW' ? 'selected' : '' }}>Pending Review</option>
                        <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Accepted</option>
                        <option value="DISAPPROVAL" {{ request('status') === 'DISAPPROVAL' ? 'selected' : '' }}>Disapproval</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($queries->count() > 0)
                <div class="table-scroll-wrapper overflow-x-auto">
                    <table class="kt-table" style="min-width: 800px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Reason</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Issued Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queries as $query)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $query->officer->initials }} {{ $query->officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $query->officer->service_number }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="max-w-xs truncate" title="{{ $query->reason }}">
                                            {{ Str::limit($query->reason, 50) }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($query->status === 'PENDING_RESPONSE')
                                            <span class="kt-badge kt-badge-warning">Pending Response</span>
                                        @elseif($query->status === 'PENDING_REVIEW')
                                            <span class="kt-badge kt-badge-info">Pending Review</span>
                                        @elseif($query->status === 'ACCEPTED')
                                            <span class="kt-badge kt-badge-success">Accepted</span>
                                        @elseif($query->status === 'DISAPPROVAL')
                                            <span class="kt-badge kt-badge-danger">Disapproval</span>
                                        @else
                                            <span class="kt-badge kt-badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('staff-officer.queries.show', $query->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 md:px-5 py-4 border-t border-border">
                    {{ $queries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No queries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Query Details')
@section('page-title', 'Query Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.queries.index') }}">Queries</a>
    <span>/</span>
    <span class="text-primary">Query Details</span>
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
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query Details</h3>
            <div class="kt-card-toolbar">
                @if($query->status === 'PENDING_RESPONSE')
                    <span class="kt-badge kt-badge-warning">Pending Response</span>
                @elseif($query->status === 'PENDING_REVIEW')
                    <span class="kt-badge kt-badge-info">Pending Review</span>
                @elseif($query->status === 'ACCEPTED')
                    <span class="kt-badge kt-badge-success">Accepted</span>
                @else
                    <span class="kt-badge kt-badge-danger">Rejected</span>
                @endif
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid gap-5">
                <div>
                    <label class="kt-label">Issued By</label>
                    <div class="kt-input bg-muted">
                        {{ $query->issuedBy->name ?? $query->issuedBy->email ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <label class="kt-label">Reason(s) for Query</label>
                    <div class="kt-input bg-muted whitespace-pre-wrap">{{ $query->reason }}</div>
                </div>

                <div>
                    <label class="kt-label">Issued Date</label>
                    <div class="kt-input bg-muted">
                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>

                @if($query->response)
                    <div>
                        <label class="kt-label">Your Response</label>
                        <div class="kt-input bg-muted whitespace-pre-wrap">{{ $query->response }}</div>
                    </div>
                    <div>
                        <label class="kt-label">Response Date</label>
                        <div class="kt-input bg-muted">
                            {{ $query->responded_at ? $query->responded_at->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                    </div>
                @endif

                @if($query->status === 'PENDING_RESPONSE')
                    <div class="border-t border-border pt-5">
                        <h4 class="font-semibold mb-3">Respond to Query</h4>
                        <form action="{{ route('officer.queries.respond', $query->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="kt-label">Your Response <span class="text-danger">*</span></label>
                                <textarea name="response" rows="6" class="kt-input @error('response') border-danger @enderror" placeholder="Provide your response to the query..." required>{{ old('response') }}</textarea>
                                @error('response')
                                    <p class="text-danger text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-muted-foreground mt-1">Minimum 10 characters required</p>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-check"></i> Submit Response
                                </button>
                                <a href="{{ route('officer.queries.index') }}" class="kt-btn kt-btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="flex gap-3 pt-5 border-t border-border">
                    <a href="{{ route('officer.queries.index') }}" class="kt-btn kt-btn-secondary">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


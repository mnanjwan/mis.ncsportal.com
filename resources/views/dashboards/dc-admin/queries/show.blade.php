@extends('layouts.app')

@section('title', 'Query Details')
@section('page-title', 'Query Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.queries.index') }}">Queries</a>
    <span>/</span>
    <span class="text-primary">Query Details</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query Details</h3>
            <div class="kt-card-toolbar">
                <span class="kt-badge kt-badge-success">Accepted</span>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid gap-5">
                <div>
                    <label class="kt-label">Officer</label>
                    <div class="kt-input bg-muted">
                        {{ $query->officer->initials }} {{ $query->officer->surname }} ({{ $query->officer->service_number }})
                    </div>
                </div>

                <div>
                    <label class="kt-label">Reason(s) for Query</label>
                    <div class="kt-input bg-muted whitespace-pre-wrap">{{ $query->reason }}</div>
                </div>

                <div>
                    <label class="kt-label">Issued By</label>
                    <div class="kt-input bg-muted">
                        {{ $query->issuedBy->name ?? $query->issuedBy->email ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <label class="kt-label">Issued Date</label>
                    <div class="kt-input bg-muted">
                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                </div>

                @if($query->response)
                    <div>
                        <label class="kt-label">Officer's Response</label>
                        <div class="kt-input bg-muted whitespace-pre-wrap">{{ $query->response }}</div>
                    </div>
                    <div>
                        <label class="kt-label">Response Date</label>
                        <div class="kt-input bg-muted">
                            {{ $query->responded_at ? $query->responded_at->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                    </div>
                @endif

                @if($query->reviewed_at)
                    <div>
                        <label class="kt-label">Reviewed Date</label>
                        <div class="kt-input bg-muted">
                            {{ $query->reviewed_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 pt-5 border-t border-border">
                    <a href="{{ route('dc-admin.queries.index') }}" class="kt-btn kt-btn-secondary">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Query Details')
@section('page-title', 'Query Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.queries.index') }}">Queries</a>
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

                @if($query->status === 'PENDING_REVIEW' && $query->issued_by_user_id === auth()->id())
                    <div class="border-t border-border pt-5">
                        <h4 class="font-semibold mb-3">Review Response</h4>
                        <form id="accept-query-form" action="{{ route('staff-officer.queries.accept', $query->id) }}" method="POST" class="inline-block mr-3">
                            @csrf
                            <button type="button" id="accept-query-btn" class="kt-btn kt-btn-success">
                                <i class="ki-filled ki-check"></i> Accept Query
                            </button>
                        </form>
                        <form id="reject-query-form" action="{{ route('staff-officer.queries.reject', $query->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="button" id="reject-query-btn" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-cross"></i> Reject Query
                            </button>
                        </form>
                    </div>
                @endif

                <div class="flex gap-3 pt-5 border-t border-border">
                    <a href="{{ route('staff-officer.queries.index') }}" class="kt-btn kt-btn-secondary">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accept Query Confirmation
    const acceptBtn = document.getElementById('accept-query-btn');
    const acceptForm = document.getElementById('accept-query-form');
    
    if (acceptBtn && acceptForm) {
        acceptBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Accept Query?',
                text: 'Are you sure you want to accept this query? It will be added to the officer\'s disciplinary record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Accept Query',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#068b57',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    acceptBtn.disabled = true;
                    acceptBtn.innerHTML = '<i class="ki-filled ki-check"></i> Processing...';
                    acceptForm.submit();
                }
            });
        });
    }
    
    // Reject Query Confirmation
    const rejectBtn = document.getElementById('reject-query-btn');
    const rejectForm = document.getElementById('reject-query-form');
    
    if (rejectBtn && rejectForm) {
        rejectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Reject Query?',
                text: 'Are you sure you want to reject this query? It will not be added to the officer\'s disciplinary record.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject Query',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    rejectBtn.disabled = true;
                    rejectBtn.innerHTML = '<i class="ki-filled ki-cross"></i> Processing...';
                    rejectForm.submit();
                }
            });
        });
    }
});
</script>
@endpush


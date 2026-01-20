@extends('layouts.app')

@section('title', 'Education Qualification Request Details')
@section('page-title', 'Education Qualification Request Details')

@section('breadcrumbs')
    @if(auth()->user() && auth()->user()->hasRole('HRD'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.education-requests.pending') }}">Education Requests</a>
        <span>/</span>
        <span class="text-primary">Details</span>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.education-requests.index') }}">Education Requests</a>
        <span>/</span>
        <span class="text-primary">Details</span>
    @endif
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Officer Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officer Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ ($request->officer->initials ?? '') . ' ' . ($request->officer->surname ?? '') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Service Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $request->officer->service_number ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Command</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->officer->presentStation->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Request Date</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requested Qualification -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requested Education Qualification</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">University/Institution</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->university }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Qualification</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->qualification }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Discipline</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->discipline ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Year Obtained</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $request->year_obtained }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supporting Documents -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Supporting Documents</h3>
            </div>
            <div class="kt-card-content">
                @if(($request->documents ?? collect())->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($request->documents as $doc)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-lg border border-input bg-muted/30">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">{{ $doc->file_name }}</span>
                                    <span class="text-xs text-secondary-foreground">
                                        @if($doc->file_size)
                                            {{ number_format($doc->file_size / 1024, 1) }} KB
                                        @else
                                            —
                                        @endif
                                        @if($doc->mime_type)
                                            • {{ $doc->mime_type }}
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('education-requests.documents.download', ['requestId' => $request->id, 'documentId' => $doc->id]) }}"
                                   class="kt-btn kt-btn-sm kt-btn-secondary">
                                    <i class="ki-filled ki-download"></i> Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-secondary-foreground">No documents attached.</div>
                @endif
            </div>
        </div>

        <!-- Status Information -->
        @if($request->status !== 'PENDING')
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Verification Details</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-secondary-foreground">Status</span>
                            <p class="mt-1">
                                <span class="kt-badge kt-badge-{{ $request->status === 'APPROVED' ? 'success' : 'danger' }} kt-badge-sm">
                                    {{ $request->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Verified By</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->verifier->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Verified At</span>
                            <p class="text-sm font-semibold text-foreground mt-1">
                                {{ $request->verified_at ? $request->verified_at->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                        @if($request->rejection_reason)
                            <div class="md:col-span-2">
                                <span class="text-sm text-secondary-foreground">Rejection Reason</span>
                                <p class="text-sm text-foreground mt-1">{{ $request->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="kt-card">
            <div class="kt-card-content">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-end gap-3">
                    @if(auth()->user() && auth()->user()->hasRole('HRD'))
                        <a href="{{ route('hrd.education-requests.pending') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                    @else
                        <a href="{{ route('officer.education-requests.index') }}" class="kt-btn kt-btn-secondary">
                            <i class="ki-filled ki-left"></i> Back to List
                        </a>
                    @endif

                    @if($request->status === 'PENDING' && auth()->user() && auth()->user()->hasRole('HRD'))
                        <form action="{{ route('hrd.education-requests.reject', $request->id) }}" method="POST" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                            @csrf
                            <input
                                type="text"
                                name="rejection_reason"
                                class="kt-input"
                                placeholder="Rejection reason (required)"
                                required
                                maxlength="500"
                            />
                            <button type="submit" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-cross"></i> Reject
                            </button>
                        </form>

                        <form action="{{ route('hrd.education-requests.approve', $request->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Approve
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


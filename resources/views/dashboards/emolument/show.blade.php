@extends('layouts.app')

@section('title', 'Emolument Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route($breadcrumbRoute) }}">{{ $breadcrumbRole }}</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route($backRoute) }}">Emoluments</a>
    <span>/</span>
    <span class="text-primary">Details</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-gray-900">Emolument Details</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Reference: #{{ str_pad($emolument->id, 6, '0', STR_PAD_LEFT) }} |
                    Year: {{ $emolument->year }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route($backRoute) }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-left"></i>
                    Back
            </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Current Status</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex items-center gap-4 flex-wrap">
                            @php
                                $statusClass = match ($emolument->status) {
                                    'RAISED' => 'info',
                                    'ASSESSED' => 'warning',
                                    'VALIDATED' => 'warning',
                                    'AUDITED' => 'success',
                                    'PROCESSED' => 'success',
                                    'REJECTED' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-lg">
                                {{ ucfirst(strtolower($emolument->status)) }}
                            </span>
                            <div class="text-sm text-secondary-foreground">
                                Last updated: {{ $emolument->updated_at->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank & PFA Details -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Payment Information</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-secondary-foreground uppercase tracking-wider mb-3">Bank Details
                                </h4>
                                <div class="bg-muted/50 p-4 rounded-lg">
                                    <div class="mb-2">
                                        <span class="text-xs text-secondary-foreground block">Bank Name</span>
                                        <span class="font-medium text-foreground">{{ $emolument->bank_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground block">Account Number</span>
                                        <span
                                            class="font-medium text-foreground font-mono">{{ $emolument->bank_account_number }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-secondary-foreground uppercase tracking-wider mb-3">PFA Details</h4>
                                <div class="bg-muted/50 p-4 rounded-lg">
                                    <div class="mb-2">
                                        <span class="text-xs text-secondary-foreground block">PFA Name</span>
                                        <span class="font-medium text-foreground">{{ $emolument->pfa_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground block">RSA PIN</span>
                                        <span class="font-medium text-foreground font-mono">{{ $emolument->rsa_pin }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Comments -->
                @if($emolument->status === 'REJECTED')
                    <div class="kt-card border-l-4 border-l-danger">
                        <div class="kt-card-header bg-danger/10">
                            <h3 class="kt-card-title text-danger">Rejection Details</h3>
                        </div>
                        <div class="kt-card-content space-y-4">
                            @if($emolument->validation && $emolument->validation->validation_status === 'REJECTED' && $emolument->validation->comments)
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground mb-2">Validation Rejection</h4>
                                    <div class="bg-danger/5 border border-danger/20 rounded-lg p-4">
                                        <p class="text-foreground whitespace-pre-line">{{ $emolument->validation->comments }}</p>
                                        <p class="text-xs text-secondary-foreground mt-2">
                                            Rejected on: {{ $emolument->validated_at ? $emolument->validated_at->format('d M Y, h:i A') : 'N/A' }}
                                            @if($emolument->validation->validator)
                                                by {{ $emolument->validation->validator->email }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                            @if($emolument->assessment && $emolument->assessment->assessment_status === 'REJECTED' && $emolument->assessment->comments)
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground mb-2">Assessment Rejection</h4>
                                    <div class="bg-danger/5 border border-danger/20 rounded-lg p-4">
                                        <p class="text-foreground whitespace-pre-line">{{ $emolument->assessment->comments }}</p>
                                        <p class="text-xs text-secondary-foreground mt-2">
                                            Rejected on: {{ $emolument->assessed_at ? $emolument->assessed_at->format('d M Y, h:i A') : 'N/A' }}
                                            @if($emolument->assessment->assessor)
                                                by {{ $emolument->assessment->assessor->email }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                            @if($emolument->audit && $emolument->audit->audit_status === 'REJECTED' && $emolument->audit->comments)
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground mb-2">Audit Rejection</h4>
                                    <div class="bg-danger/5 border border-danger/20 rounded-lg p-4">
                                        <p class="text-foreground whitespace-pre-line">{{ $emolument->audit->comments }}</p>
                                        <p class="text-xs text-secondary-foreground mt-2">
                                            Rejected on: {{ $emolument->audited_at ? $emolument->audited_at->format('d M Y, h:i A') : 'N/A' }}
                                            @if($emolument->audit->auditor)
                                                by {{ $emolument->audit->auditor->email }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($emolument->validation && $emolument->validation->validation_status === 'REJECTED')
                                @if(auth()->user()->officer && auth()->user()->officer->id === $emolument->officer_id)
                                    <div class="mt-4 pt-4 border-t border-border">
                                        <p class="text-sm text-secondary-foreground mb-3">
                                            Your emolument was rejected during validation. You can resubmit it after making the necessary corrections.
                                        </p>
                                        <form action="{{ route('emolument.resubmit', $emolument->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to resubmit this emolument? It will be sent back for assessment.');">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-primary">
                                                <i class="ki-filled ki-arrow-up"></i> Resubmit Emolument
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                @if($emolument->notes)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Notes</h3>
                        </div>
                        <div class="kt-card-content">
                            <p class="text-foreground whitespace-pre-line">{{ $emolument->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Timeline Info -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Timeline Info</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs text-secondary-foreground block">Emolument Year</span>
                                <span class="font-medium text-foreground">{{ $emolument->timeline->year ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-secondary-foreground block">Submission Period</span>
                                <span class="text-sm text-foreground">
                                    @if($emolument->timeline)
                                    {{ $emolument->timeline->start_date->format('d M Y') }} -
                                    {{ $emolument->timeline->end_date->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-secondary-foreground block">Submitted On</span>
                                <span class="text-sm text-foreground">
                                    {{ $emolument->submitted_at ? $emolument->submitted_at->format('d M Y, h:i A') : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Steps -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Workflow</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="relative">
                            @php
                                $isRaised = $emolument->submitted_at !== null;
                                $isAssessed = in_array($emolument->status, ['ASSESSED', 'VALIDATED', 'AUDITED', 'PROCESSED']) || $emolument->assessed_at;
                                $isValidated = in_array($emolument->status, ['VALIDATED', 'AUDITED', 'PROCESSED']) || $emolument->validated_at;
                                $isAudited = in_array($emolument->status, ['AUDITED', 'PROCESSED']) || $emolument->audited_at;
                                $isProcessed = $emolument->status === 'PROCESSED' || $emolument->processed_at;
                            @endphp
                            
                            <!-- Vertical Line - green for completed steps, gray for pending -->
                            @php
                                $lastCompletedStep = $isProcessed ? 5 : ($isAudited ? 4 : ($isValidated ? 3 : ($isAssessed ? 2 : ($isRaised ? 1 : 0))));
                                $lineColor = $lastCompletedStep > 0 ? 'bg-[#088a56]' : 'bg-border';
                            @endphp
                            <div class="absolute left-3 top-0 bottom-0 w-0.5 {{ $lineColor }}"></div>
                            
                            <div class="relative space-y-6">
                            <!-- Step 1: Raised -->
                                <div class="relative flex items-start gap-4">
                                    <div class="flex-shrink-0 relative z-10">
                                        <div class="w-6 h-6 rounded-full {{ $isRaised ? 'bg-[#088a56]' : 'bg-gray-200' }} border-2 border-white shadow-sm flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                </div>
                                    <div class="flex-1 pt-0.5">
                                        <h4 class="text-sm font-semibold {{ $isRaised ? 'text-foreground' : 'text-secondary-foreground' }} mb-1">Raised</h4>
                                        <p class="text-xs text-secondary-foreground">
                                    {{ $emolument->submitted_at ? $emolument->submitted_at->format('d M Y') : 'Pending' }}
                                </p>
                                    </div>
                            </div>

                            <!-- Step 2: Assessed -->
                                <div class="relative flex items-start gap-4">
                                    <div class="flex-shrink-0 relative z-10">
                                        <div class="w-6 h-6 rounded-full {{ $isAssessed ? 'bg-[#088a56]' : 'bg-gray-200' }} border-2 border-white shadow-sm flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-0.5">
                                        <h4 class="text-sm font-semibold {{ $isAssessed ? 'text-foreground' : 'text-secondary-foreground' }} mb-1">
                                            Assessed
                                        </h4>
                                        <p class="text-xs text-secondary-foreground">
                                            @if($isAssessed)
                                                {{ $emolument->assessed_at ? $emolument->assessed_at->format('d M Y') : 'Completed' }}
                                            @else
                                                Pending
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 3: Validated -->
                                <div class="relative flex items-start gap-4">
                                    <div class="flex-shrink-0 relative z-10">
                                        <div class="w-6 h-6 rounded-full {{ $isValidated ? 'bg-[#088a56]' : 'bg-gray-200' }} border-2 border-white shadow-sm flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-0.5">
                                        <h4 class="text-sm font-semibold {{ $isValidated ? 'text-foreground' : 'text-secondary-foreground' }} mb-1">
                                            Validated
                                        </h4>
                                        <p class="text-xs text-secondary-foreground">
                                            @if($isValidated)
                                                {{ $emolument->validated_at ? $emolument->validated_at->format('d M Y') : 'Completed' }}
                                            @else
                                                Pending
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 4: Audited -->
                                <div class="relative flex items-start gap-4">
                                    <div class="flex-shrink-0 relative z-10">
                                        <div class="w-6 h-6 rounded-full {{ $isAudited ? 'bg-[#088a56]' : 'bg-gray-200' }} border-2 border-white shadow-sm flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-0.5">
                                        <h4 class="text-sm font-semibold {{ $isAudited ? 'text-foreground' : 'text-secondary-foreground' }} mb-1">
                                            Audited
                                        </h4>
                                        <p class="text-xs text-secondary-foreground">
                                            @if($isAudited)
                                                {{ $emolument->audited_at ? $emolument->audited_at->format('d M Y') : 'Completed' }}
                                            @else
                                                Pending
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Step 5: Processed -->
                                <div class="relative flex items-start gap-4">
                                    <div class="flex-shrink-0 relative z-10">
                                        <div class="w-6 h-6 rounded-full {{ $isProcessed ? 'bg-[#088a56]' : 'bg-gray-200' }} border-2 border-white shadow-sm flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-0.5">
                                        <h4 class="text-sm font-semibold {{ $isProcessed ? 'text-foreground' : 'text-secondary-foreground' }} mb-1">
                                            Processed
                                        </h4>
                                        <p class="text-xs text-secondary-foreground">
                                            @if($isProcessed)
                                                {{ $emolument->processed_at ? $emolument->processed_at->format('d M Y') : 'Completed' }}
                                            @else
                                                Pending
                                            @endif
                                </p>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
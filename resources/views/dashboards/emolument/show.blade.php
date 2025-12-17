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
                                    'VALIDATED' => 'success',
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
                                $isAssessed = in_array($emolument->status, ['ASSESSED', 'VALIDATED', 'PROCESSED']) || $emolument->assessed_at;
                                $isValidated = in_array($emolument->status, ['VALIDATED', 'PROCESSED']) || $emolument->validated_at;
                                $isProcessed = $emolument->status === 'PROCESSED' || $emolument->processed_at;
                            @endphp
                            
                            <!-- Vertical Line - green for completed steps, gray for pending -->
                            @php
                                $lastCompletedStep = $isProcessed ? 4 : ($isValidated ? 3 : ($isAssessed ? 2 : ($isRaised ? 1 : 0)));
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

                                <!-- Step 4: Processed -->
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
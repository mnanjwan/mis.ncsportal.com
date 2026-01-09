@extends('layouts.app')

@section('title', 'Audit Emolument')
@section('page-title', 'Audit Emolument')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('auditor.dashboard') }}">Auditor</a>
    <span>/</span>
    <span class="text-primary">Audit Emolument</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Emolument Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Emolument Details</h3>
                </div>
                <div class="kt-card-content space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Officer Name</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $emolument->officer->initials }} {{ $emolument->officer->surname }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Service Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->officer->service_number }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Bank Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->bank_name }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">Account Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->bank_account_number }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">PFA Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->pfa_name }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-secondary-foreground">RSA PIN</span>
                            <span class="text-sm font-semibold text-mono">{{ $emolument->rsa_pin }}</span>
                        </div>
                    </div>

                    @if($emolument->notes)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Officer Notes</span>
                            <p class="text-sm text-mono">{{ $emolument->notes }}</p>
                        </div>
                    @endif

                    @if($emolument->assessment)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Assessment Comments</span>
                            <p class="text-sm text-mono">{{ $emolument->assessment->comments ?? 'No comments' }}</p>
                            <span class="text-xs text-secondary-foreground mt-1">
                                Assessed on: {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </div>
                    @endif

                    @if($emolument->validation)
                        <div class="flex flex-col gap-1 pt-4 border-t border-border">
                            <span class="text-sm text-secondary-foreground">Validation Comments</span>
                            <p class="text-sm text-mono">{{ $emolument->validation->comments ?? 'No comments' }}</p>
                            <span class="text-xs text-secondary-foreground mt-1">
                                Validated on: {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y H:i') : 'N/A' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Form -->
            <form class="kt-card" action="{{ route('emolument.process-audit', $emolument->id) }}" method="POST">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="kt-card-header">
                    <h3 class="kt-card-title">Audit Decision</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Decision <span class="text-red-500">*</span></label>
                        <select class="kt-input @error('audit_status') border-red-500 @enderror" name="audit_status" required>
                            <option value="">Select Decision</option>
                            <option value="APPROVED" {{ old('audit_status') == 'APPROVED' ? 'selected' : '' }}>Approve</option>
                            <option value="REJECTED" {{ old('audit_status') == 'REJECTED' ? 'selected' : '' }}>Reject</option>
                        </select>
                        @error('audit_status')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Comments</label>
                        <textarea class="kt-input @error('comments') border-red-500 @enderror" name="comments" rows="4"
                            placeholder="Enter audit comments (optional for approval, required for rejection)">{{ old('comments') }}</textarea>
                        @error('comments')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('auditor.dashboard') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit">
                        Submit Audit
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="xl:col-span-1">
            <!-- Guidelines Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Audit Guidelines</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <p class="text-xs text-secondary-foreground">
                            Please perform final audit verification before approving for payment processing.
                        </p>
                        <ul class="text-xs text-secondary-foreground list-disc list-inside space-y-1">
                            <li>Review assessment and validation records</li>
                            <li>Verify all information is accurate</li>
                            <li>Check compliance with audit requirements</li>
                            <li>Final approval before Accounts processing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


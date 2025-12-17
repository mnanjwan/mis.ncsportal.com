@extends('layouts.app')

@section('title', 'Assess Emolument')
@section('page-title', 'Assess Emolument')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('assessor.dashboard') }}">Assessor</a>
    <span>/</span>
    <span class="text-primary">Assess Emolument</span>
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
                </div>
            </div>

            <!-- Assessment Form -->
            <form class="kt-card" action="{{ route('emolument.process-assessment', $emolument->id) }}" method="POST">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Assessment Decision</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Decision</label>
                        <select class="kt-input" name="assessment_status" required>
                            <option value="">Select Decision</option>
                            <option value="APPROVED">Approve</option>
                            <option value="REJECTED">Reject</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Comments</label>
                        <textarea class="kt-input" name="comments" rows="4"
                            placeholder="Enter assessment comments (optional for approval, required for rejection)"></textarea>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('assessor.dashboard') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit">
                        Submit Assessment
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="xl:col-span-1">
            <!-- Guidelines Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Assessment Guidelines</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <p class="text-xs text-secondary-foreground">
                            Please verify the bank details and PFA information against the officer's records.
                        </p>
                        <ul class="text-xs text-secondary-foreground list-disc list-inside space-y-1">
                            <li>Ensure Account Number is 10 digits</li>
                            <li>Verify RSA PIN format</li>
                            <li>Check for any discrepancies</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
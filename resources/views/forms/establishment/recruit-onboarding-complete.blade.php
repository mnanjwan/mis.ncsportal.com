@extends('layouts.public')

@section('title', 'Onboarding Completed')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card bg-success/10 border border-success/20">
        <div class="kt-card-content p-8 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="size-20 rounded-full bg-success flex items-center justify-center">
                    <i class="ki-filled ki-check-circle text-white text-5xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-success">Onboarding Completed Successfully!</h2>
                <p class="text-lg text-secondary-foreground max-w-2xl">
                    Thank you, <strong>{{ trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? '')) }}</strong>, for completing your onboarding process.
                </p>
                <div class="kt-card bg-background border border-border mt-4 p-6 max-w-xl w-full">
                    <div class="kt-card-content">
                        <h3 class="text-lg font-semibold text-foreground mb-4">What Happens Next?</h3>
                        <div class="space-y-3 text-left">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-check-circle text-success text-xl mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-foreground">Document Verification</p>
                                    <p class="text-xs text-secondary-foreground">The Establishment office will review your submitted documents and information.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-check-circle text-success text-xl mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-foreground">Verification Status</p>
                                    <p class="text-xs text-secondary-foreground">You will be notified once your documents have been verified.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-check-circle text-success text-xl mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-medium text-foreground">Training Assignment</p>
                                    <p class="text-xs text-secondary-foreground">After verification, you will be assigned to training and receive your service number.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 p-4 bg-info/10 border border-info/20 rounded-lg max-w-xl w-full">
                    <div class="flex items-start gap-3">
                        <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-info mb-1">Important Information</p>
                            <p class="text-xs text-secondary-foreground">
                                Please keep your email address (<strong>{{ $recruit->email }}</strong>) active. 
                                You will receive further instructions and updates via email.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


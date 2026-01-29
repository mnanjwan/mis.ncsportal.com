@extends('layouts.app')

@section('title', 'My Retirement Information')
@section('page-title', 'My Retirement Information')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.profile') }}">Profile</a>
    <span>/</span>
    <span class="text-primary">Retirement</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(!$retirementDate)
            <!-- No Retirement Data Available -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-2">Retirement information is not available</p>
                        <p class="text-sm text-secondary-foreground">
                            Please ensure your Date of Birth and Date of First Appointment are recorded in your profile.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Retirement Alert Banner -->
            @if($isApproachingRetirement && $retirementAlert && $retirementAlert->alert_sent)
                <div class="kt-card bg-warning/10 border border-warning/20">
                    <div class="kt-card-content p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex items-center justify-center size-12 rounded-full bg-warning/20 shrink-0">
                                <i class="ki-filled ki-notification-bing text-2xl text-warning"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-foreground mb-2">Retirement Alert</h3>
                                <p class="text-sm text-secondary-foreground mb-3">
                                    You are approaching retirement! Your retirement date is in <strong>{{ number_format($daysUntilRetirement) }} days</strong>.
                                </p>
                                <div class="flex flex-col gap-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-secondary-foreground">Retirement Date:</span>
                                        <span class="font-semibold text-foreground">{{ $retirementDate->format('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-secondary-foreground">Retirement Type:</span>
                                        <span class="kt-badge kt-badge-{{ $retirementType === 'AGE' ? 'info' : 'success' }} kt-badge-sm">
                                            {{ $retirementType === 'AGE' ? 'Age-based (60 years)' : 'Service-based (35 years)' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-secondary-foreground">Alert Sent:</span>
                                        <span class="text-foreground">{{ $retirementAlert->alert_sent_at->format('d F Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Retirement Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Retirement Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Retirement Date -->
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-calendar text-primary text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-secondary-foreground block">Retirement Date</span>
                                    <span class="text-lg font-semibold text-foreground">{{ $retirementDate->format('d F Y') }}</span>
                                </div>
                            </div>
                            @if($daysUntilRetirement !== null)
                                <p class="text-sm text-secondary-foreground mt-2">
                                    @if($daysUntilRetirement > 0)
                                        <span class="font-medium text-foreground">{{ number_format($daysUntilRetirement) }}</span> days remaining
                                    @else
                                        <span class="font-medium text-danger">Retired</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <!-- Retirement Type -->
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                                    <i class="ki-filled ki-information text-info text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-secondary-foreground block">Retirement Type</span>
                                    <span class="kt-badge kt-badge-{{ $retirementType === 'AGE' ? 'info' : 'success' }} kt-badge-sm mt-1">
                                        {{ $retirementType === 'AGE' ? 'Age-based (60 years)' : 'Service-based (35 years)' }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs text-secondary-foreground mt-2">
                                {{ $retirementType === 'AGE' ? 'Based on reaching 60 years of age' : 'Based on completing 35 years of service' }}
                            </p>
                        </div>

                        <!-- Date of Birth -->
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <span class="text-xs text-secondary-foreground block mb-1">Date of Birth</span>
                            <span class="text-sm font-semibold text-foreground">
                                {{ $officer->date_of_birth ? $officer->date_of_birth->format('d F Y') : 'N/A' }}
                            </span>
                            @if($retirementType === 'AGE')
                                <p class="text-xs text-secondary-foreground mt-2">
                                    Age-based retirement: {{ $officer->date_of_birth->copy()->addYears(60)->format('d F Y') }}
                                </p>
                            @endif
                        </div>

                        <!-- Date of First Appointment -->
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <span class="text-xs text-secondary-foreground block mb-1">Date of First Appointment</span>
                            <span class="text-sm font-semibold text-foreground">
                                {{ $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('d F Y') : 'N/A' }}
                            </span>
                            @if($retirementType === 'SVC')
                                <p class="text-xs text-secondary-foreground mt-2">
                                    Service-based retirement: {{ $officer->date_of_first_appointment->copy()->addYears(35)->format('d F Y') }}
                                </p>
                            @endif
                        </div>

                        <!-- Pre-Retirement Leave Date -->
                        @if($alertDate)
                            <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                <span class="text-xs text-secondary-foreground block mb-1">Pre-Retirement Leave Date</span>
                                <span class="text-sm font-semibold text-foreground">
                                    {{ $alertDate->format('d F Y') }}
                                </span>
                                <p class="text-xs text-secondary-foreground mt-2">
                                    3 months before retirement date
                                </p>
                            </div>
                        @endif

                        <!-- Alert Status -->
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <span class="text-xs text-secondary-foreground block mb-1">Alert Status</span>
                            @if($retirementAlert && $retirementAlert->alert_sent)
                                <span class="kt-badge kt-badge-success kt-badge-sm">Alert Sent</span>
                                <p class="text-xs text-secondary-foreground mt-2">
                                    Sent on {{ $retirementAlert->alert_sent_at->format('d F Y H:i') }}
                                </p>
                            @elseif($isApproachingRetirement)
                                <span class="kt-badge kt-badge-warning kt-badge-sm">Alert Pending</span>
                                <p class="text-xs text-secondary-foreground mt-2">
                                    Alert will be sent soon
                                </p>
                            @else
                                <span class="kt-badge kt-badge-secondary kt-badge-sm">Not Yet</span>
                                <p class="text-xs text-secondary-foreground mt-2">
                                    Alert will be sent 3 months before retirement
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retirement Calculation Details -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Retirement Calculation</h3>
                </div>
                <div class="kt-card-content">
                    <div class="space-y-4">
                        <div class="p-4 rounded-lg bg-info/10 border border-info/20">
                            <h4 class="text-sm font-semibold text-foreground mb-2">How Retirement Date is Calculated</h4>
                            <p class="text-sm text-secondary-foreground mb-3">
                                Your retirement date is calculated based on whichever comes <strong>earlier</strong>:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-sm text-secondary-foreground">
                                <li><strong>Age-based:</strong> Date of Birth + 60 years = {{ $officer->date_of_birth ? $officer->date_of_birth->copy()->addYears(60)->format('d F Y') : 'N/A' }}</li>
                                <li><strong>Service-based:</strong> Date of First Appointment + 35 years = {{ $officer->date_of_first_appointment ? $officer->date_of_first_appointment->copy()->addYears(35)->format('d F Y') : 'N/A' }}</li>
                            </ul>
                            <p class="text-sm font-semibold text-foreground mt-3">
                                Your retirement date: <span class="text-primary">{{ $retirementDate->format('d F Y') }}</span> ({{ $retirementType === 'AGE' ? 'Age-based' : 'Service-based' }})
                            </p>
                        </div>

                        <div class="p-4 rounded-lg bg-warning/10 border border-warning/20">
                            <h4 class="text-sm font-semibold text-foreground mb-2">3-Month Alert System</h4>
                            <p class="text-sm text-secondary-foreground">
                                You will receive an alert notification <strong>3 months before</strong> your retirement date ({{ $alertDate ? $alertDate->format('d F Y') : 'N/A' }}). 
                                This alert helps you prepare for retirement and ensures all necessary documentation and processes are completed in time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

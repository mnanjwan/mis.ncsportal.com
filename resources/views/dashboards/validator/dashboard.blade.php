@extends('layouts.app')

@section('title', 'Validator Dashboard')
@section('page-title', 'Validator Dashboard')

@section('breadcrumbs')
    <span class="text-primary">Validator Dashboard</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    @if(isset($error))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ $error }}</p>
                </div>
            </div>
        </div>
    @else
        <!-- Command Information -->
        @if($command)
            <div class="kt-card bg-primary/5 border border-primary/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-geolocation text-primary text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">Command: {{ $command->name }}</span>
                            <span class="text-xs text-secondary-foreground">Code: {{ $command->code ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Command Officers</span>
                            <span class="text-2xl font-semibold text-mono">{{ $totalCommandOfficers ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-people text-2xl text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Validation</span>
                            <span class="text-2xl font-semibold text-mono">{{ $pendingValidationCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-wallet text-2xl text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Validated</span>
                            <span class="text-2xl font-semibold text-mono">{{ $validatedCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-verify text-2xl text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Processed</span>
                            <span class="text-2xl font-semibold text-mono">{{ $processedCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-check-circle text-2xl text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Statistics Cards -->

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Pending Emoluments for Validation -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="flex items-center justify-between">
                        <h3 class="kt-card-title">Pending Emoluments for Validation</h3>
                        <a href="{{ route('validator.emoluments') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                            <i class="ki-filled ki-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @forelse($pendingEmoluments as $emolument)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                                        <i class="ki-filled ki-wallet text-warning text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground font-mono">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Assessed: {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y H:i') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('validator.emoluments.validate', $emolument->id) }}"
                                    class="kt-btn kt-btn-success kt-btn-sm">
                                    <i class="ki-filled ki-eye"></i>
                                    Validate
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                                <p class="text-secondary-foreground">No pending emoluments for validation</p>
                                <p class="text-xs text-secondary-foreground mt-2">All assessed emoluments from your command have been validated</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Emolument Status Breakdown -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Emolument Status Breakdown</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                                    <i class="ki-filled ki-wallet text-warning"></i>
                                </div>
                                <span class="text-sm font-medium text-foreground">Assessed (Pending Validation)</span>
                            </div>
                            <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $emolumentStatus['ASSESSED'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                                    <i class="ki-filled ki-verify text-success"></i>
                                </div>
                                <span class="text-sm font-medium text-foreground">Validated</span>
                            </div>
                            <span class="kt-badge kt-badge-success kt-badge-sm">{{ $emolumentStatus['VALIDATED'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                                    <i class="ki-filled ki-check-circle text-info"></i>
                                </div>
                                <span class="text-sm font-medium text-foreground">Processed</span>
                            </div>
                            <span class="kt-badge kt-badge-info kt-badge-sm">{{ $emolumentStatus['PROCESSED'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                                    <i class="ki-filled ki-cross-circle text-danger"></i>
                                </div>
                                <span class="text-sm font-medium text-foreground">Rejected</span>
                            </div>
                            <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $emolumentStatus['REJECTED'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Recent Validations -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="flex items-center justify-between">
                        <h3 class="kt-card-title">Recent Validations</h3>
                        <a href="{{ route('validator.emoluments') }}?status=VALIDATED" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                            <i class="ki-filled ki-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @forelse($recentValidations as $emolument)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                                        <i class="ki-filled ki-verify text-success"></i>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground font-mono">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-0.5">
                                    <span class="kt-badge kt-badge-success kt-badge-sm">Validated</span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $emolument->validation?->validated_at ? $emolument->validation->validated_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-4">No recent validations</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Command Officers -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="flex items-center justify-between">
                        <h3 class="kt-card-title">Command Officers</h3>
                        <span class="kt-badge kt-badge-primary">{{ $totalCommandOfficers ?? 0 }} Officers</span>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @forelse($recentCommandOfficers as $officer)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-profile-circle text-primary"></i>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground font-mono">
                                            SVC: {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-xs text-secondary-foreground">
                                    {{ $officer->created_at ? $officer->created_at->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-4">No officers in command</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Recent Activities -->
    @endif
</div>
@endsection

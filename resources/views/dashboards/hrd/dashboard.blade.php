@extends('layouts.app')

@section('title', 'HRD Dashboard')
@section('page-title', 'HRD Dashboard')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Serving Officers</span>
                        <span class="text-2xl font-semibold text-mono">{{ $servingOfficers ?? 0 }}</span>
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
                        <span class="text-sm font-normal text-secondary-foreground">Pending Emoluments</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingEmoluments ?? 0 }}</span>
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
                        <span class="text-sm font-normal text-secondary-foreground">Active Timeline</span>
                        <span class="text-sm font-semibold text-mono">
                            @if($activeTimeline)
                                {{ $activeTimeline->start_date->format('d/m/Y') }} - {{ $activeTimeline->end_date->format('d/m/Y') }}
                            @else
                                No active timeline
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-calendar-2 text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Staff Orders</span>
                        <span class="text-2xl font-semibold text-mono">{{ $staffOrdersCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-file-up text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Statistics Cards -->

    <!-- Quick Actions Section -->
        <div class="kt-card">
            <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions - Pending Items Requiring Attention</h3>
            </div>
            <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Approved Manning Requests -->
                <a href="{{ route('hrd.manning-requests') }}" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-people text-xl text-primary"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Manning Requests</h4>
                                    <p class="text-xs text-secondary-foreground">Approved - Ready for Matching</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-primary kt-badge-sm">{{ $approvedManningRequestsCount ?? 0 }}</span>
                        </div>
                        @if(($approvedManningRequestsCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($approvedManningRequests->take(3) as $request)
                                    <div class="truncate">
                                        {{ $request->command->name ?? 'N/A' }} - {{ $request->items->count() }} item(s)
                                    </div>
                                @endforeach
                                @if($approvedManningRequestsCount > 3)
                                    <div class="text-primary font-medium">+{{ $approvedManningRequestsCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending requests</p>
                        @endif
                    </div>
                </a>

                <!-- Draft Deployments -->
                <a href="{{ route('hrd.manning-deployments.draft') }}" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                                    <i class="ki-filled ki-file-up text-xl text-warning"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Draft Deployments</h4>
                                    <p class="text-xs text-secondary-foreground">Pending Publication</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $draftDeploymentsCount ?? 0 }}</span>
                        </div>
                        @if(($draftDeploymentsCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($draftDeployments->take(3) as $deployment)
                                    <div class="truncate">
                                        {{ $deployment->deployment_number }} - {{ $deployment->assignments->count() }} officer(s)
                                    </div>
                                @endforeach
                                @if($draftDeploymentsCount > 3)
                                    <div class="text-primary font-medium">+{{ $draftDeploymentsCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No draft deployments</p>
                        @endif
                    </div>
                </a>

                <!-- APER Forms Pending Grading -->
                <a href="{{ route('hrd.aper-forms') }}?status=HRD_GRADING" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-file text-xl text-info"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">APER Forms</h4>
                                    <p class="text-xs text-secondary-foreground">Pending HRD Grading</p>
                </div>
            </div>
                            <span class="kt-badge kt-badge-info kt-badge-sm">{{ $pendingAperFormsCount ?? 0 }}</span>
                        </div>
                        @if(($pendingAperFormsCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($pendingAperForms->take(3) as $form)
                                    <div class="truncate">
                                        {{ $form->officer->initials ?? '' }} {{ $form->officer->surname ?? '' }}
                                    </div>
                                @endforeach
                                @if($pendingAperFormsCount > 3)
                                    <div class="text-primary font-medium">+{{ $pendingAperFormsCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending forms</p>
                        @endif
        </div>
                </a>

                <!-- Queries Pending Review -->
                <a href="{{ route('hrd.queries.index') }}?status=PENDING_REVIEW" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-danger/10">
                                    <i class="ki-filled ki-information text-xl text-danger"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Queries</h4>
                                    <p class="text-xs text-secondary-foreground">Pending HRD Review</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $pendingQueriesCount ?? 0 }}</span>
                        </div>
                        @if(($pendingQueriesCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($pendingQueries->take(3) as $query)
                                    <div class="truncate">
                                        {{ $query->officer->initials ?? '' }} {{ $query->officer->surname ?? '' }}
                                    </div>
                                @endforeach
                                @if($pendingQueriesCount > 3)
                                    <div class="text-primary font-medium">+{{ $pendingQueriesCount - 3 }} more</div>
                                @endif
            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending queries</p>
                        @endif
                    </div>
                </a>

                <!-- Officer Onboarding -->
                <a href="{{ route('hrd.officers') }}?onboarding_status=PENDING" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                    <i class="ki-filled ki-profile-circle text-xl text-success"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Onboarding</h4>
                                    <p class="text-xs text-secondary-foreground">Pending Verification</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-success kt-badge-sm">{{ $pendingOnboardingCount ?? 0 }}</span>
                        </div>
                        @if(($pendingOnboardingCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($pendingOnboarding->take(3) as $officer)
                                    <div class="truncate">
                                        {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                    </div>
                                @endforeach
                                @if($pendingOnboardingCount > 3)
                                    <div class="text-primary font-medium">+{{ $pendingOnboardingCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending onboarding</p>
                        @endif
                    </div>
                </a>

                <!-- Draft Movement Orders -->
                <a href="{{ route('hrd.movement-orders') }}?status=DRAFT" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-secondary/10">
                                    <i class="ki-filled ki-file-up text-xl text-secondary-foreground"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Movement Orders</h4>
                                    <p class="text-xs text-secondary-foreground">Draft Status</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-secondary kt-badge-sm">{{ $draftMovementOrdersCount ?? 0 }}</span>
                        </div>
                        @if(($draftMovementOrdersCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($draftMovementOrders->take(3) as $order)
                                    <div class="truncate">
                                        {{ $order->order_number }}
                                    </div>
                                @endforeach
                                @if($draftMovementOrdersCount > 3)
                                    <div class="text-primary font-medium">+{{ $draftMovementOrdersCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No draft orders</p>
                        @endif
                    </div>
                </a>

                <!-- Account Change Requests -->
                <a href="{{ route('hrd.officers') }}?account_changes=PENDING" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-purple-500/10">
                                    <i class="ki-filled ki-wallet text-xl text-purple-500"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Account Changes</h4>
                                    <p class="text-xs text-secondary-foreground">Pending Approval</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-sm" style="background-color: rgba(168, 85, 247, 0.1); color: rgb(168, 85, 247);">{{ $pendingAccountChangesCount ?? 0 }}</span>
                        </div>
                        @if(($pendingAccountChangesCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($pendingAccountChanges->take(3) as $change)
                                    <div class="truncate">
                                        {{ $change->officer->initials ?? '' }} {{ $change->officer->surname ?? '' }}
                                    </div>
                                @endforeach
                                @if($pendingAccountChangesCount > 3)
                                    <div class="text-primary font-medium">+{{ $pendingAccountChangesCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending changes</p>
                        @endif
                    </div>
                </a>

                <!-- Next of Kin Change Requests -->
                <a href="{{ route('hrd.officers') }}?nok_changes=PENDING" class="kt-card hover:shadow-lg transition-shadow">
                    <div class="kt-card-content p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center size-12 rounded-full bg-pink-500/10">
                                    <i class="ki-filled ki-people text-xl text-pink-500"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm">Next of Kin Changes</h4>
                                    <p class="text-xs text-secondary-foreground">Pending Approval</p>
                                </div>
                            </div>
                            <span class="kt-badge kt-badge-sm" style="background-color: rgba(236, 72, 153, 0.1); color: rgb(236, 72, 153);">{{ $pendingNokChangesCount ?? 0 }}</span>
                        </div>
                        @if(($pendingNokChangesCount ?? 0) > 0)
                            <div class="text-xs text-secondary-foreground space-y-1">
                                @foreach($pendingNokChanges->take(3) as $change)
                                    <div class="truncate">
                                        {{ $change->officer->initials ?? '' }} {{ $change->officer->surname ?? '' }}
                                    </div>
                                @endforeach
                                @if($pendingNokChangesCount > 3)
                                    <div class="text-primary font-medium">+{{ $pendingNokChangesCount - 3 }} more</div>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary-foreground">No pending changes</p>
                        @endif
                </div>
                </a>
            </div>
        </div>
    </div>
    <!-- End of Quick Actions -->
</div>

@endsection

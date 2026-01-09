@extends('layouts.app')

@section('title', 'Auditor Dashboard')
@section('page-title', 'Auditor Dashboard')

@section('breadcrumbs')
    <span class="text-primary">Auditor Dashboard</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Pending Audit</span>
                        <span class="text-2xl font-semibold text-mono">{{ $pendingAuditCount ?? 0 }}</span>
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
                        <span class="text-sm font-normal text-secondary-foreground">Audited Today</span>
                        <span class="text-2xl font-semibold text-mono">{{ $auditedToday ?? 0 }}</span>
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
                        <span class="text-sm font-normal text-secondary-foreground">Audited This Month</span>
                        <span class="text-2xl font-semibold text-mono">{{ $auditedThisMonth ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-calendar text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Audited</span>
                        <span class="text-2xl font-semibold text-mono">{{ $totalAudited ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-check-circle text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Statistics Cards -->

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        <!-- Pending Emoluments for Audit -->
        <div class="kt-card">
            <div class="kt-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="kt-card-title">Pending Emoluments for Audit</h3>
                    <a href="{{ route('auditor.emoluments') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
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
                                        Validated: {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('auditor.emoluments.audit', $emolument->id) }}"
                                class="kt-btn kt-btn-success kt-btn-sm">
                                <i class="ki-filled ki-eye"></i>
                                Audit
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No pending emoluments for audit</p>
                            <p class="text-xs text-secondary-foreground mt-2">All validated emoluments have been audited</p>
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
                            <span class="text-sm font-medium text-foreground">Validated (Pending Audit)</span>
                        </div>
                        <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $emolumentStatus['VALIDATED'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                                <i class="ki-filled ki-verify text-success"></i>
                            </div>
                            <span class="text-sm font-medium text-foreground">Audited</span>
                        </div>
                        <span class="kt-badge kt-badge-success kt-badge-sm">{{ $emolumentStatus['AUDITED'] ?? 0 }}</span>
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
        <!-- Recent Audits -->
        <div class="kt-card">
            <div class="kt-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="kt-card-title">Recent Audits</h3>
                    <a href="{{ route('auditor.emoluments') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                        View All
                        <i class="ki-filled ki-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @forelse($recentAudits as $emolument)
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
                                <span class="kt-badge kt-badge-success kt-badge-sm">Audited</span>
                                <span class="text-xs text-secondary-foreground">
                                    {{ $emolument->audited_at ? $emolument->audited_at->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary-foreground text-center py-4">No recent audits</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card bg-accent/50">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('auditor.emoluments') }}" class="kt-btn kt-btn-primary w-full justify-start">
                        <i class="ki-filled ki-wallet"></i>
                        View All Emoluments
                    </a>
                    <a href="{{ route('auditor.emoluments') }}?status=VALIDATED" class="kt-btn kt-btn-outline w-full justify-start">
                        <i class="ki-filled ki-filter"></i>
                        Pending Audit Only
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Recent Activities -->
</div>
@endsection


@extends('layouts.app')

@section('title', 'Controller Pharmacy Dashboard')
@section('page-title', 'Controller Pharmacy Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['pending_procurements'] > 0 ? 'border-warning' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Procurements</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_procurements'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-notepad text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['pending_requisitions'] > 0 ? 'border-info' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Requisitions</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_requisitions'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10 text-info">
                            <i class="ki-filled ki-basket text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['low_stock_items'] > 0 ? 'border-danger' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Low Stock Items</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['low_stock_items'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-danger/10 text-danger">
                            <i class="ki-filled ki-information text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['expiring_soon'] > 0 ? 'border-warning' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Expiring Soon (90 days)</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['expiring_soon'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-calendar text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <a href="{{ route('pharmacy.procurements.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-notepad"></i>
                        View Procurements
                    </a>
                    <a href="{{ route('pharmacy.requisitions.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-basket"></i>
                        View Requisitions
                    </a>
                    <a href="{{ route('pharmacy.stocks.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-package"></i>
                        Stock Overview
                    </a>
                    <a href="{{ route('pharmacy.reports.stock-balance') }}"
                       class="kt-btn kt-btn-primary w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-chart-line"></i>
                        Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Pending Procurements for Approval -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Procurements Awaiting Approval</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('pharmacy.procurements.index', ['status' => 'SUBMITTED']) }}" class="kt-btn kt-btn-sm kt-btn-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($pendingProcurements->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($pendingProcurements as $procurement)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $procurement->reference_number }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $procurement->items->count() }} items | Created by: {{ $procurement->createdBy->email ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('pharmacy.procurements.show', $procurement->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        Review
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No procurements pending approval.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Requisitions for Approval -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Requisitions Awaiting Approval</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('pharmacy.requisitions.index', ['status' => 'SUBMITTED']) }}" class="kt-btn kt-btn-sm kt-btn-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($pendingRequisitions->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($pendingRequisitions as $requisition)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $requisition->reference_number }} - {{ $requisition->command->name ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $requisition->items->count() }} items
                                        </span>
                                    </div>
                                    <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        Review
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No requisitions pending approval.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Low Stock Alerts -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Low Stock Alerts</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('pharmacy.stocks.index') }}" class="kt-btn kt-btn-sm kt-btn-light">
                            View Stock
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($lowStock->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($lowStock as $stock)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-danger/5 border border-danger/20">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $stock->drug->name ?? 'Unknown' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $stock->getLocationName() }}
                                        </span>
                                    </div>
                                    <span class="kt-badge kt-badge-danger kt-badge-sm">
                                        {{ $stock->quantity }} units
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">All stock levels are adequate.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Expiring Soon -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Expiring Soon (90 Days)</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('pharmacy.reports.expiry') }}" class="kt-btn kt-btn-sm kt-btn-light">
                            Full Report
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($expiringSoon->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($expiringSoon as $stock)
                                    @php
                                        $daysUntilExpiry = $stock->getDaysUntilExpiry();
                                        $warningLevel = $stock->getExpiryWarningLevel();
                                        $borderClass = match($warningLevel) {
                                            'expired' => 'border-danger/30 bg-danger/5',
                                            'critical' => 'border-danger/30 bg-danger/5',
                                            'warning' => 'border-warning/30 bg-warning/5',
                                            'caution' => 'border-info/30 bg-info/5',
                                            default => 'border-warning/20 bg-warning/5'
                                        };
                                        $badgeClass = match($warningLevel) {
                                            'expired' => 'kt-badge-danger',
                                            'critical' => 'kt-badge-danger',
                                            'warning' => 'kt-badge-warning',
                                            'caution' => 'kt-badge-info',
                                            default => 'kt-badge-warning'
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between p-3 rounded-lg {{ $borderClass }}">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $stock->drug->name ?? 'Unknown' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $stock->getLocationName() }} | Batch: {{ $stock->batch_number ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="kt-badge {{ $badgeClass }} kt-badge-sm">
                                        @if($daysUntilExpiry !== null && $daysUntilExpiry >= 0)
                                            @if($daysUntilExpiry === 0)
                                                Expires today!
                                            @elseif($daysUntilExpiry === 1)
                                                1 day left
                                            @else
                                                {{ $daysUntilExpiry }} days left
                                            @endif
                                        @else
                                            Expires: {{ $stock->expiry_date->format('d M Y') }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No items expiring in the next 90 days.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

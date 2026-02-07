@extends('layouts.app')

@section('title', 'Central Medical Store Dashboard')
@section('page-title', 'Central Medical Store Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['pending_receipt'] > 0 ? 'border-warning' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Receipt</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_receipt'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-delivery text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['pending_issue'] > 0 ? 'border-info' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pending Issue</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['pending_issue'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10 text-info">
                            <i class="ki-filled ki-exit-right text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Total Stock Items</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['total_stock_items'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10 text-success">
                            <i class="ki-filled ki-package text-2xl"></i>
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
                    <a href="{{ route('pharmacy.procurements.index', ['status' => 'APPROVED']) }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-delivery"></i>
                        Receive Procurements
                    </a>
                    <a href="{{ route('pharmacy.requisitions.index', ['status' => 'APPROVED']) }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-exit-right"></i>
                        Issue Requisitions
                    </a>
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'CENTRAL_STORE']) }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-package"></i>
                        View Stock
                    </a>
                    <a href="{{ route('pharmacy.drugs.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-pill"></i>
                        Drug Catalog
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Pending Receipt -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Procurements Awaiting Receipt</h3>
                </div>
                <div class="kt-card-content">
                    @if($pendingReceipt->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($pendingReceipt as $procurement)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $procurement->reference_number }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $procurement->items->count() }} items | Approved: {{ $procurement->approved_at?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('pharmacy.procurements.show', $procurement->id) }}" class="kt-btn kt-btn-sm kt-btn-success">
                                        Receive
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No procurements pending receipt.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Issue -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Requisitions Awaiting Issue</h3>
                </div>
                <div class="kt-card-content">
                    @if($pendingIssue->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($pendingIssue as $requisition)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $requisition->reference_number }} - {{ $requisition->command->name ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $requisition->items->count() }} items
                                        </span>
                                    </div>
                                    <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                        Issue
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                            <p class="text-secondary-foreground">No requisitions pending issue.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stock Overview -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Central Store Stock Overview</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'CENTRAL_STORE']) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All Stock
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($stockOverview->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Drug</th>
                                    <th>Quantity</th>
                                    <th>Batch</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockOverview as $stock)
                                    <tr>
                                        <td>{{ $stock->drug->name ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="{{ $stock->quantity < 10 ? 'text-danger font-semibold' : '' }}">
                                                {{ number_format($stock->quantity) }} {{ $stock->drug->unit_of_measure ?? 'units' }}
                                            </span>
                                        </td>
                                        <td>{{ $stock->batch_number ?? '-' }}</td>
                                        <td>
                                            @if($stock->expiry_date)
                                                @php
                                                    $daysUntilExpiry = $stock->getDaysUntilExpiry();
                                                    $warningLevel = $stock->getExpiryWarningLevel();
                                                    $expiryClass = match($warningLevel) {
                                                        'expired' => 'text-danger font-semibold',
                                                        'critical' => 'text-danger font-semibold',
                                                        'warning' => 'text-warning font-semibold',
                                                        'caution' => 'text-info font-semibold',
                                                        default => ''
                                                    };
                                                @endphp
                                                <div class="flex flex-col">
                                                    <span class="{{ $expiryClass }}">
                                                        {{ $stock->expiry_date->format('d M Y') }}
                                                    </span>
                                                    @if($daysUntilExpiry !== null && $daysUntilExpiry >= 0)
                                                        <span class="text-xs {{ $expiryClass }}">
                                                            @if($daysUntilExpiry === 0)
                                                                Expires today!
                                                            @elseif($daysUntilExpiry === 1)
                                                                1 day left
                                                            @else
                                                                {{ $daysUntilExpiry }} days left
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-secondary-foreground">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-cross-circle"></i> Expired
                                                </span>
                                            @elseif($stock->isExpiringVerySoon(30))
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Critical
                                                </span>
                                            @elseif($stock->isExpiringModerately(60))
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Warning
                                                </span>
                                            @elseif($stock->isExpiringSoon(90))
                                                <span class="kt-badge kt-badge-info kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Caution
                                                </span>
                                            @elseif($stock->quantity < 10)
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-arrow-down"></i> Low Stock
                                                </span>
                                            @else
                                                <span class="kt-badge kt-badge-success kt-badge-sm">
                                                    <i class="ki-filled ki-check-circle"></i> OK
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-package text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No stock records found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

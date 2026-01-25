@extends('layouts.app')

@section('title', 'Command Pharmacist Dashboard')
@section('page-title', 'Command Pharmacist Dashboard')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">{{ $commandName ?? 'Dashboard' }}</span>
@endsection

@section('content')
    @if(!$commandId)
        <div class="kt-alert kt-alert-danger mb-5">
            <i class="ki-filled ki-information"></i>
            <span>You are not assigned to any command. Please contact administration to assign you to a command.</span>
        </div>
    @endif
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Draft Requisitions</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['draft'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-notepad-edit text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Submitted</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['submitted'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10 text-info">
                            <i class="ki-filled ki-send text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5 {{ $stats['issued'] > 0 ? 'border-success' : '' }}">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Ready to Dispense</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['issued'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10 text-success">
                            <i class="ki-filled ki-pill text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Stock Items</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['command_stock_items'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10 text-primary">
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
                    <a href="{{ route('pharmacy.requisitions.create') }}"
                       class="kt-btn kt-btn-primary w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-plus"></i>
                        New Requisition
                    </a>
                    <a href="{{ route('pharmacy.requisitions.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-document"></i>
                        My Requisitions
                    </a>
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'COMMAND_PHARMACY', 'command_id' => $commandId]) }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-package"></i>
                        My Stock
                    </a>
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'CENTRAL_STORE']) }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-eye"></i>
                        Central Store Stock
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Ready to Dispense -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Ready to Dispense</h3>
                </div>
                <div class="kt-card-content">
                    @if($readyToDispense->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($readyToDispense as $requisition)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-success/5 border border-success/20">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $requisition->reference_number }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $requisition->items->count() }} items | Issued: {{ $requisition->issued_at?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-success">
                                        Dispense
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-pill text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No items ready to dispense.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Requisitions -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">My Recent Requisitions</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('pharmacy.requisitions.index') }}" class="kt-btn kt-btn-sm kt-btn-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    @if($myRequisitions->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($myRequisitions as $requisition)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $requisition->reference_number ?? 'DRAFT' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $requisition->items->count() }} items
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="kt-badge kt-badge-{{ $requisition->status === 'ISSUED' ? 'success' : ($requisition->status === 'REJECTED' ? 'danger' : ($requisition->status === 'DRAFT' ? 'warning' : 'info')) }} kt-badge-sm">
                                            {{ $requisition->status }}
                                        </span>
                                        <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No requisitions created yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Command Pharmacy Stock -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My Pharmacy Stock</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'COMMAND_PHARMACY', 'command_id' => $commandId]) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($commandStock->count() > 0)
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
                                @foreach($commandStock as $stock)
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
                                                <span class="{{ $stock->isExpired() ? 'text-danger' : ($stock->isExpiringSoon() ? 'text-warning' : '') }}">
                                                    {{ $stock->expiry_date->format('d M Y') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">Expired</span>
                                            @elseif($stock->isExpiringSoon())
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Expiring Soon</span>
                                            @elseif($stock->quantity < 10)
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">Low Stock</span>
                                            @else
                                                <span class="kt-badge kt-badge-success kt-badge-sm">OK</span>
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
                        <p class="text-secondary-foreground">No stock records found. Create a requisition to receive drugs.</p>
                        <a href="{{ route('pharmacy.requisitions.create') }}" class="kt-btn kt-btn-primary mt-4">
                            Create Requisition
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

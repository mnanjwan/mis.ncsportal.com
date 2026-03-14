@extends('layouts.app')

@section('title', 'Command Stock')
@section('page-title', 'Command Stock & Inventory')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.command-pharmacist.dashboard') }}" class="text-secondary-foreground hover:text-primary">Dashboard</a>
    <span>/</span>
    <span class="text-secondary-foreground">Inventory & Dispensing</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-alert kt-alert-success">
                <i class="ki-filled ki-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Summary -->
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-foreground">{{ strtoupper($commandName) }}</h2>
            <p class="text-sm text-secondary-foreground">Manage your current pharmacy inventory and dispense drugs from issued requisitions.</p>
        </div>

        <!-- Section: Command Inventory -->
        <div class="kt-card">
            <div class="kt-card-header border-b-0">
                <h3 class="kt-card-title flex items-center gap-2">
                    <i class="ki-filled ki-package text-primary"></i>
                    Current Pharmacy Inventory
                </h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('pharmacy.stocks.index', ['location_type' => 'COMMAND_PHARMACY', 'command_id' => $commandId]) }}" class="text-primary hover:text-primary/80 text-sm font-semibold">
                        View Full Stock History
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0">
                @if($commandStock->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr class="bg-muted/30">
                                    <th class="ps-5">Drug / Item</th>
                                    <th>Quantity</th>
                                    <th>Batch</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commandStock as $stock)
                                    <tr class="hover:bg-muted/20 transition-colors">
                                        <td class="ps-5">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-foreground">{{ $stock->drug->name ?? 'Unknown' }}</span>
                                                <span class="text-[10px] text-muted-foreground uppercase tracking-tight">{{ $stock->drug->unit_of_measure ?? 'Units' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-mono font-bold @if($stock->quantity < 10) text-danger @endif">
                                                {{ number_format($stock->quantity) }}
                                            </span>
                                        </td>
                                        <td class="text-muted-foreground text-sm">{{ $stock->batch_number ?? '-' }}</td>
                                        <td>
                                            @if($stock->expiry_date)
                                                <span class="text-xs {{ $stock->isExpired() || $stock->isExpiringVerySoon(30) ? 'text-danger font-bold' : '' }}">
                                                    {{ $stock->expiry_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted-foreground">-</span>
                                            @endif
                                        </td>
                                        <td class="pe-5">
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-outline kt-badge-sm">Expired</span>
                                            @elseif($stock->quantity < 10)
                                                <span class="kt-badge kt-badge-warning kt-badge-outline kt-badge-sm">Low Stock</span>
                                            @else
                                                <span class="kt-badge kt-badge-success kt-badge-outline kt-badge-sm">Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 border-t border-border">
                        <i class="ki-filled ki-package text-5xl text-muted-foreground/30 mb-4"></i>
                        <p class="text-secondary-foreground font-medium">Inventory is empty.</p>
                        <p class="text-sm text-muted-foreground mt-1">Submit a requisition to receive drugs from the Central Medical Store.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section: Ready to Dispense -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title flex items-center gap-2">
                    <i class="ki-filled ki-pill text-success"></i>
                    Ready to Dispense (Issued Requisitions)
                </h3>
                <p class="text-xs text-muted-foreground">Select a requisition to confirm dispensing to patients.</p>
            </div>
            <div class="kt-card-content">
                @if($requisitions->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr class="bg-muted/10">
                                    <th>Reference</th>
                                    <th>Drugs / items</th>
                                    <th>Items</th>
                                    <th>Issued on</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitions as $requisition)
                                    <tr class="hover:bg-muted/10 transition-colors">
                                        <td>
                                            <span class="font-bold text-mono text-primary">{{ $requisition->reference_number }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm text-foreground">
                                                @php
                                                    $drugNames = $requisition->items
                                                        ->map(fn ($i) => $i->drug->name ?? null)
                                                        ->filter()
                                                        ->unique()
                                                        ->values();
                                                @endphp
                                                {{ $drugNames->implode(', ') ?: '—' }}
                                            </span>
                                        </td>
                                        <td><span class="kt-badge kt-badge-sm kt-badge-light">{{ $requisition->items->count() }}</span></td>
                                        <td class="text-muted-foreground text-sm">{{ $requisition->issued_at ? $requisition->issued_at->format('d M Y') : '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}#dispense-form" class="kt-btn kt-btn-sm kt-btn-success shadow-sm">
                                                <i class="ki-filled ki-pill"></i> Dispense Now
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="size-16 rounded-full bg-muted/20 flex items-center justify-center mx-auto mb-4">
                            <i class="ki-filled ki-clipboard text-3xl text-muted-foreground"></i>
                        </div>
                        <p class="text-secondary-foreground font-medium">No pending requisitions to dispense.</p>
                        <p class="text-sm text-muted-foreground mt-1">Issued requisitions from Central Medical Store will appear here.</p>
                        <div class="flex items-center justify-center gap-3 mt-6">
                            <a href="{{ route('pharmacy.command-pharmacist.dashboard') }}" class="kt-btn kt-btn-light kt-btn-sm">
                                <i class="ki-filled ki-home-3"></i> Home
                            </a>
                            <a href="{{ route('pharmacy.requisitions.index') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                                View My Requisitions
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

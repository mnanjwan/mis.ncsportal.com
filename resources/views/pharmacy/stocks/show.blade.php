@extends('layouts.app')

@section('title', $drug->name . ' - Stock Details')
@section('page-title', $drug->name)
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.stocks.index') }}" class="text-secondary-foreground hover:text-primary">Stock</a>
    <span>/</span>
    <span class="text-secondary-foreground">{{ $drug->name }}</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-alert kt-alert-success">
                <i class="ki-filled ki-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="kt-alert kt-alert-danger">
                <i class="ki-filled ki-information"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Drug Info -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-mono">{{ $drug->name }}</h2>
                        <p class="text-sm text-secondary-foreground mt-1">
                            Category: {{ $drug->category ?? 'N/A' }} | Unit: {{ $drug->unit_of_measure }}
                        </p>
                        @if($drug->description)
                            <p class="text-sm text-secondary-foreground mt-2">{{ $drug->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <span class="kt-badge kt-badge-{{ $drug->is_active ? 'success' : 'danger' }} kt-badge-lg">
                            {{ $drug->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Stock Summary -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Stock Summary</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="p-4 rounded-lg bg-primary/10 border border-primary/20">
                            <div class="text-sm text-secondary-foreground">Central Store</div>
                            <div class="text-2xl font-semibold text-primary">
                                {{ number_format($centralStock->sum('quantity')) }} {{ $drug->unit_of_measure }}
                            </div>
                        </div>
                        <div class="p-4 rounded-lg bg-info/10 border border-info/20">
                            <div class="text-sm text-secondary-foreground">Command Pharmacies</div>
                            <div class="text-2xl font-semibold text-info">
                                {{ number_format($commandStocks->sum('quantity')) }} {{ $drug->unit_of_measure }}
                            </div>
                        </div>
                        <div class="p-4 rounded-lg bg-success/10 border border-success/20">
                            <div class="text-sm text-secondary-foreground">Total Stock</div>
                            <div class="text-2xl font-semibold text-success">
                                {{ number_format($centralStock->sum('quantity') + $commandStocks->sum('quantity')) }} {{ $drug->unit_of_measure }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Central Store Stock -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Central Store Stock</h3>
                </div>
                <div class="kt-card-content">
                    @if($centralStock->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($centralStock as $stock)
                                <div class="p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="text-sm font-medium">Batch: {{ $stock->batch_number ?? 'N/A' }}</div>
                                            <div class="text-xs text-secondary-foreground">
                                                Expiry: {{ $stock->expiry_date ? $stock->expiry_date->format('d M Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold {{ $stock->quantity < 10 ? 'text-danger' : '' }}">
                                                {{ number_format($stock->quantity) }}
                                            </div>
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-xs">Expired</span>
                                            @elseif($stock->isExpiringSoon())
                                                <span class="kt-badge kt-badge-warning kt-badge-xs">Expiring</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-secondary-foreground">No stock at Central Store</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Command Pharmacies Stock -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Command Pharmacies Stock</h3>
                </div>
                <div class="kt-card-content">
                    @if($commandStocks->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($commandStocks as $stock)
                                <div class="p-3 rounded-lg bg-muted/50 border border-input">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="text-sm font-medium">{{ $stock->command->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-secondary-foreground">
                                                Batch: {{ $stock->batch_number ?? 'N/A' }} | 
                                                Expiry: {{ $stock->expiry_date ? $stock->expiry_date->format('d M Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold {{ $stock->quantity < 10 ? 'text-danger' : '' }}">
                                                {{ number_format($stock->quantity) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-secondary-foreground">No stock at Command Pharmacies</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stock Movements History -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Stock Movements</h3>
            </div>
            <div class="kt-card-content">
                @if($movements->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Quantity</th>
                                    <th>Batch</th>
                                    <th>By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="kt-badge kt-badge-{{ $movement->isAddition() ? 'success' : 'warning' }} kt-badge-sm">
                                                {{ $movement->getMovementTypeLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $movement->getLocationName() }}</td>
                                        <td>
                                            <span class="{{ $movement->isAddition() ? 'text-success' : 'text-warning' }} font-semibold">
                                                {{ $movement->isAddition() ? '+' : '' }}{{ number_format($movement->quantity) }}
                                            </span>
                                        </td>
                                        <td>{{ $movement->batch_number ?? '-' }}</td>
                                        <td>{{ $movement->createdBy->officer->full_name ?? $movement->createdBy->email ?? 'N/A' }}</td>
                                        <td class="max-w-xs truncate">{{ $movement->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-secondary-foreground">No stock movements recorded.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

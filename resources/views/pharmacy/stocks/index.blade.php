@extends('layouts.app')

@section('title', 'Pharmacy Stock')
@section('page-title', 'Pharmacy Stock')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Stock</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="kt-label text-xs">Location Type</label>
                        <select name="location_type" class="kt-input kt-input-sm" onchange="this.form.submit()">
                            <option value="CENTRAL_STORE" {{ $locationType === 'CENTRAL_STORE' ? 'selected' : '' }}>Central Medical Store</option>
                            <option value="COMMAND_PHARMACY" {{ $locationType === 'COMMAND_PHARMACY' ? 'selected' : '' }}>Command Pharmacy</option>
                        </select>
                    </div>
                    @if($locationType === 'COMMAND_PHARMACY' && $commands->count() > 0)
                        <div>
                            <label class="kt-label text-xs">Command</label>
                            <select name="command_id" class="kt-input kt-input-sm" onchange="this.form.submit()">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ $commandId == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex-grow">
                        <label class="kt-label text-xs">Search Drug</label>
                        <input type="text" name="search" class="kt-input kt-input-sm" 
                               value="{{ $search }}" placeholder="Search by drug name...">
                    </div>
                    <div>
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    {{ $locationType === 'CENTRAL_STORE' ? 'Central Medical Store Stock' : 'Command Pharmacy Stock' }}
                </h3>
                <div class="kt-card-toolbar">
                    @if(auth()->user()->hasRole('OC Pharmacy'))
                        <a href="{{ route('pharmacy.reports.stock-balance') }}" class="kt-btn kt-btn-sm kt-btn-light">
                            <i class="ki-filled ki-chart-line"></i> Reports
                        </a>
                    @endif
                </div>
            </div>
            <div class="kt-card-content">
                @if($stocks->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Drug</th>
                                    <th>Category</th>
                                    @if($locationType === 'COMMAND_PHARMACY')
                                        <th>Command</th>
                                    @endif
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Batch</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr>
                                        <td class="font-medium">{{ $stock->drug->name ?? 'Unknown' }}</td>
                                        <td>{{ $stock->drug->category ?? '-' }}</td>
                                        @if($locationType === 'COMMAND_PHARMACY')
                                            <td>{{ $stock->command->name ?? '-' }}</td>
                                        @endif
                                        <td>
                                            <span class="{{ $stock->quantity < 10 ? 'text-danger font-semibold' : '' }}">
                                                {{ number_format($stock->quantity) }}
                                            </span>
                                        </td>
                                        <td>{{ $stock->drug->unit_of_measure ?? 'units' }}</td>
                                        <td>{{ $stock->batch_number ?? '-' }}</td>
                                        <td>
                                            @if($stock->expiry_date)
                                                <span class="{{ $stock->isExpired() ? 'text-danger font-semibold' : ($stock->isExpiringSoon() ? 'text-warning font-semibold' : '') }}">
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
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Expiring</span>
                                            @elseif($stock->quantity < 10)
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">Low</span>
                                            @else
                                                <span class="kt-badge kt-badge-success kt-badge-sm">OK</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('pharmacy.stocks.show', $stock->pharmacy_drug_id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                                <i class="ki-filled ki-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $stocks->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-package text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No stock records found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

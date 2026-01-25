@extends('layouts.app')

@section('title', 'Custom Report')
@section('page-title', 'Custom Pharmacy Report')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Reports</span>
    <span>/</span>
    <span class="text-secondary-foreground">Custom</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Search & Filter</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" class="grid gap-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="kt-label">Report Type</label>
                            <select name="report_type" class="kt-input" id="reportType">
                                <option value="stock" {{ $reportType === 'stock' ? 'selected' : '' }}>Stock Report</option>
                                <option value="movements" {{ $reportType === 'movements' ? 'selected' : '' }}>Stock Movements</option>
                            </select>
                        </div>
                        <div>
                            <label class="kt-label">Search Drug Name</label>
                            <input type="text" name="search" class="kt-input" value="{{ $search }}" placeholder="Enter drug name...">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="kt-label">Category</label>
                            <select name="category" class="kt-input">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="kt-label">Location Type</label>
                            <select name="location_type" class="kt-input">
                                <option value="">All Locations</option>
                                <option value="CENTRAL_STORE" {{ $locationType === 'CENTRAL_STORE' ? 'selected' : '' }}>Central Store</option>
                                <option value="COMMAND_PHARMACY" {{ $locationType === 'COMMAND_PHARMACY' ? 'selected' : '' }}>Command Pharmacy</option>
                            </select>
                        </div>
                        <div>
                            <label class="kt-label">Command</label>
                            <select name="command_id" class="kt-input">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ $commandId == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Stock-specific filters -->
                    <div class="stock-filters {{ $reportType === 'movements' ? 'hidden' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="kt-label">Min Quantity</label>
                                <input type="number" name="min_quantity" class="kt-input" value="{{ $minQuantity }}" placeholder="0">
                            </div>
                            <div>
                                <label class="kt-label">Max Quantity</label>
                                <input type="number" name="max_quantity" class="kt-input" value="{{ $maxQuantity }}" placeholder="999999">
                            </div>
                            <div>
                                <label class="kt-label">Expiry From</label>
                                <input type="date" name="expiry_from" class="kt-input" value="{{ $expiryFrom }}">
                            </div>
                            <div>
                                <label class="kt-label">Expiry To</label>
                                <input type="date" name="expiry_to" class="kt-input" value="{{ $expiryTo }}">
                            </div>
                        </div>
                    </div>

                    <!-- Movements-specific filters -->
                    <div class="movements-filters {{ $reportType === 'stock' ? 'hidden' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="kt-label">Movement Type</label>
                                <select name="movement_type" class="kt-input">
                                    <option value="">All Types</option>
                                    @foreach($movementTypes as $type)
                                        <option value="{{ $type }}" {{ $movementType === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', $type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="kt-label">Date From</label>
                                <input type="date" name="date_from" class="kt-input" value="{{ $dateFrom }}">
                            </div>
                            <div>
                                <label class="kt-label">Date To</label>
                                <input type="date" name="date_to" class="kt-input" value="{{ $dateTo }}">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Generate Report
                        </button>
                        <a href="{{ route('pharmacy.reports.custom.print', request()->query()) }}" 
                           target="_blank" 
                           class="kt-btn kt-btn-success">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        @if($reportType === 'stock' && $results->count() > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Stock Results ({{ $results->total() }} records)</h3>
                </div>
                <div class="kt-card-content">
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Drug</th>
                                    <th>Location</th>
                                    <th>Quantity</th>
                                    <th>Batch</th>
                                    <th>Expiry</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $index => $stock)
                                    <tr>
                                        <td>{{ $results->firstItem() + $index }}</td>
                                        <td class="font-medium">{{ $stock->drug->name ?? 'Unknown' }}</td>
                                        <td>{{ $stock->getLocationName() }}</td>
                                        <td>{{ number_format($stock->quantity) }} {{ $stock->drug->unit_of_measure ?? '' }}</td>
                                        <td>{{ $stock->batch_number ?? '-' }}</td>
                                        <td>{{ $stock->expiry_date ? $stock->expiry_date->format('d M Y') : '-' }}</td>
                                        <td>{{ $stock->updated_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $results->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @elseif($reportType === 'movements' && $movements->count() > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Movement Results ({{ $movements->total() }} records)</h3>
                </div>
                <div class="kt-card-content">
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Drug</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Quantity</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $index => $movement)
                                    <tr>
                                        <td>{{ $movements->firstItem() + $index }}</td>
                                        <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                                        <td class="font-medium">{{ $movement->drug->name ?? 'Unknown' }}</td>
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
                                        <td>{{ $movement->createdBy->officer->full_name ?? $movement->createdBy->email ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $movements->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="text-center py-12">
                        <i class="ki-filled ki-search-list text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No results found. Adjust your filters and try again.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('reportType');
    const stockFilters = document.querySelector('.stock-filters');
    const movementsFilters = document.querySelector('.movements-filters');

    reportType.addEventListener('change', function() {
        if (this.value === 'stock') {
            stockFilters.classList.remove('hidden');
            movementsFilters.classList.add('hidden');
        } else {
            stockFilters.classList.add('hidden');
            movementsFilters.classList.remove('hidden');
        }
    });
});
</script>
@endpush

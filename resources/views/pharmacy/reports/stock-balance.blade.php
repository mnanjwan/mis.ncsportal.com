@extends('layouts.app')

@section('title', 'Stock Balance Report')
@section('page-title', 'Stock Balance Report')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Reports</span>
    <span>/</span>
    <span class="text-secondary-foreground">Stock Balance</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="kt-label text-xs">Location Type</label>
                        <select name="location_type" class="kt-input kt-input-sm">
                            <option value="">All Locations</option>
                            <option value="CENTRAL_STORE" {{ $locationType === 'CENTRAL_STORE' ? 'selected' : '' }}>Central Store Only</option>
                            <option value="COMMAND_PHARMACY" {{ $locationType === 'COMMAND_PHARMACY' ? 'selected' : '' }}>Command Pharmacies Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="kt-label text-xs">Command</label>
                        <select name="command_id" class="kt-input kt-input-sm">
                            <option value="">All Commands</option>
                            @foreach($commands as $command)
                                <option value="{{ $command->id }}" {{ $commandId == $command->id ? 'selected' : '' }}>
                                    {{ $command->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('pharmacy.reports.stock-balance.print', request()->query()) }}" 
                           target="_blank" 
                           class="kt-btn kt-btn-sm kt-btn-success">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Stock Balance Summary</h3>
            </div>
            <div class="kt-card-content">
                @if($summary->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Drug Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th class="text-center">Central Store</th>
                                    <th class="text-center">Command Pharmacies</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-medium">{{ $item['drug']->name ?? 'Unknown' }}</td>
                                        <td>{{ $item['drug']->category ?? '-' }}</td>
                                        <td>{{ $item['drug']->unit_of_measure ?? 'units' }}</td>
                                        <td class="text-center">
                                            <span class="{{ $item['central_store'] < 10 ? 'text-danger font-semibold' : '' }}">
                                                {{ number_format($item['central_store']) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ number_format($item['command_pharmacies']) }}</td>
                                        <td class="text-center font-semibold">{{ number_format($item['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-muted/50">
                                    <th colspan="4" class="text-right">Totals:</th>
                                    <th class="text-center">{{ number_format($summary->sum('central_store')) }}</th>
                                    <th class="text-center">{{ number_format($summary->sum('command_pharmacies')) }}</th>
                                    <th class="text-center">{{ number_format($summary->sum('total')) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-package text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No stock data available.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Links -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Other Reports</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('pharmacy.reports.expiry') }}" class="kt-btn kt-btn-outline w-full">
                        <i class="ki-filled ki-calendar"></i> Expiry Report
                    </a>
                    <a href="{{ route('pharmacy.reports.custom') }}" class="kt-btn kt-btn-outline w-full">
                        <i class="ki-filled ki-search-list"></i> Custom Report
                    </a>
                    <a href="{{ route('pharmacy.stocks.index') }}" class="kt-btn kt-btn-outline w-full">
                        <i class="ki-filled ki-package"></i> View Stock
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

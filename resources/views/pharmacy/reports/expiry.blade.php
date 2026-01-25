@extends('layouts.app')

@section('title', 'Expiry Report')
@section('page-title', 'Drug Expiry Report')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Reports</span>
    <span>/</span>
    <span class="text-secondary-foreground">Expiry</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="kt-label text-xs">Days Until Expiry</label>
                        <select name="days" class="kt-input kt-input-sm">
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Days</option>
                            <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 Days</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Days</option>
                            <option value="180" {{ $days == 180 ? 'selected' : '' }}>180 Days</option>
                            <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="kt-label text-xs">Location Type</label>
                        <select name="location_type" class="kt-input kt-input-sm">
                            <option value="">All Locations</option>
                            <option value="CENTRAL_STORE" {{ $locationType === 'CENTRAL_STORE' ? 'selected' : '' }}>Central Store</option>
                            <option value="COMMAND_PHARMACY" {{ $locationType === 'COMMAND_PHARMACY' ? 'selected' : '' }}>Command Pharmacies</option>
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
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_expired" value="1" {{ $includeExpired ? 'checked' : '' }} class="kt-checkbox">
                            <span class="text-sm">Include Expired</span>
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('pharmacy.reports.expiry.print', request()->query()) }}" 
                           target="_blank" 
                           class="kt-btn kt-btn-sm kt-btn-success">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card border-danger">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-secondary-foreground">Expired Items</div>
                            <div class="text-2xl font-semibold text-danger">{{ $expired->count() }}</div>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-danger/10 text-danger">
                            <i class="ki-filled ki-information-3 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-card border-warning">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-secondary-foreground">Expiring Within {{ $days }} Days</div>
                            <div class="text-2xl font-semibold text-warning">{{ $expiringSoon->count() }}</div>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-calendar text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Items by Expiry Date</h3>
            </div>
            <div class="kt-card-content">
                @if($stocks->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Drug Name</th>
                                    <th>Location</th>
                                    <th>Batch</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $index => $stock)
                                    <tr class="{{ $stock->isExpired() ? 'bg-danger/5' : ($stock->isExpiringSoon(30) ? 'bg-warning/5' : '') }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-medium">{{ $stock->drug->name ?? 'Unknown' }}</td>
                                        <td>{{ $stock->getLocationName() }}</td>
                                        <td>{{ $stock->batch_number ?? '-' }}</td>
                                        <td>{{ number_format($stock->quantity) }} {{ $stock->drug->unit_of_measure ?? '' }}</td>
                                        <td>
                                            <span class="{{ $stock->isExpired() ? 'text-danger font-semibold' : ($stock->isExpiringSoon(30) ? 'text-warning font-semibold' : '') }}">
                                                {{ $stock->expiry_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $daysLeft = $stock->getDaysUntilExpiry();
                                            @endphp
                                            @if($daysLeft !== null)
                                                @if($daysLeft < 0)
                                                    <span class="text-danger font-semibold">{{ abs($daysLeft) }} days ago</span>
                                                @elseif($daysLeft === 0)
                                                    <span class="text-danger font-semibold">Today</span>
                                                @else
                                                    <span class="{{ $daysLeft <= 30 ? 'text-warning font-semibold' : '' }}">{{ $daysLeft }} days</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">EXPIRED</span>
                                            @elseif($stock->isExpiringSoon(30))
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">CRITICAL</span>
                                            @elseif($stock->isExpiringSoon(90))
                                                <span class="kt-badge kt-badge-info kt-badge-sm">SOON</span>
                                            @else
                                                <span class="kt-badge kt-badge-light kt-badge-sm">OK</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-check-circle text-5xl text-success mb-4"></i>
                        <p class="text-secondary-foreground">No items expiring within the selected timeframe.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

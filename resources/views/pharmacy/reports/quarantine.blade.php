@extends('layouts.app')

@section('title', 'Quarantine Report')
@section('page-title', 'Expired / Quarantine Stock Report')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Reports</span>
    <span>/</span>
    <span class="text-secondary-foreground">Quarantine</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="kt-label text-xs">Status</label>
                        <select name="status" class="kt-input kt-input-sm">
                            <option value="">All Statuses</option>
                            <option value="QUARANTINED" {{ $status === 'QUARANTINED' ? 'selected' : '' }}>Quarantined</option>
                            <option value="DESTROYED" {{ $status === 'DESTROYED' ? 'selected' : '' }}>Destroyed</option>
                            <option value="NAFDAC" {{ $status === 'NAFDAC' ? 'selected' : '' }}>Given to NAFDAC</option>
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
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                    </div>
                    <div class="ml-auto">
                        <a href="{{ route('pharmacy.reports.quarantine.print', request()->query()) }}" 
                           target="_blank" 
                           class="kt-btn kt-btn-sm kt-btn-success">
                            <i class="ki-filled ki-printer"></i> Print Report
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quarantined / Expired Items</h3>
            </div>
            <div class="kt-card-content">
                @if($records->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Drug / Item Name</th>
                                    <th>Location</th>
                                    <th>Batch</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Notes / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $index => $record)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="font-medium">{{ $record->drug->name ?? 'Unknown' }}</td>
                                        <td>
                                            @if($record->location_type === 'CENTRAL_STORE')
                                                Central Store
                                            @else
                                                {{ $record->command->name ?? 'Command' }}
                                            @endif
                                        </td>
                                        <td><code>{{ $record->batch_number ?? '-' }}</code></td>
                                        <td>{{ number_format($record->quantity) }} {{ $record->drug->unit_of_measure ?? '' }}</td>
                                        <td>
                                            <span class="text-danger font-semibold">
                                                {{ $record->expiry_date->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($record->status === 'QUARANTINED')
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">QUARANTINED</span>
                                            @elseif($record->status === 'DESTROYED')
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">DESTROYED</span>
                                            @elseif($record->status === 'NAFDAC')
                                                <span class="kt-badge kt-badge-info kt-badge-sm">NAFDAC</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->action_notes)
                                                <div class="text-xs text-secondary-foreground">{{ $record->action_notes }}</div>
                                                <div class="text-[10px] text-gray-500">By: {{ $record->actedBy->officer->full_name ?? 'System' }}</div>
                                            @else
                                                <span class="text-gray-500 text-xs">No notes</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information text-5xl text-info mb-4"></i>
                        <p class="text-secondary-foreground">No quarantined items found matching your criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

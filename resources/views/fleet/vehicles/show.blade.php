@extends('layouts.app')

@section('title', 'Fleet Vehicle')
@section('page-title', 'Fleet Vehicle')

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header flex items-center justify-between">
                <h3 class="kt-card-title">Vehicle Details</h3>
                <div class="flex gap-2">
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.edit-identifiers', $vehicle) }}">
                        Edit Reg/Engine
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Make/Model:</strong> {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '-' }}</div>
                    <div><strong>Type:</strong> {{ $vehicle->vehicle_type }}</div>
                    <div><strong>Reg No:</strong> {{ $vehicle->reg_no ?? '-' }}</div>
                    <div><strong>Engine No:</strong> {{ $vehicle->engine_number ?? '-' }}</div>
                    <div><strong>Chassis No:</strong> {{ $vehicle->chassis_number }}</div>
                    <div><strong>Year:</strong> {{ $vehicle->year_of_manufacture ?? '-' }}</div>
                    <div><strong>Service Status:</strong> {{ $vehicle->service_status }}</div>
                    <div><strong>Lifecycle:</strong> {{ $vehicle->lifecycle_status }}</div>
                    <div><strong>Command:</strong> {{ $vehicle->currentCommand->name ?? '-' }}</div>
                    <div><strong>Officer:</strong> {{ $vehicle->currentOfficer->full_name ?? '-' }}</div>
                </div>

                <div class="flex flex-wrap gap-2 mt-5">
                    @if(auth()->user()->hasRole('CD') && (int) $vehicle->current_command_id === (int) (auth()->user()->roles()->where('name','CD')->wherePivot('is_active', true)->first()?->pivot?->command_id))
                        <form method="POST" action="{{ route('fleet.vehicles.service-status', $vehicle) }}" class="flex gap-2 items-center">
                            @csrf
                            <select name="service_status" class="kt-select kt-select-sm">
                                <option value="SERVICEABLE" @selected($vehicle->service_status === 'SERVICEABLE')>Serviceable</option>
                                <option value="UNSERVICEABLE" @selected($vehicle->service_status === 'UNSERVICEABLE')>Unserviceable</option>
                            </select>
                            <button class="kt-btn kt-btn-sm" type="submit">Update Status</button>
                        </form>
                    @endif

                    @if(auth()->user()->hasRole('CD') && $vehicle->lifecycle_status === 'AT_COMMAND_POOL' && !$vehicle->reserved_fleet_request_id)
                        <a class="kt-btn kt-btn-primary" href="{{ route('fleet.vehicles.issue', $vehicle) }}">Issue to Officer</a>
                    @endif

                    @php
                        $isOfficerReturningOwn = auth()->user()->hasRole('Officer')
                            && auth()->user()->officer
                            && (int) $vehicle->current_officer_id === (int) auth()->user()->officer->id;
                        $isCdReturn = auth()->user()->hasRole('CD') && $vehicle->lifecycle_status === 'IN_OFFICER_CUSTODY';
                    @endphp

                    @if($isOfficerReturningOwn || $isCdReturn)
                        <form method="POST" action="{{ route('fleet.vehicles.return', $vehicle) }}">
                            @csrf
                            <button class="kt-btn kt-btn-warning" type="submit">Return Vehicle</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @php
            $latestCommandAssignment = $vehicle->assignments
                ->whereNotNull('assigned_to_command_id')
                ->sortByDesc('assigned_at')
                ->first();
            $canReceive = auth()->user()->hasRole('Area Controller')
                && $latestCommandAssignment
                && empty($latestCommandAssignment->received_at);
        @endphp

        @if($canReceive)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Unit Head Receipt (Area Controller)</h3>
                </div>
                <div class="kt-card-content p-5 lg:p-7.5">
                    <form method="POST" action="{{ route('fleet.assignments.receive', $latestCommandAssignment) }}">
                        @csrf
                        <button class="kt-btn kt-btn-primary" type="submit">Mark Received</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Reg/Engine Change History</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($vehicle->audits->isEmpty())
                    <p class="text-sm text-secondary-foreground">No changes recorded yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">When</th>
                                    <th class="text-left">Field</th>
                                    <th class="text-left">Old</th>
                                    <th class="text-left">New</th>
                                    <th class="text-left">By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicle->audits->sortByDesc('changed_at') as $a)
                                    <tr>
                                        <td>{{ $a->changed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $a->field_name }}</td>
                                        <td>{{ $a->old_value ?? '-' }}</td>
                                        <td>{{ $a->new_value ?? '-' }}</td>
                                        <td>{{ $a->changedBy->email ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Nominal Roll - Vehicle Allocation Report')
@section('page-title', 'Nominal Roll - Vehicle Allocation Report')

@section('breadcrumbs')
    @php
        $user = auth()->user();
        $dashboardRoute = null;
        if ($user->hasRole('CD')) {
            $dashboardRoute = route('fleet.cd.dashboard');
        } elseif ($user->hasRole('O/C T&L')) {
            $dashboardRoute = route('fleet.oc-tl.dashboard');
        } elseif ($user->hasRole('Transport Store/Receiver')) {
            $dashboardRoute = route('fleet.store-receiver.dashboard');
        } elseif ($user->hasRole('CC T&L')) {
            $dashboardRoute = route('fleet.cc-tl.dashboard');
        } elseif ($user->hasRole('DCG FATS')) {
            $dashboardRoute = route('fleet.dcg-fats.dashboard');
        } elseif ($user->hasRole('ACG TS')) {
            $dashboardRoute = route('fleet.acg-ts.dashboard');
        }
    @endphp
    @if($dashboardRoute)
        <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">Fleet</a>
        <span>/</span>
    @endif
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.reports.returns') }}">Reports</a>
    <span>/</span>
    <span class="text-primary">Returns</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter</h3>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                <form method="GET" action="{{ route('fleet.reports.returns') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="text-sm font-medium">Start Date</label>
                        <input class="kt-input w-full" type="date" name="start_date" value="{{ $startDate }}" />
                    </div>
                    <div>
                        <label class="text-sm font-medium">End Date</label>
                        <input class="kt-input w-full" type="date" name="end_date" value="{{ $endDate }}" />
                    </div>
                    <div>
                        <button class="kt-btn kt-btn-primary w-full" type="submit">Apply</button>
                    </div>
                </form>
            </div>
        </div>  

        <div class="kt-card">
            <div class="kt-card-header flex items-center justify-between">
                <h3 class="kt-card-title">Nominal Roll (Vehicle Allocation Report)</h3>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-printer"></i>
                        Print
                    </button>
                </div>
            </div>
            <div class="kt-card-content p-5 lg:p-7.5">
                @if($rows->isEmpty())
                    <p class="text-sm text-secondary-foreground">No vehicle allocations found for selected date range.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">S/N</th>
                                    <th class="text-left">Reg No.</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-left">Make/Model</th>
                                    <th class="text-left">Chassis No.</th>
                                    <th class="text-left">Engine No.</th>
                                    <th class="text-left">Officer Name</th>
                                    <th class="text-left">Service No.</th>
                                    <th class="text-left">Date of Allocation</th>
                                    <th class="text-left">Service Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row->vehicle->reg_no ?? '-' }}</td>
                                        <td>{{ $row->vehicle->vehicle_type ?? '-' }}</td>
                                        <td>{{ trim(($row->vehicle->make ?? '') . ' ' . ($row->vehicle->model ?? '')) ?: '-' }}</td>
                                        <td>{{ $row->vehicle->chassis_number ?? '-' }}</td>
                                        <td>{{ $row->vehicle->engine_number ?? '-' }}</td>
                                        <td>
                                            @if($row->assignedToOfficer)
                                                {{ $row->assignedToOfficer->full_name ?? ($row->assignedToOfficer->surname . ' ' . ($row->assignedToOfficer->first_name ?? '') . ' ' . ($row->assignedToOfficer->middle_name ?? '')) }}
                                            @elseif($row->assigned_to_command_id)
                                                <span class="text-muted-foreground italic">Command Pool</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->assignedToOfficer)
                                                {{ $row->assignedToOfficer->service_number ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $row->assigned_at?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <span class="px-2 py-0.5 rounded text-xs {{ $row->vehicle->service_status === 'SERVICEABLE' ? 'bg-green-100 text-green-800' : ($row->vehicle->service_status === 'UNSERVICEABLE' ? 'bg-red-100 text-red-800' : 'bg-muted') }}">
                                                {{ $row->vehicle->service_status ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-sm text-secondary-foreground">
                        <p><strong>Total Records:</strong> {{ $rows->count() }}</p>
                        <p><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


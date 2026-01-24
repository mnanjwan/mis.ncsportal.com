@extends('layouts.app')

@section('title', 'Fleet Vehicles')
@section('page-title', 'Fleet Vehicles')

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
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.index') }}">Vehicles</a>
    <span>/</span>
    <span class="text-primary">List</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="kt-card-title">Vehicles</h3>
                @if(auth()->user()->hasRole('Transport Store/Receiver'))
                    <a class="kt-btn kt-btn-primary" href="{{ route('fleet.vehicles.intake.create') }}">
                        <i class="ki-filled ki-plus"></i>
                        Receive Vehicle
                    </a>
                @endif
            </div>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            @if($vehicles->isEmpty())
                <p class="text-sm text-secondary-foreground">No vehicles found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr>
                                <th class="text-left">S/N</th>
                                <th class="text-left">Reg No</th>
                                <th class="text-left">Type</th>
                                <th class="text-left">Make/Model</th>
                                <th class="text-left">Chassis No</th>
                                <th class="text-left">Engine No</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $i => $v)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $v->reg_no ?? '-' }}</td>
                                    <td>{{ $v->vehicle_type }}</td>
                                    <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '-' }}</td>
                                    <td>{{ $v->chassis_number }}</td>
                                    <td>{{ $v->engine_number ?? '-' }}</td>
                                    <td>{{ $v->service_status }}</td>
                                    <td>
                                        <a class="kt-btn kt-btn-sm" href="{{ route('fleet.vehicles.show', $v) }}">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection


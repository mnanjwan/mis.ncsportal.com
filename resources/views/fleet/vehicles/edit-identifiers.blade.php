@extends('layouts.app')

@section('title', 'Edit Vehicle Identifiers')
@section('page-title', 'Edit Vehicle Identifiers')

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
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.show', $vehicle) }}">View</a>
    <span>/</span>
    <span class="text-primary">Edit Identifiers</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Update Reg No / Engine No</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.vehicles.identifiers.update', $vehicle) }}" class="grid gap-4 max-w-xl">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-sm font-medium">Reg No</label>
                    <input class="kt-input w-full" name="reg_no" value="{{ old('reg_no', $vehicle->reg_no) }}" />
                    @error('reg_no')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Engine No</label>
                    <input class="kt-input w-full" name="engine_number" value="{{ old('engine_number', $vehicle->engine_number) }}" />
                    @error('engine_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button class="kt-btn kt-btn-primary" type="submit">Save</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.show', $vehicle) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


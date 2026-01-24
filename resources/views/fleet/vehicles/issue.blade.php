@extends('layouts.app')

@section('title', 'Issue Vehicle')
@section('page-title', 'Issue Vehicle')

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
    <span class="text-primary">Issue to Officer</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Issue Vehicle to Officer</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="text-sm text-secondary-foreground mb-4">
                <strong>Vehicle:</strong>
                {{ $vehicle->vehicle_type }} — {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '-' }}
                (Chassis: {{ $vehicle->chassis_number }})
            </div>

            <form method="POST" action="{{ route('fleet.vehicles.issue.store', $vehicle) }}" class="grid gap-4 max-w-2xl">
                @csrf

                <div>
                    <label class="text-sm font-medium">Officer</label>
                    <select class="kt-select w-full" name="officer_id" required>
                        <option value="">Select officer</option>
                        @foreach($officers as $o)
                            <option value="{{ $o->id }}" @selected(old('officer_id') == $o->id)>
                                {{ $o->full_name }} ({{ $o->service_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('officer_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Notes (optional)</label>
                    <textarea class="kt-textarea w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button class="kt-btn kt-btn-primary" type="submit">Issue Vehicle</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.show', $vehicle) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'New Fleet Request')
@section('page-title', 'New Fleet Request')

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
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.requests.index') }}">Requests</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">CD Requisition</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.requests.store') }}" class="grid gap-4 max-w-2xl">
                @csrf

                <div>
                    <label class="text-sm font-medium">Vehicle Type</label>
                    <select name="requested_vehicle_type" class="kt-select w-full" required>
                        <option value="">Select type</option>
                        <option value="SALOON" @selected(old('requested_vehicle_type')==='SALOON')>Saloon</option>
                        <option value="SUV" @selected(old('requested_vehicle_type')==='SUV')>SUV</option>
                        <option value="BUS" @selected(old('requested_vehicle_type')==='BUS')>Bus</option>
                    </select>
                    @error('requested_vehicle_type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Make (optional)</label>
                        <input type="text" name="requested_make" class="kt-input w-full" value="{{ old('requested_make') }}" />
                        @error('requested_make')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Model (optional)</label>
                        <input type="text" name="requested_model" class="kt-input w-full" value="{{ old('requested_model') }}" />
                        @error('requested_model')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Year (optional)</label>
                        <input type="number" name="requested_year" class="kt-input w-full" value="{{ old('requested_year') }}" />
                        @error('requested_year')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Quantity</label>
                        <input type="number" name="requested_quantity" class="kt-input w-full" min="1" max="1000" value="{{ old('requested_quantity', 1) }}" required />
                        @error('requested_quantity')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="kt-btn kt-btn-primary" type="submit">Save Draft</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.requests.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection


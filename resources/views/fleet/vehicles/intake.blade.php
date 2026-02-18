@extends('layouts.app')

@section('title', 'Receive Vehicle')
@section('page-title', 'Receive Vehicle')

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
    <span class="text-primary">Receive Vehicle</span>
@endsection

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Store/Transport Vehicle Intake</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.vehicles.intake.store') }}" class="grid gap-4 max-w-2xl" id="vehicleIntakeForm">
                @csrf

                <!-- Vehicle Model Selection -->
                <div>
                    <label class="text-sm font-medium">Vehicle Model <span class="text-red-600">*</span></label>
                    <select class="kt-select w-full" name="vehicle_model_id" id="vehicle_model_id" onchange="toggleModelFields()">
                        <option value="">Select existing model or create new...</option>
                        @foreach($vehicleModels as $model)
                            <option value="{{ $model->id }}" @selected(old('vehicle_model_id') == $model->id)>
                                {{ $model->display_name }}
                            </option>
                        @endforeach
                        <option value="new" @selected(old('vehicle_model_id') === 'new')>+ Create New Model</option>
                    </select>
                    @error('vehicle_model_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-secondary-foreground mt-1">Select an existing vehicle model (e.g., Toyota PickUp 2018) or create a new one</p>
                </div>

                <!-- New Model Fields (hidden by default) -->
                <div id="newModelFields" style="display: none;" class="grid gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-sm">Create New Vehicle Model</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium">Make <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" name="make" id="make" value="{{ old('make') }}" placeholder="e.g., Toyota, Honda" />
                            @error('make')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium" for="vehicle_type">Vehicle Type <span class="text-red-600">*</span></label>
                            <x-fleet-vehicle-type-select name="vehicle_type" id="vehicle_type" :required="true" />
                            @error('vehicle_type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium">Year of Manufacture <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" type="number" name="year_of_manufacture" id="year_of_manufacture" 
                                   value="{{ old('year_of_manufacture') }}" 
                                   min="1950" max="{{ date('Y') }}" 
                                   placeholder="e.g., 2018" />
                            @error('year_of_manufacture')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p class="text-xs text-secondary-foreground">This will create a new vehicle model: <strong id="modelPreview">Make VehicleType Year</strong></p>
                </div>

                <!-- Vehicle-Specific Fields -->
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-sm mb-4">Vehicle Details</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-sm font-medium">Chassis Number <span class="text-red-600">*</span></label>
                            <input class="kt-input w-full" name="chassis_number" value="{{ old('chassis_number') }}" required />
                            @error('chassis_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-secondary-foreground mt-1">Primary identifier - must be unique</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Engine Number</label>
                            <input class="kt-input w-full" name="engine_number" value="{{ old('engine_number') }}" />
                            @error('engine_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            <p class="text-xs text-secondary-foreground mt-1">Primary identifier - can be changed later</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium">Reg No</label>
                            <input class="kt-input w-full" name="reg_no" value="{{ old('reg_no') }}" />
                            @error('reg_no')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium">Received Date</label>
                            <input class="kt-input w-full" type="date" name="received_at" value="{{ old('received_at') }}" />
                            @error('received_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-sm font-medium">Date of Allocation (optional)</label>
                            <input class="kt-input w-full" type="date" name="date_of_allocation" value="{{ old('date_of_allocation') }}" />
                            @error('date_of_allocation')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-medium">Notes</label>
                        <textarea class="kt-textarea w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button class="kt-btn kt-btn-primary" type="submit">Receive Vehicle</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleModelFields() {
            const select = document.getElementById('vehicle_model_id');
            const newModelFields = document.getElementById('newModelFields');
            const makeInput = document.getElementById('make');
            const typeInput = document.getElementById('vehicle_type');
            const yearInput = document.getElementById('year_of_manufacture');
            const modelPreview = document.getElementById('modelPreview');

            if (select.value === 'new') {
                newModelFields.style.display = 'block';
                makeInput.required = true;
                typeInput.required = true;
                yearInput.required = true;
            } else {
                newModelFields.style.display = 'none';
                makeInput.required = false;
                typeInput.required = false;
                yearInput.required = false;
            }

            updateModelPreview();
        }

        function updateModelPreview() {
            const make = document.getElementById('make').value || 'Make';
            const type = document.getElementById('vehicle_type').value || 'VehicleType';
            const year = document.getElementById('year_of_manufacture').value || 'Year';
            document.getElementById('modelPreview').textContent = `${make} ${type} ${year}`;
        }

        // Update preview on input
        document.getElementById('make')?.addEventListener('input', updateModelPreview);
        document.getElementById('vehicle_type')?.addEventListener('change', updateModelPreview);
        document.getElementById('year_of_manufacture')?.addEventListener('input', updateModelPreview);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleModelFields();
        });
    </script>
    @endpush
@endsection

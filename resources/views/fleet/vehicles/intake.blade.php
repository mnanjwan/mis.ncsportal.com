@extends('layouts.app')

@section('title', 'Receive Vehicle')
@section('page-title', 'Receive Vehicle')

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Store/Transport Vehicle Intake</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.vehicles.intake.store') }}" class="grid gap-4 max-w-2xl">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Make</label>
                        <input class="kt-input w-full" name="make" value="{{ old('make') }}" required />
                        @error('make')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Model</label>
                        <input class="kt-input w-full" name="model" value="{{ old('model') }}" />
                        @error('model')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium">Vehicle Type</label>
                        <select class="kt-select w-full" name="vehicle_type" required>
                            <option value="">Select type</option>
                            <option value="SALOON" @selected(old('vehicle_type')==='SALOON')>Saloon</option>
                            <option value="SUV" @selected(old('vehicle_type')==='SUV')>SUV</option>
                            <option value="BUS" @selected(old('vehicle_type')==='BUS')>Bus</option>
                        </select>
                        @error('vehicle_type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Year</label>
                        <input class="kt-input w-full" type="number" name="year_of_manufacture" value="{{ old('year_of_manufacture') }}" />
                        @error('year_of_manufacture')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Received Date</label>
                        <input class="kt-input w-full" type="date" name="received_at" value="{{ old('received_at') }}" />
                        @error('received_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Chassis Number</label>
                        <input class="kt-input w-full" name="chassis_number" value="{{ old('chassis_number') }}" required />
                        @error('chassis_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Engine Number</label>
                        <input class="kt-input w-full" name="engine_number" value="{{ old('engine_number') }}" />
                        @error('engine_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Reg No</label>
                        <input class="kt-input w-full" name="reg_no" value="{{ old('reg_no') }}" />
                        @error('reg_no')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Date of Allocation (optional)</label>
                        <input class="kt-input w-full" type="date" name="date_of_allocation" value="{{ old('date_of_allocation') }}" />
                        @error('date_of_allocation')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Notes</label>
                    <textarea class="kt-textarea w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3">
                    <button class="kt-btn kt-btn-primary" type="submit">Receive Vehicle</button>
                    <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection


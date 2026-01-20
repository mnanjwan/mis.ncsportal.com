@extends('layouts.app')

@section('title', 'Edit Vehicle Identifiers')
@section('page-title', 'Edit Vehicle Identifiers')

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Update Reg No / Engine No</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form method="POST" action="{{ route('fleet.vehicles.update-identifiers', $vehicle) }}" class="grid gap-4 max-w-xl">
                @csrf

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


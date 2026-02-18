@extends('layouts.app')

@section('title', 'Allocate Vehicle to Command')
@section('page-title', 'Allocate Vehicle to Command')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.cc-tl.dashboard') }}">Fleet</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.vehicles.index') }}">Vehicles</a>
    <span>/</span>
    <span class="text-primary">Allocate to Command</span>
@endsection

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Allocate Vehicle Directly to Command</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <p class="text-sm text-secondary-foreground mb-4">
                Select a vehicle in stock and the command to receive it. The command (Area Controller) will acknowledge receipt on the vehicle page.
            </p>

            @if($vehicles->isEmpty())
                <p class="text-sm text-secondary-foreground">No vehicles in stock available for allocation. Only vehicles with lifecycle status <strong>IN_STOCK</strong> and not reserved for a request can be allocated.</p>
                <a class="kt-btn kt-btn-secondary mt-4" href="{{ route('fleet.vehicles.index') }}">Back to Vehicles</a>
            @else
                <form method="POST" action="{{ route('fleet.allocate-to-command.store') }}" class="grid gap-4 max-w-2xl">
                    @csrf

                    <div>
                        <label class="text-sm font-medium" for="fleet_vehicle_id">Vehicle <span class="text-red-600">*</span></label>
                        <select class="kt-input w-full" name="fleet_vehicle_id" id="fleet_vehicle_id" required>
                            <option value="">Select vehicle...</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" @selected(old('fleet_vehicle_id') == $v->id)>
                                    {{ $v->reg_no ?? $v->chassis_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }} ({{ $v->vehicle_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('fleet_vehicle_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium" for="command_id">Command to receive <span class="text-red-600">*</span></label>
                        <select class="kt-input w-full" name="command_id" id="command_id" required>
                            <option value="">Select command...</option>
                            @foreach($commands as $cmd)
                                <option value="{{ $cmd->id }}" @selected(old('command_id') == $cmd->id)>{{ $cmd->name }}</option>
                            @endforeach
                        </select>
                        @error('command_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium" for="notes">Notes (optional)</label>
                        <textarea class="kt-textarea w-full" name="notes" id="notes" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">Allocate to Command</button>
                        <a class="kt-btn kt-btn-secondary" href="{{ route('fleet.vehicles.index') }}">Cancel</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

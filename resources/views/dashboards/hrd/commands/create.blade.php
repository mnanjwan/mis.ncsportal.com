@extends('layouts.app')

@section('title', 'Create Command')
@section('page-title', 'Create Command')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Commands</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Create New Command</h3>
            </div>
            <div class="kt-card-content">
                <form method="POST" action="{{ route('hrd.commands.store') }}" class="flex flex-col gap-5">
                    @csrf

                    @if($errors->any())
                        <div class="kt-card bg-danger/10 border border-danger/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">
                                Command Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   class="kt-input w-full @error('code') border-danger @enderror" 
                                   placeholder="e.g., APAPA, MMIA"
                                   required>
                            @error('code')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">
                                Command Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="kt-input w-full @error('name') border-danger @enderror" 
                                   placeholder="e.g., Apapa Command"
                                   required>
                            @error('name')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Zone <span class="text-danger">*</span>
                        </label>
                        <select name="zone_id" 
                                class="kt-input w-full @error('zone_id') border-danger @enderror" 
                                required>
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" 
                                        {{ old('zone_id', request('zone_id')) == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }} ({{ $zone->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('zone_id')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-secondary-foreground mt-1">All commands must belong to a zone</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Location
                        </label>
                        <input type="text" 
                               name="location" 
                               value="{{ old('location') }}"
                               class="kt-input w-full @error('location') border-danger @enderror" 
                               placeholder="e.g., Lagos, Abuja">
                        @error('location')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="kt-checkbox">
                            <span class="text-sm text-secondary-foreground">Active</span>
                        </label>
                        <p class="text-xs text-secondary-foreground mt-1">Inactive commands cannot be assigned to officers</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-border">
                        <a href="{{ route('hrd.commands.index') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Create Command
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


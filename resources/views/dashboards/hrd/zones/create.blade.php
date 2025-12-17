@extends('layouts.app')

@section('title', 'Create Zone')
@section('page-title', 'Create Zone')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.zones.index') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.zones.index') }}">Zones</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Create New Zone</h3>
            </div>
            <div class="kt-card-content">
                <form method="POST" action="{{ route('hrd.zones.store') }}" class="flex flex-col gap-5">
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
                                Zone Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   class="kt-input w-full @error('code') border-danger @enderror" 
                                   placeholder="e.g., ZONE_A, HEADQUARTERS"
                                   required>
                            @error('code')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-secondary-foreground mt-1">Unique code identifier for the zone</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">
                                Zone Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="kt-input w-full @error('name') border-danger @enderror" 
                                   placeholder="e.g., Zone A HQ, Headquarters"
                                   required>
                            @error('name')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Description
                        </label>
                        <textarea name="description" 
                                  rows="3"
                                  class="kt-input w-full @error('description') border-danger @enderror" 
                                  placeholder="Optional description of the zone">{{ old('description') }}</textarea>
                        @error('description')
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
                        <p class="text-xs text-secondary-foreground mt-1">Inactive zones cannot be assigned to new commands</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-border">
                        <a href="{{ route('hrd.zones.index') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Create Zone
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


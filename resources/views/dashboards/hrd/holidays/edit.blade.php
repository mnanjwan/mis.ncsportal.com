@extends('layouts.app')

@section('title', 'Edit Holiday')
@section('page-title', 'Edit Holiday')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.holidays.index') }}">Holiday Settings</a>
    <span>/</span>
    <span class="text-primary">Edit Holiday</span>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Edit Holiday: {{ $holiday->name }}</h3>
        </div>
        <div class="kt-card-content p-5 lg:p-7.5">
            <form action="{{ route('hrd.holidays.update', $holiday->id) }}" method="POST" class="flex flex-col gap-6">
                @csrf
                @method('PUT')
                
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-foreground">Holiday Name</label>
                    <input type="text" name="name" class="kt-input" placeholder="e.g. Id-el-Fitr" required value="{{ old('name', $holiday->name) }}">
                    <p class="text-xs text-secondary-foreground">Enter a descriptive name for the holiday.</p>
                    @error('name') <span class="text-xs text-danger font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-foreground">Date</label>
                    <input type="date" name="date" class="kt-input" required value="{{ old('date', $holiday->date->format('Y-m-d')) }}">
                    <p class="text-xs text-secondary-foreground">Select the specific date for this holiday.</p>
                    @error('date') <span class="text-xs text-danger font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-start gap-3 p-4 rounded-lg bg-muted/30 border border-input">
                    <input type="checkbox" name="is_floating" id="is_floating" class="kt-checkbox mt-1" value="1" {{ old('is_floating', $holiday->is_floating) ? 'checked' : '' }}>
                    <div class="flex flex-col gap-1 cursor-pointer" onclick="document.getElementById('is_floating').click()">
                        <label for="is_floating" class="text-sm font-semibold text-foreground">Floating Holiday</label>
                        <p class="text-xs text-secondary-foreground">Enable if this holiday's date changes every year (e.g., religious holidays). If disabled, it will be treated as a fixed date override.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <a href="{{ route('hrd.holidays.index') }}" class="kt-btn kt-btn-light">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

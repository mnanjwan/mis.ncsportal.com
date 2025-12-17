@extends('layouts.app')

@section('title', 'Extend Emolument Timeline')
@section('page-title', 'Extend Emolument Timeline')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.emolument-timeline') }}">Emolument Timeline</a>
    <span>/</span>
    <span class="text-primary">Extend</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.emolument-timeline') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Timelines
            </a>
        </div>

        <!-- Timeline Info -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex flex-col gap-2">
                    <h3 class="text-lg font-semibold text-mono">Current Timeline</h3>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Period: <span class="font-semibold text-mono">
                                {{ $timeline->start_date->format('d/m/Y') }} - {{ $timeline->end_date->format('d/m/Y') }}
                            </span>
                        </span>
                        <span class="text-secondary-foreground">
                            Year: <span class="font-semibold text-mono">{{ $timeline->year ?? 'N/A' }}</span>
                        </span>
                        <span class="kt-badge kt-badge-{{ $timeline->is_active ? 'success' : 'secondary' }} kt-badge-sm">
                            {{ $timeline->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Extend Emolument Timeline</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.emolument-timeline.extend.store', $timeline->id) }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Extension End Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">New End Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="extension_end_date" 
                                   class="kt-input" 
                                   value="{{ old('extension_end_date') }}"
                                   min="{{ $timeline->end_date->format('Y-m-d') }}"
                                   required>
                            <span class="text-xs text-secondary-foreground mt-1">
                                Must be after current end date: {{ $timeline->end_date->format('d/m/Y') }}
                            </span>
                            @error('extension_end_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Extension Reason -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Extension Reason</label>
                            <textarea name="extension_reason" 
                                      class="kt-input" 
                                      rows="4"
                                      placeholder="Enter reason for extension">{{ old('extension_reason') }}</textarea>
                            @error('extension_reason')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.emolument-timeline') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Extend Timeline
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


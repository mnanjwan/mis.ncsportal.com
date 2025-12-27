@extends('layouts.app')

@section('title', 'Extend APER Timeline')
@section('page-title', 'Extend APER Timeline')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.aper-timeline') }}">APER Timeline</a>
    <span>/</span>
    <span class="text-primary">Extend</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.aper-timeline') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Timelines
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="kt-card bg-success/10 border border-success/20 mb-5">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-success text-xl"></i>
                        <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                        <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
                <div class="kt-card-content p-4">
                    <ul class="list-disc list-inside text-sm text-danger">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Extend APER Timeline</h3>
            </div>
            <div class="kt-card-content">
                <div class="mb-5 p-4 bg-muted/50 rounded-lg">
                    <p class="text-sm text-secondary-foreground mb-2"><strong>Current Period:</strong></p>
                    <p class="text-sm font-medium text-foreground">
                        {{ $timeline->start_date->format('d/m/Y H:i') }} - {{ $timeline->end_date->format('d/m/Y H:i') }}
                    </p>
                </div>

                <form action="{{ route('hrd.aper-timeline.extend.store', $timeline->id) }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Extension End Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">New End Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="extension_end_date" 
                                       id="extension_end_date"
                                       class="kt-input" 
                                       value="{{ old('extension_end_date') }}"
                                       min="{{ $timeline->end_date->format('Y-m-d') }}"
                                       required>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Must be after {{ $timeline->end_date->format('d/m/Y H:i') }}
                                </p>
                                @error('extension_end_date')
                                    <span class="text-sm text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="kt-form-label">New End Time <span class="text-danger">*</span></label>
                                <input type="time" 
                                       name="extension_end_time" 
                                       id="extension_end_time"
                                       class="kt-input" 
                                       value="{{ old('extension_end_time', '23:59') }}"
                                       required>
                                @error('extension_end_time')
                                    <span class="text-sm text-danger">{{ $message }}</span>
                                @enderror
                            </div>
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
                            <a href="{{ route('hrd.aper-timeline') }}" class="kt-btn kt-btn-secondary">
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


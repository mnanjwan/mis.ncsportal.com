@extends('layouts.app')

@section('title', 'Create APER Timeline')
@section('page-title', 'Create APER Timeline')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.aper-timeline') }}">APER Timeline</a>
    <span>/</span>
    <span class="text-primary">Create</span>
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
                <h3 class="kt-card-title">Create APER Timeline</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.aper-timeline.store') }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Year -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="year" 
                                   class="kt-input" 
                                   value="{{ old('year', date('Y')) }}"
                                   min="2020"
                                   max="2100"
                                   placeholder="Enter year"
                                   required>
                            @error('year')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="start_date" 
                                   class="kt-input" 
                                   value="{{ old('start_date') }}"
                                   required>
                            @error('start_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="end_date" 
                                   class="kt-input" 
                                   value="{{ old('end_date') }}"
                                   required>
                            @error('end_date')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Description</label>
                            <textarea name="description" 
                                      class="kt-input" 
                                      rows="4"
                                      placeholder="Enter timeline description">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-sm text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center gap-2">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-input text-primary focus:ring-primary">
                            <label for="is_active" class="kt-form-label cursor-pointer">
                                Set as Active Timeline
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('hrd.aper-timeline') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i> Create Timeline
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


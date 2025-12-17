@extends('layouts.app')

@section('title', 'Create Emolument Timeline')
@section('page-title', 'Create Emolument Timeline')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.emolument-timeline') }}">Emolument Timeline</a>
    <span>/</span>
    <span class="text-primary">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.emolument-timeline') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Timelines
            </a>
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
                <h3 class="kt-card-title">Create Emolument Timeline</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('hrd.emolument-timeline.store') }}" method="POST">
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
                            <a href="{{ route('hrd.emolument-timeline') }}" class="kt-btn kt-btn-secondary">
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


@extends('layouts.app')

@section('title', 'Nominate Officer for Course')
@section('page-title', 'Nominate Officer for Course')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.courses') }}">Course Nominations</a>
    <span>/</span>
    <span class="text-primary">Nominate Officer</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Nominate Officer for Course</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ route('hrd.courses.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Officer -->
                <div class="space-y-2">
                    <label for="officer_id" class="block text-sm font-medium text-foreground">
                        Officer <span class="text-danger">*</span>
                    </label>
                    <select name="officer_id" 
                            id="officer_id" 
                            class="kt-input @error('officer_id') kt-input-error @enderror"
                            required>
                        <option value="">Select Officer</option>
                        @foreach($officers as $officer)
                            <option value="{{ $officer->id }}" {{ old('officer_id') == $officer->id ? 'selected' : '' }}>
                                {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }} - {{ $officer->service_number ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('officer_id')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course Name -->
                <div class="space-y-2">
                    <label for="course_name" class="block text-sm font-medium text-foreground">
                        Course Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="course_name" 
                           id="course_name"
                           value="{{ old('course_name') }}"
                           class="kt-input @error('course_name') kt-input-error @enderror"
                           placeholder="e.g., Advanced Leadership Training"
                           required>
                    @error('course_name')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course Type -->
                <div class="space-y-2">
                    <label for="course_type" class="block text-sm font-medium text-foreground">
                        Course Type
                    </label>
                    <input type="text" 
                           name="course_type" 
                           id="course_type"
                           value="{{ old('course_type') }}"
                           class="kt-input @error('course_type') kt-input-error @enderror"
                           placeholder="e.g., Professional Development, Technical Training">
                    @error('course_type')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-foreground">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date"
                               value="{{ old('start_date') }}"
                               class="kt-input @error('start_date') kt-input-error @enderror"
                               required>
                        @error('start_date')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-foreground">
                            End Date
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date"
                               value="{{ old('end_date') }}"
                               class="kt-input @error('end_date') kt-input-error @enderror">
                        @error('end_date')
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-foreground">
                        Notes
                    </label>
                    <textarea name="notes" 
                              id="notes"
                              rows="3"
                              class="kt-input @error('notes') kt-input-error @enderror"
                              placeholder="Additional information about the course nomination...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route('hrd.courses') }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Nominate Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


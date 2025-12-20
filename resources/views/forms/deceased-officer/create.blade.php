@extends('layouts.app')

@section('title', 'Report Deceased Officer')
@section('page-title', 'Report Deceased Officer')

@section('breadcrumbs')
    @if(auth()->user()->hasRole('Area Controller'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    @elseif(auth()->user()->hasRole('Staff Officer'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    @endif
    <span>/</span>
    <span class="text-primary">Report Deceased Officer</span>
@endsection

@section('content')
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
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Report Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Report Deceased Officer</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ auth()->user()->hasRole('Area Controller') ? route('area-controller.deceased-officers.store') : route('staff-officer.deceased-officers.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Officer Selection -->
                        <div>
                            <label for="officer_id" class="block text-sm font-medium text-foreground mb-2">
                                Officer <span class="text-danger">*</span>
                            </label>
                            <select id="officer_id" 
                                    name="officer_id" 
                                    class="kt-input w-full" 
                                    required>
                                <option value="">Select Officer</option>
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}" {{ old('officer_id') == $officer->id ? 'selected' : '' }}>
                                        {{ $officer->service_number }} - {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->substantive_rank }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date of Death -->
                        <div>
                            <label for="date_of_death" class="block text-sm font-medium text-foreground mb-2">
                                Date of Death <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   id="date_of_death" 
                                   name="date_of_death" 
                                   value="{{ old('date_of_death') }}"
                                   class="kt-input w-full" 
                                   required
                                   max="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Death Certificate -->
                        <div>
                            <label for="death_certificate" class="block text-sm font-medium text-foreground mb-2">
                                Death Certificate (Optional)
                            </label>
                            <input type="file" 
                                   id="death_certificate" 
                                   name="death_certificate" 
                                   class="kt-input w-full"
                                   accept=".jpeg,.jpg,.png,.pdf">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Accepted formats: JPEG, JPG, PNG, PDF (Max: 5MB)
                            </p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-foreground mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="kt-input w-full"
                                      placeholder="Enter any additional information">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>This report will be sent to Welfare for validation</li>
                                            <li>Welfare will verify the death certificate and generate comprehensive data</li>
                                            <li>The officer will be marked as deceased after Welfare validation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ auth()->user()->hasRole('Area Controller') ? route('area-controller.dashboard') : route('staff-officer.dashboard') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Submit Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

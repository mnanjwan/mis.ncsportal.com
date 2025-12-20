@extends('layouts.app')

@section('title', 'Upload Training Results')
@section('page-title', 'Upload Training Results')

@section('content')
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

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Upload Training Results (CSV)</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('tradoc.upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- CSV File Upload -->
                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-foreground mb-2">
                                CSV File <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt"
                                   class="kt-input w-full" 
                                   required>
                            <p class="text-xs text-secondary-foreground mt-1">
                                Accepted formats: CSV, TXT (Max: 5MB)
                            </p>
                        </div>

                        <!-- CSV Format Info -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <h4 class="text-sm font-semibold text-info mb-2">CSV Format Requirements</h4>
                                <p class="text-xs text-secondary-foreground mb-2">Your CSV file must have the following columns (in order):</p>
                                <ol class="list-decimal list-inside text-xs text-secondary-foreground space-y-1">
                                    <li>Appointment Number</li>
                                    <li>Officer Name</li>
                                    <li>Training Score (0-100)</li>
                                    <li>Status (PASS/FAIL) - Optional, defaults to PASS if score >= 50</li>
                                </ol>
                                <p class="text-xs text-danger font-semibold mt-2">
                                    <strong>Important:</strong> Only appointment numbers from the new recruits list (without service numbers) can be uploaded. The system will validate each appointment number against existing new recruits.
                                </p>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Results will be automatically sorted by performance (highest to lowest) after upload.
                                </p>
                            </div>
                        </div>

                        @if($errors->has('csv_errors'))
                            <div class="kt-card bg-danger/10 border border-danger/20">
                                <div class="kt-card-header">
                                    <h4 class="text-sm font-semibold text-danger">CSV Validation Errors</h4>
                                </div>
                                <div class="kt-card-content p-4">
                                    <p class="text-xs text-danger mb-3 font-medium">
                                        The following errors were found in your CSV file. Please fix them and try again:
                                    </p>
                                    <div class="max-h-60 overflow-y-auto">
                                        <ul class="list-disc list-inside text-xs text-danger space-y-1">
                                            @foreach($errors->get('csv_errors') as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($errors->any() && !$errors->has('csv_errors'))
                            <div class="kt-card bg-danger/10 border border-danger/20">
                                <div class="kt-card-content p-4">
                                    <ul class="list-disc list-inside text-xs text-danger space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('tradoc.dashboard') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-file-up"></i>
                                Upload CSV
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

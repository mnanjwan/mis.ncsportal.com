@extends('layouts.app')

@section('title', 'Apply for Pass')
@section('page-title', 'Apply for Pass')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Apply for Pass</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Pass Info Card -->
            <div class="kt-card bg-warning/10 border border-warning/20">
                <div class="kt-card-content p-5">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-2xl text-warning"></i>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-mono">Pass Eligibility Requirements</span>
                            <span class="text-xs text-secondary-foreground">
                                Pass can only be applied if and only if you have exhausted your Annual Leave
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Maximum number of days: 5 days per application
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Pass Info Card -->

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

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form class="kt-card" action="{{ route('pass.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Pass Application Form</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">Start Date</label>
                            <input class="kt-input" type="date" name="start_date" id="start-date"
                                value="{{ old('start_date') }}" required />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label font-normal text-mono">End Date</label>
                            <input class="kt-input" type="date" name="end_date" id="end-date" value="{{ old('end_date') }}"
                                required />
                            <span class="text-xs text-secondary-foreground">Maximum 5 days</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Reason for Pass</label>
                        <textarea class="kt-input" placeholder="Enter reason for pass" name="reason" rows="4"
                            required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Supporting Documents</label>
                        <input class="kt-input" type="file" name="supporting_documents" accept="image/jpeg,application/pdf"
                            multiple />
                        <span class="text-xs text-secondary-foreground">Upload supporting documents</span>
                        <span class="text-xs" style="color: red;">
                            <strong>Document Type Allowed:</strong> JPEG or PDF format (multiple files allowed)<br>
                            <strong>Document Size Allowed:</strong> Maximum 5MB per file
                        </span>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('officer.dashboard') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit" id="submit-btn">
                        Submit Application
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
            <!-- End of Form -->
        </div>
        <div class="xl:col-span-1">
            <!-- Pass Rules Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Pass Rules</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <p class="text-xs text-secondary-foreground">
                            Pass allows you to be away from duty for short periods when you have exhausted your annual
                            leave.
                        </p>
                        <div class="kt-card shadow-none bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-xs text-secondary-foreground">
                                    <strong class="text-mono">Important:</strong>
                                </p>
                                <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                    <li>Maximum 5 days per application</li>
                                    <li>Only available after exhausting annual leave</li>
                                    <li>Requires approval from Staff Officer and DC Admin</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground">
                            Your application will be reviewed by Staff Officer, then minuted to DC Admin for approval.
                        </p>
                    </div>
                </div>
            </div>
            <!-- End of Pass Rules Card -->
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Calculate days and validate
                const startDate = document.getElementById('start-date');
                const endDate = document.getElementById('end-date');

                function validateDates() {
                    if (startDate.value && endDate.value) {
                        const start = new Date(startDate.value);
                        const end = new Date(endDate.value);
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                        if (diffDays > 5) {
                            alert('Pass cannot exceed 5 days. Please adjust your dates.');
                            endDate.value = '';
                            return false;
                        }
                    }
                    return true;
                }

                startDate.addEventListener('change', validateDates);
                endDate.addEventListener('change', validateDates);
            });
        </script>
    @endpush
@endsection
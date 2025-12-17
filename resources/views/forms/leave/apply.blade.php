@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Apply for Leave</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Leave Balance Info -->
            <div class="kt-card bg-success/10 border border-success/20">
                <div class="kt-card-content p-5">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-2xl text-success"></i>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-mono">Leave Balance Available</span>
                            <span class="text-xs text-secondary-foreground" id="leave-balance-info">
                                Annual Leave: 30 days remaining (Standard)
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Annual leave can be applied for a maximum of 2 times per year
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Leave Balance Info -->

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
            <form class="kt-card" action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Leave Application Form</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Leave Type</label>
                        <select class="kt-input" name="leave_type_id" id="leave-type" required>
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                        </div>
                    </div>
                    <div class="flex flex-col gap-1" id="edd_field" style="display: none;">
                        <label class="kt-form-label font-normal text-mono">Expected Date of Delivery (EDD)</label>
                        <input class="kt-input" type="date" name="expected_date_of_delivery"
                            value="{{ old('expected_date_of_delivery') }}" />
                        <span class="text-xs text-secondary-foreground">Required for Maternity Leave</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Reason for Leave</label>
                        <textarea class="kt-input" placeholder="Enter reason for leave" name="reason" rows="4"
                            required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Supporting Documents</label>
                        <input class="kt-input" type="file" name="medical_certificate"
                            accept="image/jpeg,application/pdf" />
                        <span class="text-xs text-secondary-foreground">Upload supporting documents (JPEG or PDF
                            format)</span>
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
            <!-- Leave Rules Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Leave Rules</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="kt-card shadow-none bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-xs text-secondary-foreground">
                                    <strong class="text-mono">Annual Leave:</strong>
                                </p>
                                <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                    <li>GL 07 and Below: 28 Days</li>
                                    <li>Level 08 and above: 30 days</li>
                                    <li>Maximum 2 times per year</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground">
                            Your application will be reviewed by Staff Officer, then minuted to DC Admin for approval.
                        </p>
                        <p class="text-xs text-secondary-foreground">
                            You will receive a notification once your leave is approved.
                        </p>
                    </div>
                </div>
            </div>
            <!-- End of Leave Rules Card -->
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Show EDD field for Maternity Leave
                const leaveTypeSelect = document.getElementById('leave-type');
                const eddField = document.getElementById('edd_field');
                const eddInput = eddField.querySelector('input');

                function toggleEddField() {
                    const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                    if (selectedOption && selectedOption.text.includes('Maternity Leave')) {
                        eddField.style.display = 'block';
                        eddInput.required = true;
                    } else {
                        eddField.style.display = 'none';
                        eddInput.required = false;
                    }
                }

                leaveTypeSelect.addEventListener('change', toggleEddField);

                // Check on load in case of validation errors redirecting back
                toggleEddField();
            });
        </script>
    @endpush
@endsection
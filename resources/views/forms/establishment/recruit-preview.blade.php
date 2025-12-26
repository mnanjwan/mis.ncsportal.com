@extends('layouts.app')

@section('title', 'Add New Recruit - Preview')
@section('page-title', 'Add New Recruit - Preview')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.new-recruits') }}">New Recruits</a>
    <span>/</span>
    <span class="text-primary">Preview</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-4 lg:p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #28a745; color: white;">✓</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #28a745;">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #28a745; color: white;">✓</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #28a745;">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #28a745; color: white;">✓</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #28a745;">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #28a745; color: white;">✓</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #28a745;">Next of Kin</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #068b57; color: white;">5</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #068b57;">Preview</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Content -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Review Your Information</h3>
            <p class="text-sm text-muted mt-1">Please review all your information before final submission. You can go back to edit any step.</p>
        </div>
        <div class="kt-card-content">
            <form method="POST" action="{{ route('establishment.new-recruits.final-submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="profile_picture_data" value="{{ $step4['profile_picture_data'] ?? '' }}">
                
                <div class="grid lg:grid-cols-2 gap-5 lg:gap-7.5">
                    <!-- Left Column -->
                    <div class="flex flex-col gap-5">
                        <!-- Step 1: Personal Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h4 class="kt-card-title">Step 1: Personal Information</h4>
                            </div>
                            <div class="kt-card-content">
                                <div class="grid gap-3">
                                    <div><strong>Name:</strong> {{ $step1['initials'] ?? '' }} {{ $step1['surname'] ?? '' }}</div>
                                    <div><strong>Gender:</strong> {{ ($step1['sex'] ?? '') == 'M' ? 'Male' : (($step1['sex'] ?? '') == 'F' ? 'Female' : 'N/A') }}</div>
                                    <div><strong>Date of Birth:</strong> {{ $step1['date_of_birth'] ?? 'N/A' }}</div>
                                    <div><strong>Phone:</strong> {{ $step1['phone_number'] ?? 'N/A' }}</div>
                                    <div><strong>Email:</strong> {{ $step1['email'] ?? 'N/A' }}</div>
                                    <div><strong>State of Origin:</strong> {{ $step1['state_of_origin'] ?? 'N/A' }}</div>
                                    <div><strong>LGA:</strong> {{ $step1['lga'] ?? 'N/A' }}</div>
                                    <div><strong>Geopolitical Zone:</strong> {{ $step1['geopolitical_zone'] ?? 'N/A' }}</div>
                                    <div><strong>Marital Status:</strong> {{ $step1['marital_status'] ?? 'N/A' }}</div>
                                    <div><strong>Residential Address:</strong> {{ $step1['residential_address'] ?? 'N/A' }}</div>
                                    <div><strong>Permanent Home Address:</strong> {{ $step1['permanent_home_address'] ?? 'N/A' }}</div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('establishment.new-recruits.create') }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 1</a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Employment Details -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h4 class="kt-card-title">Step 2: Employment Details</h4>
                            </div>
                            <div class="kt-card-content">
                                <div class="grid gap-3">
                                    <div><strong>Date of First Appointment:</strong> {{ $step2['date_of_first_appointment'] ?? 'N/A' }}</div>
                                    <div><strong>Date of Present Appointment:</strong> {{ $step2['date_of_present_appointment'] ?? 'N/A' }}</div>
                                    <div><strong>Substantive Rank:</strong> {{ $step2['substantive_rank'] ?? 'N/A' }}</div>
                                    <div><strong>Salary Grade Level:</strong> {{ $step2['salary_grade_level'] ?? 'N/A' }}</div>
                                    <div><strong>Appointment Number:</strong> 
                                        @php
                                            use App\Helpers\AppointmentNumberHelper;
                                            // Check if appointment number already exists (e.g., from officer record)
                                            $existingAppointmentNumber = $officer->appointment_number ?? null;
                                            
                                            if ($existingAppointmentNumber) {
                                                // Appointment number already assigned
                                                $displayNumber = $existingAppointmentNumber;
                                                $showNote = false;
                                            } else {
                                                // Preview what will be assigned
                                                $prefix = AppointmentNumberHelper::getPrefix(
                                                    $step2['substantive_rank'] ?? '',
                                                    $step2['salary_grade_level'] ?? null
                                                );
                                                $displayNumber = AppointmentNumberHelper::generateNext($prefix);
                                                $showNote = true;
                                            }
                                        @endphp
                                        <span class="text-primary font-semibold">
                                            {{ $displayNumber }}
                                        </span>
                                        @if($showNote)
                                        <span class="text-xs text-muted ml-2">(Will be assigned after creation)</span>
                                        @endif
                                    </div>
                                    <div><strong>Date Posted to Station:</strong> {{ $step2['date_posted_to_station'] ?? 'N/A' }}</div>
                                    <div><strong>Unit:</strong> {{ $step2['unit'] ?? 'N/A' }}</div>
                                    @if(isset($step2['education']) && is_array($step2['education']))
                                    <div>
                                        <strong>Education:</strong>
                                        <ul class="list-disc list-inside mt-2">
                                            @foreach($step2['education'] as $edu)
                                            <li>{{ $edu['university'] ?? 'N/A' }} - {{ $edu['qualification'] ?? 'N/A' }} @if(!empty($edu['year_obtained'])) ({{ $edu['year_obtained'] }}) @endif @if(!empty($edu['discipline'])) - {{ $edu['discipline'] }} @endif</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('establishment.new-recruits.step2') }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 2</a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Banking Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h4 class="kt-card-title">Step 3: Banking Information</h4>
                            </div>
                            <div class="kt-card-content">
                                <div class="grid gap-3">
                                    <div><strong>Bank Name:</strong> {{ $step3['bank_name'] ?? 'N/A' }}</div>
                                    <div><strong>Account Number:</strong> {{ $step3['bank_account_number'] ?? 'N/A' }}</div>
                                    <div><strong>Sort Code:</strong> {{ $step3['sort_code'] ?? 'N/A' }}</div>
                                    <div><strong>PFA Name:</strong> {{ $step3['pfa_name'] ?? 'N/A' }}</div>
                                    <div><strong>RSA Number:</strong> {{ $step3['rsa_number'] ?? 'N/A' }}</div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('establishment.new-recruits.step3') }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 3</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="flex flex-col gap-5">
                        <!-- Profile Photo -->
                        @if(isset($step4['profile_picture_data']) && !empty($step4['profile_picture_data']))
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h4 class="kt-card-title">Profile Photo</h4>
                            </div>
                            <div class="kt-card-content">
                                <div class="flex justify-center">
                                    <div class="relative bg-white rounded-lg p-4 shadow-lg" style="width: 200px; height: 250px; border: 4px solid #068b57;">
                                        <div class="w-full h-full flex items-center justify-center bg-muted/10 rounded overflow-hidden">
                                            <img src="{{ $step4['profile_picture_data'] }}" 
                                                 alt="Profile Photo"
                                                 style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">
                                        </div>
                                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-white text-xs px-3 py-1 rounded whitespace-nowrap" style="background-color: #068b57;">
                                            Official Passport Photo
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 4: Next of Kin -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h4 class="kt-card-title">Step 4: Next of Kin</h4>
                            </div>
                            <div class="kt-card-content">
                                @if(isset($step4['next_of_kin']) && is_array($step4['next_of_kin']))
                                <div class="grid gap-4">
                                    @foreach($step4['next_of_kin'] as $index => $nok)
                                    <div class="p-3 border border-input rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <strong>Next of Kin #{{ $index + 1 }}</strong>
                                            @if(isset($nok['is_primary']) && $nok['is_primary'] == '1')
                                            <span class="text-xs px-2 py-1 rounded text-white" style="background-color: #068b57;">Primary</span>
                                            @endif
                                        </div>
                                        <div class="grid gap-2 text-sm">
                                            <div><strong>Name:</strong> {{ $nok['name'] ?? 'N/A' }}</div>
                                            <div><strong>Relationship:</strong> {{ $nok['relationship'] ?? 'N/A' }}</div>
                                            <div><strong>Phone:</strong> {{ $nok['phone_number'] ?? 'N/A' }}</div>
                                            @if(!empty($nok['email']))
                                            <div><strong>Email:</strong> {{ $nok['email'] }}</div>
                                            @endif
                                            <div><strong>Address:</strong> {{ $nok['address'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                
                                <div class="mt-4 grid grid-cols-3 gap-2 text-sm">
                                    <div><strong>Interdicted:</strong> {{ ($step4['interdicted'] ?? false) ? 'Yes' : 'No' }}</div>
                                    <div><strong>Suspended:</strong> {{ ($step4['suspended'] ?? false) ? 'Yes' : 'No' }}</div>
                                    <div><strong>Quartered:</strong> {{ ($step4['quartered'] ?? false) ? 'Yes' : 'No' }}</div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('establishment.new-recruits.step4') }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 4</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between gap-3 pt-5 mt-5 border-t border-input">
                    <a href="{{ route('establishment.new-recruits.step4') }}" class="kt-btn kt-btn-secondary">Back to Edit</a>
                    <button type="submit" id="final-submit-btn" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">
                        <i class="ki-filled ki-check" style="color: white;"></i> Create Recruit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('establishment.new-recruits.final-submit') }}"]');
    const submitBtn = document.getElementById('final-submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog using SweetAlert2
            Swal.fire({
                title: 'Create Recruit?',
                text: 'Are you sure you want to create this recruit? After creation, you can assign appointment numbers (CDT/RCT). This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Create Recruit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#068b57',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '⏳ Creating...';
                    
                    // Submit the form
                    form.submit();
                }
            });
        });
    }
});
</script>
@endpush
@endsection

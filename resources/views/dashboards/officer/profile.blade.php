@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Profile</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Profile Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5">
                    <div class="kt-avatar size-24 relative">
                        <div class="kt-avatar-image">
                            @if($officer->getProfilePictureUrlFull())
                                <img id="profile-page-picture" alt="avatar" src="{{ $officer->getProfilePictureUrlFull() }}" />
                            @else
                                <div class="flex items-center justify-center size-24 rounded-full bg-primary/10 text-primary font-bold text-xl">
                                    {{ strtoupper(($officer->initials[0] ?? '') . ($officer->surname[0] ?? '')) }}
                                </div>
                            @endif
                        </div>
                        @if($isOnboarded || (method_exists($officer, 'needsProfilePictureUpdateAfterPromotion') && $officer->needsProfilePictureUpdateAfterPromotion()))
                        <label for="profile-picture-upload" class="absolute bottom-0 right-0 bg-primary text-white rounded-full p-2 cursor-pointer hover:bg-primary/90 transition-colors" title="Change Profile Picture">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </label>
                        <input type="file" id="profile-picture-upload" class="hidden" accept="image/*">
                        @endif
                    </div>
                    @if($isOnboarded || (method_exists($officer, 'needsProfilePictureUpdateAfterPromotion') && $officer->needsProfilePictureUpdateAfterPromotion()))
                    <div class="text-xs" style="margin-top: 0.5rem;">
                        @if($officer->profile_picture_updated_at)
                            <span class="text-secondary-foreground">Last updated: <strong class="text-mono">{{ $officer->profile_picture_updated_at->format('d/m/Y') }}</strong></span><br>
                        @endif
                        <span style="color: red;"><strong>Document Type Allowed:</strong> Images (all types)<br>
                        <strong>Document Size Allowed:</strong> Maximum 5MB</span>
                    </div>
                    @endif
                    <div class="flex flex-col gap-2 grow">
                        <h2 class="text-2xl font-semibold text-mono">
                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-secondary-foreground">
                                Service Number: <span class="font-semibold text-mono">{{ $officer->service_number ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Rank: <span class="font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Command: <span class="font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('officer.retirement') }}" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-calendar-tick"></i>
                            View Retirement
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Picture Update Required (Post-Promotion) -->
        @if(isset($officer) && $officer && method_exists($officer, 'needsProfilePictureUpdateAfterPromotion') && $officer->needsProfilePictureUpdateAfterPromotion())
            <div class="kt-card" style="background-color: #fee2e2; border: 2px solid #dc3545;">
                <div class="kt-card-content p-4">
                    <div class="flex items-start gap-3">
                        <i class="ki-filled ki-information text-xl" style="color: #dc3545;"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold mb-1" style="color: #b91c1c;">
                                Action Required: Update your profile picture
                            </p>
                            <p class="text-sm" style="color: #dc2626;">
                                Change Profile Picture hasn’t been done yet. You will be unable to raise emolument until you update your profile picture.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Personal Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Personal Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Birth</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_birth ? $officer->date_of_birth->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Sex</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->sex ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Marital Status</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->marital_status ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">State of Origin</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->state_of_origin ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">LGA</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->lga ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Geopolitical Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->geopolitical_zone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Service Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of First Appointment</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date of Present Appointment</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_of_present_appointment ? $officer->date_of_present_appointment->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Substantive Rank</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Salary Grade Level</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->salary_grade_level ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Zone</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->zone->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Command/Present Station</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Date Posted to Station</span>
                            <span class="text-sm font-semibold text-mono">
                                {{ $officer->date_posted_to_station ? $officer->date_posted_to_station->format('d/m/Y') : 'N/A' }}
                            </span>
                        </div>
                        @if($officer->unit)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Unit</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->unit }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Contact Information</h3>
                    <a href="{{ route('officer.settings.contact-details') }}" class="kt-btn kt-btn-secondary kt-btn-sm">
                        Update
                    </a>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Email</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->email ?? $officer->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Phone Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->phone_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Residential Address</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->residential_address ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Permanent Home Address</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->permanent_home_address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Educational & Professional Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Educational & Professional</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @php
                            // Build education array from entry_qualification and additional_qualification
                            $educationEntries = [];
                            
                            // First, try to get all education entries from additional_qualification JSON
                            if ($officer->additional_qualification) {
                                $allEducation = json_decode($officer->additional_qualification, true);
                                if (is_array($allEducation) && count($allEducation) > 0) {
                                    // Check if first entry in JSON matches entry_qualification (new format with all entries)
                                    $firstEntryMatches = isset($allEducation[0]) && 
                                        isset($allEducation[0]['qualification']) && 
                                        $allEducation[0]['qualification'] === $officer->entry_qualification;
                                    
                                    if ($firstEntryMatches) {
                                        // New format: All entries (including first) are in JSON with universities
                                        $educationEntries = $allEducation;
                                    } else {
                                        // Old format: JSON only has entries from index 1, need to prepend first entry
                                        $firstEntry = [
                                            'university' => '', // Not stored in old format
                                            'qualification' => $officer->entry_qualification,
                                            'discipline' => $officer->discipline ?? ''
                                        ];
                                        $educationEntries = array_merge([$firstEntry], $allEducation);
                                    }
                                }
                            }
                            
                            // Fallback: If no JSON data, reconstruct from legacy fields only
                            if (empty($educationEntries) && $officer->entry_qualification) {
                                // First education entry from entry_qualification and discipline (no university available)
                                $educationEntries[] = [
                                    'university' => '', // Not stored in legacy format
                                    'qualification' => $officer->entry_qualification,
                                    'discipline' => $officer->discipline ?? ''
                                ];
                            }
                        @endphp
                        
                        @if(count($educationEntries) > 0)
                            @foreach($educationEntries as $index => $edu)
                                <div class="border-b border-input pb-4 {{ $index > 0 ? 'mt-4' : '' }}">
                                    <div class="text-xs text-secondary-foreground mb-2 font-semibold">Education Entry #{{ $index + 1 }}</div>
                                    @if(!empty($edu['university']))
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-secondary-foreground">Institution</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['university'] }}</span>
                                    </div>
                                    @endif
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-secondary-foreground">Qualification</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['qualification'] ?? 'N/A' }}</span>
                                    </div>
                                    @if(!empty($edu['year_obtained']))
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm text-secondary-foreground">Year Obtained</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['year_obtained'] }}</span>
                                    </div>
                                    @endif
                                    @if(!empty($edu['discipline']))
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-secondary-foreground">Discipline</span>
                                        <span class="text-sm font-semibold text-mono">{{ $edu['discipline'] }}</span>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm text-secondary-foreground">No education information available</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Financial Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Bank Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->bank_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Bank Account Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->bank_account_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Sort Code</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->sort_code ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">PFA Name</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->pfa_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">RSA Number</span>
                            <span class="text-sm font-semibold text-mono">{{ $officer->rsa_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Status Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Status</span>
                            <span class="kt-badge kt-badge-{{ $officer->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                {{ $officer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Interdicted</span>
                            <span class="kt-badge kt-badge-{{ $officer->interdicted ? 'danger' : 'success' }} kt-badge-sm">
                                {{ $officer->interdicted ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Suspended</span>
                            <span class="kt-badge kt-badge-{{ $officer->suspended ? 'danger' : 'success' }} kt-badge-sm">
                                {{ $officer->suspended ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-secondary-foreground">Quartered</span>
                            <span class="kt-badge kt-badge-{{ $officer->quartered ? 'success' : 'secondary' }} kt-badge-sm">
                                {{ $officer->quartered ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next of Kin Information -->
            @php
                $nextOfKin = $officer->nextOfKin ?? collect();
            @endphp
            @if($nextOfKin->count() > 0)
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Next of Kin Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4">
                        @foreach($nextOfKin as $index => $nok)
                            <div class="border-b border-input pb-4 {{ $index > 0 ? 'mt-4' : '' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-xs text-secondary-foreground font-semibold">Next of Kin #{{ $index + 1 }}</div>
                                    @if($nok->is_primary)
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Primary</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Name</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->name }}</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Relationship</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->relationship }}</span>
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Phone Number</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->phone_number }}</span>
                                </div>
                                @if($nok->email)
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-secondary-foreground">Email</span>
                                    <span class="text-sm font-semibold text-mono">{{ $nok->email }}</span>
                                </div>
                                @endif
                                <div class="flex items-start justify-between">
                                    <span class="text-sm text-secondary-foreground">Address</span>
                                    <span class="text-sm font-semibold text-mono text-right max-w-[60%]">{{ $nok->address }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Image Cropper Modal -->
    <div id="image-cropper-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-background rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-mono">Crop Profile Picture</h3>
                    <button id="close-cropper-modal" class="text-secondary-foreground hover:text-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="mb-4">
                    <img id="cropper-image" src="" alt="Crop" style="max-width: 100%; max-height: 400px;">
                </div>
                <div class="flex gap-3 justify-end">
                    <button id="cancel-crop" class="kt-btn kt-btn-dim">Cancel</button>
                    <button id="save-crop" class="kt-btn kt-btn-primary">Save Picture</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const uploadInput = document.getElementById('profile-picture-upload');
                const modal = document.getElementById('image-cropper-modal');
                const cropperImage = document.getElementById('cropper-image');
                const closeModalBtn = document.getElementById('close-cropper-modal');
                const cancelBtn = document.getElementById('cancel-crop');
                const saveBtn = document.getElementById('save-crop');
                let cropper = null;
                let selectedFile = null;

                // Show modal
                function showModal() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                // Hide modal
                function hideModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    cropperImage.src = '';
                    selectedFile = null;
                    if (uploadInput) {
                        uploadInput.value = '';
                    }
                }

                // Close modal handlers
                closeModalBtn.addEventListener('click', hideModal);
                cancelBtn.addEventListener('click', hideModal);

                // Click outside to close
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        hideModal();
                    }
                });

                // Initialize cropper when image is loaded
                function initCropper(file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        cropperImage.src = e.target.result;
                        
                        // Destroy existing cropper if any
                        if (cropper) {
                            cropper.destroy();
                        }

                        // Initialize new cropper
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                    reader.readAsDataURL(file);
                }

                // Handle file selection
                if (uploadInput) {
                    uploadInput.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        if (!file) return;

                        // Validate file type
                        if (!file.type.startsWith('image/')) {
                            alert('Please select an image file.');
                            return;
                        }

                        // Validate file size (max 5MB for cropping, will be compressed after)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Image size must be less than 5MB.');
                            return;
                        }

                        selectedFile = file;
                        initCropper(file);
                        showModal();
                    });
                }

                // Handle save crop
                saveBtn.addEventListener('click', async () => {
                    if (!cropper || !selectedFile) {
                        return;
                    }

                    // Get cropped canvas
                    const canvas = cropper.getCroppedCanvas({
                        width: 400,
                        height: 400,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    // Convert canvas to blob
                    canvas.toBlob(async (blob) => {
                        if (!blob) {
                            alert('Failed to process image.');
                            return;
                        }

                        // Create form data
                        const formData = new FormData();
                        formData.append('profile_picture', blob, selectedFile.name);

                        try {
                            saveBtn.disabled = true;
                            saveBtn.textContent = 'Uploading...';

                            const response = await fetch('{{ route("officer.profile.update-picture") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (response.ok) {
                                // Build URL with cache-busting parameter
                                const separator = data.profile_picture_url.includes('?') ? '&' : '?';
                                const newImageUrl = data.profile_picture_url + separator + '_t=' + Date.now();
                                
                                // Get image elements by ID (same approach for both)
                                const profileImg = document.getElementById('profile-page-picture');
                                const sidebarImg = document.getElementById('sidebar-profile-picture');
                                
                                // Close modal first so images are visible
                                hideModal();
                                
                                // Function to update image immediately (same for both)
                                function updateImageImmediately(imgElement, newUrl) {
                                    if (!imgElement) return;
                                    
                                    // Update src immediately - browser will load it right away
                                    imgElement.src = newUrl;
                                    
                                    // Add loading state visual feedback
                                    imgElement.style.opacity = '0.7';
                                    
                                    imgElement.onload = function() {
                                        this.style.opacity = '1';
                                    };
                                    
                                    imgElement.onerror = function() {
                                        // If error, try reloading with a new timestamp
                                        this.src = newUrl.split('?')[0] + '?_t=' + Date.now();
                                        this.style.opacity = '1';
                                    };
                                }
                                
                                // Update both images using the same function
                                updateImageImmediately(profileImg, newImageUrl);
                                updateImageImmediately(sidebarImg, newImageUrl);
                                
                                // Show success message
                                setTimeout(() => {
                                    alert('Profile picture updated successfully!');
                                }, 200);
                            } else {
                                alert(data.message || 'Failed to update profile picture.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('An error occurred while updating the profile picture.');
                        } finally {
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Save Picture';
                        }
                    }, 'image/jpeg', 0.9);
                });
            });
        </script>
    @endpush
@endsection

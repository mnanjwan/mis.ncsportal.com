@extends('layouts.public')

@section('title', 'Recruit Onboarding - Preview')

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
            <form method="POST" action="{{ route('recruit.onboarding.final-submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="token" value="{{ request('token') ?? session('recruit_onboarding_token') }}">
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
                                    <a href="{{ route('recruit.onboarding.step1', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 1</a>
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
                                    <div><strong>Zone:</strong> {{ $zone->name ?? ($step2['zone_id'] ?? 'N/A') }}</div>
                                    <div><strong>Command/Present Station:</strong> {{ $command->name ?? ($step2['command_id'] ?? 'N/A') }}</div>
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
                                    
                                    @php
                                        $hasStep2Documents = isset($step2['documents']) && is_array($step2['documents']) && count($step2['documents']) > 0;
                                    @endphp
                                    
                                    @if($hasStep2Documents)
                                    <div class="mt-4 pt-4 border-t border-input">
                                        <strong class="block mb-3 text-base">Uploaded Documents ({{ count($step2['documents']) }})</strong>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                            @foreach($step2['documents'] as $index => $doc)
                                            <div class="flex flex-col items-center p-3 border border-input rounded-lg bg-muted/20 hover:bg-muted/40 transition-colors">
                                                @php
                                                    $isImage = isset($doc['type']) && str_starts_with($doc['type'], 'image/');
                                                    $imageSrc = null;
                                                    
                                                    if ($isImage && isset($doc['temp_path'])) {
                                                        // Check if file exists using Storage
                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($doc['temp_path'])) {
                                                            try {
                                                                // Use Storage facade to read file
                                                                $fileContent = \Illuminate\Support\Facades\Storage::disk('local')->get($doc['temp_path']);
                                                                if ($fileContent) {
                                                                    $imageData = base64_encode($fileContent);
                                                                    $imageSrc = 'data:' . $doc['type'] . ';base64,' . $imageData;
                                                                }
                                                            } catch (\Exception $e) {
                                                                \Log::error('Error reading step2 document file via Storage', [
                                                                    'temp_path' => $doc['temp_path'] ?? 'N/A',
                                                                    'error' => $e->getMessage(),
                                                                ]);
                                                                // Fallback: try direct file read
                                                                try {
                                                                    $filePath = storage_path('app/' . $doc['temp_path']);
                                                                    if (file_exists($filePath)) {
                                                                        $imageData = base64_encode(file_get_contents($filePath));
                                                                        $imageSrc = 'data:' . $doc['type'] . ';base64,' . $imageData;
                                                                    }
                                                                } catch (\Exception $e2) {
                                                                    \Log::error('Fallback file read also failed for step2', [
                                                                        'file' => $filePath ?? 'N/A',
                                                                        'error' => $e2->getMessage(),
                                                                    ]);
                                                                }
                                                            }
                                                        } else {
                                                            \Log::warning('Step2 document file not found in storage', [
                                                                'temp_path' => $doc['temp_path'] ?? 'N/A',
                                                            ]);
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($imageSrc)
                                                <img src="{{ $imageSrc }}" 
                                                     alt="{{ $doc['name'] ?? 'Document' }}" 
                                                     class="w-full h-32 object-cover rounded mb-2 cursor-pointer hover:opacity-80 transition-opacity border border-input document-thumbnail"
                                                     data-image-src="{{ htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') }}"
                                                     data-file-name="{{ htmlspecialchars($doc['name'] ?? 'Document', ENT_QUOTES, 'UTF-8') }}"
                                                     title="Click to zoom">
                                                @else
                                                <div class="w-full h-32 flex items-center justify-center bg-muted rounded mb-2 border border-input cursor-pointer hover:bg-muted/60 transition-colors document-placeholder"
                                                     data-file-name="{{ htmlspecialchars($doc['name'] ?? 'Document', ENT_QUOTES, 'UTF-8') }}"
                                                     data-file-path="{{ htmlspecialchars($doc['temp_path'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                     title="Click to view">
                                                    <i class="ki-filled ki-file text-primary text-3xl"></i>
                                                </div>
                                                @endif
                                                
                                                <div class="w-full text-center">
                                                    <span class="text-xs font-medium truncate block w-full" title="{{ $doc['name'] ?? 'Document' }}">
                                                        {{ $doc['name'] ?? 'Document' }}
                                                    </span>
                                                    <span class="text-xs text-muted mt-1 block">
                                                        {{ isset($doc['size']) ? number_format($doc['size'] / 1024, 1) : '0' }} KB
                                                    </span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('recruit.onboarding.step2', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 2</a>
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
                                    <a href="{{ route('recruit.onboarding.step3', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 3</a>
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
                                
                                @php
                                    $hasDocuments = isset($step4['documents']) && is_array($step4['documents']) && count($step4['documents']) > 0;
                                @endphp
                                
                                @if($hasDocuments)
                                <div class="mt-4 pt-4 border-t border-input">
                                    <strong class="block mb-3 text-base">Uploaded Documents ({{ count($step4['documents']) }})</strong>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                        @foreach($step4['documents'] as $index => $doc)
                                        <div class="flex flex-col items-center p-3 border border-input rounded-lg bg-muted/20 hover:bg-muted/40 transition-colors">
                                            @php
                                                $isImage = isset($doc['type']) && str_starts_with($doc['type'], 'image/');
                                                $imageSrc = null;
                                                
                                                if ($isImage && isset($doc['temp_path'])) {
                                                    // Check if file exists using Storage
                                                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($doc['temp_path'])) {
                                                        try {
                                                            // Use Storage facade to read file
                                                            $fileContent = \Illuminate\Support\Facades\Storage::disk('local')->get($doc['temp_path']);
                                                            if ($fileContent) {
                                                                $imageData = base64_encode($fileContent);
                                                                $imageSrc = 'data:' . $doc['type'] . ';base64,' . $imageData;
                                                            }
                                                        } catch (\Exception $e) {
                                                            \Log::error('Error reading document file via Storage', [
                                                                'temp_path' => $doc['temp_path'] ?? 'N/A',
                                                                'error' => $e->getMessage(),
                                                            ]);
                                                            // Fallback: try direct file read
                                                            try {
                                                                $filePath = storage_path('app/' . $doc['temp_path']);
                                                                if (file_exists($filePath)) {
                                                                    $imageData = base64_encode(file_get_contents($filePath));
                                                                    $imageSrc = 'data:' . $doc['type'] . ';base64,' . $imageData;
                                                                }
                                                            } catch (\Exception $e2) {
                                                                \Log::error('Fallback file read also failed', [
                                                                    'file' => $filePath ?? 'N/A',
                                                                    'error' => $e2->getMessage(),
                                                                ]);
                                                            }
                                                        }
                                                    } else {
                                                        \Log::warning('Document file not found in storage', [
                                                            'temp_path' => $doc['temp_path'] ?? 'N/A',
                                                        ]);
                                                    }
                                                }
                                            @endphp
                                            
                                            @if($imageSrc)
                                            <img src="{{ $imageSrc }}" 
                                                 alt="{{ $doc['name'] ?? 'Document' }}" 
                                                 class="w-full h-32 object-cover rounded mb-2 cursor-pointer hover:opacity-80 transition-opacity border border-input document-thumbnail"
                                                 data-image-src="{{ htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') }}"
                                                 data-file-name="{{ htmlspecialchars($doc['name'] ?? 'Document', ENT_QUOTES, 'UTF-8') }}"
                                                 title="Click to zoom">
                                            @else
                                            <div class="w-full h-32 flex items-center justify-center bg-muted rounded mb-2 border border-input cursor-pointer hover:bg-muted/60 transition-colors document-placeholder"
                                                 data-file-name="{{ htmlspecialchars($doc['name'] ?? 'Document', ENT_QUOTES, 'UTF-8') }}"
                                                 data-file-path="{{ htmlspecialchars($doc['temp_path'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                 title="Click to view">
                                                <i class="ki-filled ki-file text-primary text-3xl"></i>
                                            </div>
                                            @endif
                                            
                                            <div class="w-full text-center">
                                                <span class="text-xs font-medium truncate block w-full" title="{{ $doc['name'] ?? 'Document' }}">
                                                    {{ $doc['name'] ?? 'Document' }}
                                                </span>
                                                <span class="text-xs text-muted mt-1 block">
                                                    {{ isset($doc['size']) ? number_format($doc['size'] / 1024, 1) : '0' }} KB
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <div class="mt-4 pt-4 border-t border-input">
                                    <div class="text-sm text-muted">
                                        <i class="ki-filled ki-information"></i> No documents uploaded
                                    </div>
                                    @if(config('app.debug'))
                                    <div class="mt-2 text-xs text-muted p-2 bg-muted/20 rounded">
                                        <strong>Debug:</strong> step4['documents'] = {{ isset($step4['documents']) ? 'exists (' . (is_array($step4['documents']) ? count($step4['documents']) : 'not array') . ')' : 'not set' }}
                                    </div>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="mt-4">
                                    <a href="{{ route('recruit.onboarding.step4', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-sm kt-btn-secondary">Edit Step 4</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between gap-3 pt-5 mt-5 border-t border-input">
                    <a href="{{ route('recruit.onboarding.step4', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-secondary">Back to Edit</a>
                    <button type="submit" id="final-submit-btn" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">
                        <i class="ki-filled ki-check" style="color: white;"></i> Submit for Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Zoom Modal -->
<div id="document-modal" onclick="closeDocumentModal(event)">
    <div class="modal-content">
        <button class="modal-close" onclick="closeDocumentModal(event)">&times;</button>
        <img id="modal-image" class="modal-image" src="" alt="" onclick="event.stopPropagation()">
        <div id="modal-title" class="modal-title"></div>
    </div>
</div>

@push('styles')
<style>
    #document-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.95);
        overflow: auto;
    }
    
    #document-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        position: relative;
        max-width: 95%;
        max-height: 95%;
        margin: auto;
        background-color: transparent;
        text-align: center;
    }
    
    .modal-image {
        max-width: 100%;
        max-height: 85vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }
    
    .modal-close {
        position: absolute;
        top: -50px;
        right: 0;
        color: #fff;
        font-size: 45px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: opacity 0.2s;
    }
    
    .modal-close:hover {
        opacity: 0.7;
    }
    
    .modal-title {
        color: #fff;
        text-align: center;
        margin-top: 20px;
        font-size: 18px;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
function showDocumentModal(imageSrc, fileName, filePath = null) {
    const modal = document.getElementById('document-modal');
    const modalImage = document.getElementById('modal-image');
    const modalTitle = document.getElementById('modal-title');
    
    if (imageSrc) {
        modalImage.src = imageSrc;
        modalImage.style.display = 'block';
        modalTitle.textContent = fileName;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    } else if (filePath) {
        // For non-image files, show file name
        modalTitle.textContent = fileName + ' (Non-image file - cannot preview)';
        modalImage.style.display = 'none';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeDocumentModal(event) {
    // Only close if clicking the close button or the background (not the image itself)
    if (event.target.id === 'document-modal' || event.target.classList.contains('modal-close')) {
        const modal = document.getElementById('document-modal');
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('document-modal');
        if (modal && modal.classList.contains('show')) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle clicks on document thumbnails
    document.querySelectorAll('.document-thumbnail').forEach(function(img) {
        img.addEventListener('click', function() {
            const imageSrc = this.getAttribute('data-image-src');
            const fileName = this.getAttribute('data-file-name');
            showDocumentModal(imageSrc, fileName);
        });
    });
    
    // Handle clicks on document placeholders
    document.querySelectorAll('.document-placeholder').forEach(function(div) {
        div.addEventListener('click', function() {
            const fileName = this.getAttribute('data-file-name');
            const filePath = this.getAttribute('data-file-path');
            showDocumentModal(null, fileName, filePath);
        });
    });
    
    // Form submission handler
    const form = document.querySelector('form[action="{{ route('recruit.onboarding.final-submit') }}"]');
    const submitBtn = document.getElementById('final-submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog using SweetAlert2
            Swal.fire({
                title: 'Submit Application?',
                text: 'Are you sure this information is correct as any wrong information may lead to termination of appointment and possible prosecution for forgery',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit Application',
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

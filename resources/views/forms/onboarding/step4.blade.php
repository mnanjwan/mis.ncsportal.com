@extends('layouts.app')

@section('title', 'Onboarding - Step 4: Next of Kin')
@section('page-title', 'Onboarding - Step 4: Next of Kin')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-4 lg:p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-2">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold text-sm">✓</div>
                    <span class="text-xs sm:text-sm text-success">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold text-sm">✓</div>
                    <span class="text-xs sm:text-sm text-success">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold text-sm">✓</div>
                    <span class="text-xs sm:text-sm text-success">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm">4</div>
                    <span class="text-xs sm:text-sm font-medium">Next of Kin</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Next of Kin Information</h3>
        </div>
        <div class="kt-card-content">
            @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <div class="kt-alert-content">
                    <strong class="text-danger">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="onboarding-step4-form" method="POST" action="{{ route('onboarding.submit') }}" enctype="multipart/form-data" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Left Column: Form Content -->
                    <div class="lg:col-span-2 flex flex-col gap-5 order-2 lg:order-1">
                        <!-- Next of Kin Section -->
                <div class="flex flex-col gap-5 pt-5 border-t border-input">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">Next of Kin Information</h3>
                            <p class="text-sm text-muted mt-1">You can add up to 5 next of kin. At least one must be marked as primary.</p>
                        </div>
                        <button type="button" id="add-nok-btn" class="kt-btn kt-btn-sm text-white w-full sm:w-auto" style="background-color: #068b57; border-color: #068b57;">
                            <i class="ki-filled ki-plus" style="color: white;"></i> Add Next of Kin
                        </button>
                    </div>
                    
                    <div id="nok-entries" class="flex flex-col gap-5">
                        <!-- Next of kin entries will be added here dynamically -->
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 pt-5 border-t border-input">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="interdicted" id="interdicted" value="1" {{ old('interdicted', $savedData['interdicted'] ?? false) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="interdicted" class="kt-form-label">Interdicted</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="suspended" id="suspended" value="1" {{ old('suspended', $savedData['suspended'] ?? false) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="suspended" class="kt-form-label">Suspended</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="quartered" id="quartered" value="1" {{ old('quartered', $savedData['quartered'] ?? false) ? 'checked' : '' }} class="kt-checkbox"/>
                        <label for="quartered" class="kt-form-label">Quartered</label>
                    </div>
                </div>
                
                <div class="flex flex-col gap-1 pt-5 border-t border-input">
                    <label class="kt-form-label">Upload Documents <span class="text-muted">(Preferably in JPEG format)</span></label>
                    <input type="file" name="documents[]" class="kt-input" multiple accept="image/jpeg,image/jpg,image/png"/>
                    <small class="text-muted">You can upload multiple documents. JPEG format is preferred to save space.</small>
                </div>
                
                <div class="kt-alert kt-alert-warning mt-5">
                    <div class="kt-alert-content">
                        <strong>Important Notice:</strong> Any false information provided by you can lead to Dismissal for Forgery under the PSR Rules.
                    </div>
                </div>
                
                <div class="flex items-center gap-2 pt-5 border-t border-input">
                    <input type="checkbox" name="accept_disclaimer" id="accept_disclaimer" value="1" required class="kt-checkbox"/>
                    <label for="accept_disclaimer" class="kt-form-label">I accept the disclaimer and confirm that all information provided is true and accurate <span class="text-danger">*</span></label>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step3') }}'" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</button>
                    <button type="submit" class="kt-btn text-white w-full sm:flex-1 whitespace-nowrap" id="submit-btn" style="background-color: #068b57; border-color: #068b57;">Submit Onboarding</button>
                </div>
                    </div>
                    
                    <!-- Right Column: Profile Photo (Passport Style) -->
                    <div class="lg:col-span-1 order-1 lg:order-2">
                        <div class="kt-card lg:sticky lg:top-5">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Profile Photo <span class="text-danger">*</span></h3>
                            </div>
                            <div class="kt-card-content p-4 lg:p-5">
                                <div class="flex flex-col items-center gap-4">
                                    <!-- Passport-style photo frame -->
                                    <div class="relative bg-white rounded-lg p-3 lg:p-4 shadow-lg w-full max-w-[200px] mx-auto" style="aspect-ratio: 4/5; border: 4px solid #068b57;">
                                        <div class="w-full h-full flex items-center justify-center bg-muted/10 rounded overflow-hidden" style="position: relative;">
                                            <!-- User icon instead of image -->
                                            <div id="onboarding-profile-picture-icon" class="flex items-center justify-center w-full h-full">
                                                <i class="ki-filled ki-user" style="font-size: 80px; color: #068b57;"></i>
                                            </div>
                                            <!-- Image preview (hidden by default, shown when image is uploaded) -->
                                            <img id="onboarding-profile-picture" 
                                                 alt="Profile Photo"
                                                 class="hidden"
                                                 style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;"
                                                 src="{{ old('profile_picture_preview', $savedData['profile_picture_preview'] ?? '') }}" />
                                        </div>
                                        <!-- Passport photo label -->
                                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 text-white text-xs px-3 py-1 rounded whitespace-nowrap" style="background-color: #068b57;">
                                            Official Passport Photo
                                        </div>
                                    </div>
                                    
                                    <!-- Upload button -->
                                    <label for="onboarding-profile-picture-upload" class="kt-btn w-full cursor-pointer text-white" style="background-color: #068b57; border-color: #068b57;">
                                        <i class="ki-filled ki-camera" style="color: white;"></i>
                                        Upload Photo
                                    </label>
                                    <input type="file" id="onboarding-profile-picture-upload" class="hidden" accept="image/*">
                                    <input type="hidden" name="profile_picture_data" id="profile_picture_data" required>
                                    
                                    <!-- Error message for profile picture -->
                                    <span id="profile_picture_error" class="error-message text-danger text-sm font-medium hidden" style="color: #dc3545 !important;"></span>
                                    
                                    <p class="text-xs text-center text-muted mt-2">
                                        <span class="text-danger font-semibold">Required</span><br>
                                        Recommended: 2x2 inches<br>
                                        White background<br>
                                        Max size: 2MB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if(session('error'))
                <div class="kt-alert kt-alert-danger mt-3">
                    <div class="kt-alert-content text-danger">{{ session('error') }}</div>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Image Cropper Modal -->
<div id="onboarding-image-cropper-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-background rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mono">Crop Profile Picture</h3>
                <button id="onboarding-close-cropper-modal" class="text-secondary-foreground hover:text-foreground">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <img id="onboarding-cropper-image" src="" alt="Crop" style="max-width: 100%; max-height: 400px;">
            </div>
            <div class="flex gap-3 justify-end">
                <button id="onboarding-cancel-crop" class="kt-btn kt-btn-dim">Cancel</button>
                    <button id="onboarding-save-crop" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">Save Picture</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <style>
        /* Ensure all asterisks in onboarding forms are red */
        .kt-form-label span.text-danger,
        .kt-form-label .text-danger,
        label span.text-danger,
        label .text-danger,
        .kt-card-title span.text-danger,
        .kt-card-title .text-danger {
            color: #dc3545 !important;
        }
        
        /* Override icon colors to use green (#068b57) instead of blue (#2b80ff) */
        /* Icons outside buttons */
        .ki-filled:not(.kt-btn i):not(.kt-btn-primary i):not(button i):not(label.kt-btn i),
        .ki-outline:not(.kt-btn i):not(.kt-btn-primary i):not(button i):not(label.kt-btn i),
        i[class*="ki-"]:not(.kt-btn i):not(.kt-btn-primary i):not(button i):not(label.kt-btn i) {
            color: #068b57 !important;
        }
        
        /* Icons inside buttons should remain white */
        .kt-btn i,
        .kt-btn-primary i,
        button i,
        label.kt-btn i {
            color: white !important;
        }
        
        /* Buttons with icons should have green background */
        .kt-btn-primary,
        button.kt-btn-primary,
        label.kt-btn-primary {
            background-color: #068b57 !important;
            border-color: #068b57 !important;
        }
        
        .kt-btn-primary:hover {
            background-color: #057a4d !important;
            border-color: #057a4d !important;
        }
        
        /* Primary colored elements */
        .bg-primary {
            background-color: #068b57 !important;
        }
        
        .border-primary {
            border-color: #068b57 !important;
        }
        
        .text-primary {
            color: #068b57 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
let nokEntryCount = 0;
const MAX_NOK_ENTRIES = 5;

// Relationships list
const relationships = [
    'Spouse',
    'Father',
    'Mother',
    'Parent',
    'Brother',
    'Sister',
    'Son',
    'Daughter',
    'Child',
    'Uncle',
    'Aunt',
    'Cousin',
    'Nephew',
    'Niece',
    'Grandfather',
    'Grandmother',
    'Grandson',
    'Granddaughter',
    'Father-in-law',
    'Mother-in-law',
    'Brother-in-law',
    'Sister-in-law',
    'Son-in-law',
    'Daughter-in-law',
    'Guardian',
    'Other'
];

// Initialize next of kin section
function initializeNOKSection() {
    const addBtn = document.getElementById('add-nok-btn');
    const entriesContainer = document.getElementById('nok-entries');
    
    // Load saved next of kin entries
    const savedNOK = @json(old('next_of_kin', $savedData['next_of_kin'] ?? []));
    
    if (savedNOK && Array.isArray(savedNOK) && savedNOK.length > 0) {
        savedNOK.forEach(nok => {
            if (nok && (nok.name || nok.next_of_kin_name)) {
                addNOKEntry(nok);
            }
        });
    }
    
    // If no saved entries, add one empty entry by default
    if (entriesContainer.children.length === 0) {
        addNOKEntry();
    }
    
    addBtn.addEventListener('click', () => {
        const currentCount = document.querySelectorAll('#nok-entries .kt-card').length;
        if (currentCount >= MAX_NOK_ENTRIES) {
            alert(`You can only add up to ${MAX_NOK_ENTRIES} next of kin entries.`);
            return;
        }
        addNOKEntry();
    });
}

function addNOKEntry(data = null) {
    const entriesContainer = document.getElementById('nok-entries');
    const entryId = nokEntryCount++;
    
    const entryDiv = document.createElement('div');
    entryDiv.className = 'kt-card p-5 border border-input rounded-lg';
    entryDiv.dataset.entryId = entryId;
    
    const savedName = data && (data.name || data.next_of_kin_name) ? (data.name || data.next_of_kin_name) : '';
    const savedRelationship = data && data.relationship ? data.relationship : '';
    const savedPhone = data && (data.phone_number || data.next_of_kin_phone) ? (data.phone_number || data.next_of_kin_phone) : '';
    const savedEmail = data && (data.email || data.next_of_kin_email) ? (data.email || data.next_of_kin_email) : '';
    const savedAddress = data && data.address ? data.address : '';
    const isPrimary = data && (data.is_primary === true || data.is_primary === '1' || data.is_primary === 1);
    
    entryDiv.innerHTML = `
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 pb-4 border-b border-input">
            <h4 class="text-md font-semibold">Next of Kin #${entryId + 1}</h4>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" 
                           name="primary_nok" 
                           value="${entryId}"
                           class="kt-radio primary-nok-radio"
                           ${isPrimary ? 'checked' : ''}
                           onchange="handlePrimaryChange()">
                    <span class="text-sm font-medium">Primary</span>
                </label>
                <button type="button" 
                        class="kt-btn kt-btn-sm kt-btn-danger remove-nok-btn w-full sm:w-auto" 
                        onclick="removeNOKEntry(${entryId})">
                    <i class="ki-filled ki-trash"></i> Remove
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Name(s) of Next of KIN <span class="text-danger">*</span></label>
                <input type="text" 
                       name="next_of_kin[${entryId}][name]" 
                       class="kt-input nok-name" 
                       value="${savedName}"
                       required/>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Relationship <span class="text-danger">*</span></label>
                <select name="next_of_kin[${entryId}][relationship]" class="kt-input nok-relationship" required>
                    <option value="">Select...</option>
                    ${relationships.map(rel => 
                        `<option value="${rel}" ${savedRelationship == rel ? 'selected' : ''}>${rel}</option>`
                    ).join('')}
                </select>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" 
                       name="next_of_kin[${entryId}][phone_number]" 
                       class="kt-input nok-phone" 
                       value="${savedPhone}"
                       required/>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
            <div class="flex flex-col gap-1">
                <label class="kt-form-label">Email Address</label>
                <input type="email" 
                       name="next_of_kin[${entryId}][email]" 
                       class="kt-input nok-email" 
                       value="${savedEmail}"/>
                <span class="error-message text-danger text-sm hidden"></span>
            </div>
        </div>
        <div class="flex flex-col gap-1 mt-5">
            <label class="kt-form-label">Address <span class="text-danger">*</span></label>
            <textarea name="next_of_kin[${entryId}][address]" 
                      class="kt-input nok-address" 
                      rows="3" 
                      required>${savedAddress}</textarea>
            <span class="error-message text-danger text-sm hidden"></span>
        </div>
        <input type="hidden" 
               name="next_of_kin[${entryId}][is_primary]" 
               class="nok-is-primary"
               value="${isPrimary ? '1' : '0'}">
    `;
    
    entriesContainer.appendChild(entryDiv);
    
    // Update primary status on radio change
    const primaryRadio = entryDiv.querySelector('.primary-nok-radio');
    primaryRadio.addEventListener('change', function() {
        handlePrimaryChange();
    });
}

function removeNOKEntry(entryId) {
    const entry = document.querySelector(`[data-entry-id="${entryId}"]`);
    if (entry) {
        // Check if this is the primary entry
        const isPrimary = entry.querySelector('.primary-nok-radio')?.checked;
        entry.remove();
        
        // If it was primary and there are other entries, make the first one primary
        if (isPrimary) {
            const remainingEntries = document.querySelectorAll('#nok-entries .kt-card');
            if (remainingEntries.length > 0) {
                const firstRadio = remainingEntries[0].querySelector('.primary-nok-radio');
                if (firstRadio) {
                    firstRadio.checked = true;
                    handlePrimaryChange();
                }
            }
        }
        
        // Renumber entries
        renumberNOKEntries();
    }
    
    // If no entries left, add one
    const entriesContainer = document.getElementById('nok-entries');
    if (entriesContainer.children.length === 0) {
        addNOKEntry();
    }
}

function renumberNOKEntries() {
    const entries = document.querySelectorAll('#nok-entries .kt-card');
    entries.forEach((entry, index) => {
        const title = entry.querySelector('h4');
        if (title) {
            title.textContent = `Next of Kin #${index + 1}`;
        }
    });
}

function handlePrimaryChange() {
    const primaryRadios = document.querySelectorAll('.primary-nok-radio');
    primaryRadios.forEach((radio, index) => {
        const entry = radio.closest('.kt-card');
        const hiddenInput = entry?.querySelector('.nok-is-primary');
        if (radio.checked) {
            if (hiddenInput) {
                hiddenInput.value = '1';
            }
        } else {
            if (hiddenInput) {
                hiddenInput.value = '0';
            }
        }
    });
}

// Validation functions
function showError(field, message) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
        input?.classList.add('border-danger');
    }
}

function clearError(field) {
    const input = document.querySelector(`[name="${field}"]`);
    const errorSpan = input?.parentElement?.querySelector('.error-message');
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.add('hidden');
        input?.classList.remove('border-danger');
    }
}

function validateStep4() {
    let isValid = true;
    
    // Validate profile picture
    const profilePictureData = document.getElementById('profile_picture_data');
    const profilePictureError = document.getElementById('profile_picture_error');
    
    if (!profilePictureData || !profilePictureData.value || !profilePictureData.value.trim()) {
        if (profilePictureError) {
            profilePictureError.textContent = 'Please upload your official passport photo before proceeding.';
            profilePictureError.classList.remove('hidden');
            profilePictureError.style.color = '#dc3545';
        }
        isValid = false;
        // Scroll to profile photo section
        const profileSection = document.querySelector('.kt-card.sticky');
        if (profileSection) {
            profileSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    } else {
        if (profilePictureError) {
            profilePictureError.textContent = '';
            profilePictureError.classList.add('hidden');
        }
    }
    
    // Validate next of kin entries
    const nokCards = document.querySelectorAll('#nok-entries .kt-card');
    
    if (nokCards.length === 0) {
        alert('Please add at least one next of kin.');
        isValid = false;
        return false;
    }
    
    // Check if at least one is marked as primary
    const primaryRadios = document.querySelectorAll('.primary-nok-radio:checked');
    if (primaryRadios.length === 0) {
        alert('Please mark at least one next of kin as primary.');
        isValid = false;
        return false;
    }
    
    // Validate each next of kin entry
    nokCards.forEach((card, index) => {
        const entryId = card.dataset.entryId;
        const name = card.querySelector(`.nok-name`);
        const relationship = card.querySelector(`.nok-relationship`);
        const phone = card.querySelector(`.nok-phone`);
        const address = card.querySelector(`.nok-address`);
        const email = card.querySelector(`.nok-email`);
        
        // Validate name
        if (!name || !name.value.trim()) {
            const errorSpan = name?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Name is required';
                errorSpan.classList.remove('hidden');
                name?.classList.add('border-danger');
            }
            isValid = false;
        }
        
        // Validate relationship
        if (!relationship || !relationship.value.trim()) {
            const errorSpan = relationship?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Relationship is required';
                errorSpan.classList.remove('hidden');
                relationship?.classList.add('border-danger');
            }
            isValid = false;
        }
        
        // Validate phone
        if (!phone || !phone.value.trim()) {
            const errorSpan = phone?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Phone number is required';
                errorSpan.classList.remove('hidden');
                phone?.classList.add('border-danger');
            }
            isValid = false;
        } else if (phone.value.trim() && !/^[0-9+\-\s()]+$/.test(phone.value.trim())) {
            const errorSpan = phone?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Please enter a valid phone number';
                errorSpan.classList.remove('hidden');
                phone?.classList.add('border-danger');
            }
        isValid = false;
    }

        // Validate address
        if (!address || !address.value.trim()) {
            const errorSpan = address?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Address is required';
                errorSpan.classList.remove('hidden');
                address?.classList.add('border-danger');
            }
        isValid = false;
    }
        
        // Validate email format if provided
        if (email && email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            const errorSpan = email?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = 'Please enter a valid email address';
                errorSpan.classList.remove('hidden');
                email?.classList.add('border-danger');
            }
            isValid = false;
        }
    });

    // Validate disclaimer acceptance
    const acceptDisclaimer = document.getElementById('accept_disclaimer');
    if (!acceptDisclaimer.checked) {
        const disclaimerLabel = acceptDisclaimer.parentElement;
        if (disclaimerLabel) {
            disclaimerLabel.style.border = '1px solid #dc3545';
            disclaimerLabel.style.borderRadius = '4px';
            disclaimerLabel.style.padding = '8px';
        }
        isValid = false;
    } else {
        const disclaimerLabel = acceptDisclaimer.parentElement;
        if (disclaimerLabel) {
            disclaimerLabel.style.border = '';
            disclaimerLabel.style.padding = '';
        }
    }

    return isValid;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeNOKSection();
    initializeProfilePictureUpload();
});

// Profile Picture Upload Functionality
function initializeProfilePictureUpload() {
    const uploadInput = document.getElementById('onboarding-profile-picture-upload');
    const modal = document.getElementById('onboarding-image-cropper-modal');
    const cropperImage = document.getElementById('onboarding-cropper-image');
    const closeModalBtn = document.getElementById('onboarding-close-cropper-modal');
    const cancelBtn = document.getElementById('onboarding-cancel-crop');
    const saveBtn = document.getElementById('onboarding-save-crop');
    const profileImg = document.getElementById('onboarding-profile-picture');
    const profileIcon = document.getElementById('onboarding-profile-picture-icon');
    const profilePictureData = document.getElementById('profile_picture_data');
    let cropper = null;
    let selectedFile = null;
    
    // Check if there's a saved image on load
    if (profileImg) {
        const imgSrc = profileImg.getAttribute('src');
        // If there's a valid image source (not default avatar and not empty)
        if (imgSrc && imgSrc.trim() !== '' && !imgSrc.includes('300-1.png') && !imgSrc.includes('data:image') && imgSrc !== window.location.href) {
            profileImg.classList.remove('hidden');
            if (profileIcon) profileIcon.classList.add('hidden');
        } else if (profileImg.src && profileImg.src.includes('data:image')) {
            // If there's base64 data, show the image
            profileImg.classList.remove('hidden');
            if (profileIcon) profileIcon.classList.add('hidden');
        } else {
            // Otherwise show the icon
            profileImg.classList.add('hidden');
            if (profileIcon) profileIcon.classList.remove('hidden');
        }
    }

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
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

    // Click outside to close
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal();
            }
        });
    }

    // Initialize cropper when image is loaded
    function initCropper(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            cropperImage.src = e.target.result;
            
            // Destroy existing cropper if any
            if (cropper) {
                cropper.destroy();
            }

                        // Initialize new cropper with passport photo aspect ratio (2:2.5 or 4:5)
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: 0.8, // 2:2.5 aspect ratio (width:height)
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
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            if (!cropper || !selectedFile) {
                return;
            }

            // Get cropped canvas - maintain passport photo aspect ratio (2x2.5 or 4:5)
            // Standard passport photo is typically 2x2 inches or 51x51mm, but we'll use 2x2.5 for the frame
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 500, // 2:2.5 aspect ratio to match the frame
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Convert canvas to blob and then to base64
            canvas.toBlob((blob) => {
                if (!blob) {
                    alert('Failed to process image.');
                    return;
                }

            // Convert blob to base64 for storage in hidden field
            const reader = new FileReader();
            reader.onload = function() {
                const base64 = reader.result;
                profilePictureData.value = base64;
                
                // Hide icon and show image
                if (profileIcon) profileIcon.classList.add('hidden');
                profileImg.classList.remove('hidden');
                
                // Clear any error message
                const profilePictureError = document.getElementById('profile_picture_error');
                if (profilePictureError) {
                    profilePictureError.textContent = '';
                    profilePictureError.classList.add('hidden');
                }
                
                // Update preview image with fade effect
                profileImg.style.opacity = '0.5';
                profileImg.src = base64;
                profileImg.onload = function() {
                    this.style.opacity = '1';
                };
                
                // Close modal
                hideModal();
            };
            reader.readAsDataURL(blob);
            }, 'image/jpeg', 0.9);
        });
    }
}

// Form submission handler
document.getElementById('onboarding-step4-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateStep4()) {
        const firstError = document.querySelector('.error-message:not(.hidden)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (!document.getElementById('accept_disclaimer').checked) {
            document.getElementById('accept_disclaimer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    // Show confirmation dialog using SweetAlert2
    Swal.fire({
        title: 'Confirm Submission',
        text: 'Are you sure you want to submit your onboarding information? Please review all your details before proceeding. You will be able to review everything on the next page before final submission.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#068b57',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Submitting...';
    
            // Submit the form
            document.getElementById('onboarding-step4-form').submit();
        }
    });
});

// Clear errors on input - use event delegation for dynamically added fields
document.getElementById('onboarding-step4-form').addEventListener('input', function(e) {
    if (e.target.matches('.nok-name, .nok-relationship, .nok-phone, .nok-email, .nok-address')) {
        const errorSpan = e.target.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            e.target.classList.remove('border-danger');
        }
    }
});

document.getElementById('onboarding-step4-form').addEventListener('change', function(e) {
    if (e.target.matches('.nok-name, .nok-relationship, .nok-phone, .nok-email, .nok-address')) {
        const errorSpan = e.target.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            e.target.classList.remove('border-danger');
        }
    }
});

// Clear disclaimer error on check
document.getElementById('accept_disclaimer').addEventListener('change', function() {
    if (this.checked) {
        const disclaimerLabel = this.parentElement;
        if (disclaimerLabel) {
            disclaimerLabel.style.border = '';
            disclaimerLabel.style.padding = '';
        }
    }
});
</script>
@endpush
@endsection



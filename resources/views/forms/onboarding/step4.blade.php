@extends('layouts.app')

@section('title', 'Onboarding - Step 4: Next of Kin')
@section('page-title', 'Onboarding - Step 4: Next of Kin')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Progress Indicator -->
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold">✓</div>
                    <span class="text-sm text-success">Personal Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold">✓</div>
                    <span class="text-sm text-success">Employment Details</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-success text-white flex items-center justify-center font-semibold">✓</div>
                    <span class="text-sm text-success">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold">4</div>
                    <span class="text-sm font-medium">Next of Kin</span>
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
            <form id="onboarding-step4-form" method="POST" action="{{ route('onboarding.submit') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Name(s) of Next of KIN <span class="text-danger">*</span></label>
                        <input type="text" name="next_of_kin_name" class="kt-input" value="{{ old('next_of_kin_name', $savedData['next_of_kin_name'] ?? '') }}" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Relationship <span class="text-danger">*</span></label>
                        <select name="relationship" class="kt-input" required>
                            <option value="">Select...</option>
                            <option value="Spouse" {{ old('relationship', $savedData['relationship'] ?? '') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                            <option value="Parent" {{ old('relationship', $savedData['relationship'] ?? '') == 'Parent' ? 'selected' : '' }}>Parent</option>
                            <option value="Sibling" {{ old('relationship', $savedData['relationship'] ?? '') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                            <option value="Child" {{ old('relationship', $savedData['relationship'] ?? '') == 'Child' ? 'selected' : '' }}>Child</option>
                            <option value="Other" {{ old('relationship', $savedData['relationship'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="next_of_kin_phone" class="kt-input" value="{{ old('next_of_kin_phone', $savedData['next_of_kin_phone'] ?? '') }}" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Email Address</label>
                        <input type="email" name="next_of_kin_email" class="kt-input" value="{{ old('next_of_kin_email', $savedData['next_of_kin_email'] ?? '') }}"/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                </div>
                
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label">Address <span class="text-danger">*</span></label>
                    <textarea name="next_of_kin_address" class="kt-input" rows="3" required>{{ old('next_of_kin_address', $savedData['next_of_kin_address'] ?? '') }}</textarea>
                    <span class="error-message text-danger text-sm hidden"></span>
                </div>
                
                <div class="grid lg:grid-cols-3 gap-5 pt-5 border-t border-input">
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
                
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step3') }}'" class="kt-btn kt-btn-secondary">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary" id="submit-btn">Submit Onboarding</button>
                </div>
                
                @if(session('error'))
                <div class="kt-alert kt-alert-danger mt-3">
                    <div class="kt-alert-content">{{ session('error') }}</div>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
    
    const requiredFields = {
        'next_of_kin_name': 'Next of Kin Name is required',
        'relationship': 'Relationship is required',
        'next_of_kin_phone': 'Next of Kin Phone Number is required',
        'next_of_kin_address': 'Next of Kin Address is required'
    };

    // Clear all errors first
    Object.keys(requiredFields).forEach(field => clearError(field));

    // Validate required fields
    Object.keys(requiredFields).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        const value = input?.value?.trim();
        
        if (!value || value === '') {
            showError(field, requiredFields[field]);
            isValid = false;
        }
    });

    // Validate email format if provided
    const email = document.querySelector('[name="next_of_kin_email"]')?.value?.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('next_of_kin_email', 'Please enter a valid email address');
        isValid = false;
    }

    // Validate phone number format
    const phone = document.querySelector('[name="next_of_kin_phone"]')?.value?.trim();
    if (phone && !/^[0-9+\-\s()]+$/.test(phone)) {
        showError('next_of_kin_phone', 'Please enter a valid phone number');
        isValid = false;
    }

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
    
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Submitting...';
    
    this.submit();
});

// Clear errors on input
document.querySelectorAll('#onboarding-step4-form input, #onboarding-step4-form select, #onboarding-step4-form textarea').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
    });
    input.addEventListener('change', function() {
        clearError(this.name);
    });
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



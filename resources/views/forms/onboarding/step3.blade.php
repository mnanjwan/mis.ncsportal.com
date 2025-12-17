@extends('layouts.app')

@section('title', 'Onboarding - Step 3: Banking Information')
@section('page-title', 'Onboarding - Step 3: Banking Information')

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
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold">3</div>
                    <span class="text-sm font-medium">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold">4</div>
                    <span class="text-sm text-secondary-foreground">Next of Kin</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Banking Information</h3>
        </div>
        <div class="kt-card-content">
            @if($errors->any())
            <div class="kt-alert kt-alert-danger mb-5">
                <div class="kt-alert-content">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="onboarding-step3-form" method="POST" action="{{ route('onboarding.step3.save') }}" class="flex flex-col gap-5">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Bank Name <span class="text-danger">*</span></label>
                        <select name="bank_name" class="kt-input" required>
                            <option value="">Select Bank...</option>
                            <option value="Access Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Access Bank' ? 'selected' : '' }}>Access Bank</option>
                            <option value="First Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'First Bank' ? 'selected' : '' }}>First Bank</option>
                            <option value="GTBank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'GTBank' ? 'selected' : '' }}>GTBank</option>
                            <option value="UBA" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'UBA' ? 'selected' : '' }}>UBA</option>
                            <option value="Zenith Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Zenith Bank' ? 'selected' : '' }}>Zenith Bank</option>
                            <option value="Fidelity Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Fidelity Bank' ? 'selected' : '' }}>Fidelity Bank</option>
                            <option value="Union Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Union Bank' ? 'selected' : '' }}>Union Bank</option>
                            <option value="Stanbic IBTC" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Stanbic IBTC' ? 'selected' : '' }}>Stanbic IBTC</option>
                            <option value="Ecobank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Ecobank' ? 'selected' : '' }}>Ecobank</option>
                            <option value="Sterling Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Sterling Bank' ? 'selected' : '' }}>Sterling Bank</option>
                            <option value="Wema Bank" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Wema Bank' ? 'selected' : '' }}>Wema Bank</option>
                            <option value="FCMB" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'FCMB' ? 'selected' : '' }}>FCMB</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Bank Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="bank_account_number" class="kt-input" value="{{ old('bank_account_number', $savedData['bank_account_number'] ?? '') }}" maxlength="10" required/>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Sort Code (Optional)</label>
                        <input type="text" name="sort_code" class="kt-input" value="{{ old('sort_code', $savedData['sort_code'] ?? '') }}"/>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Name of PFA (Pension Fund Administrator) <span class="text-danger">*</span></label>
                        <select name="pfa_name" class="kt-input" required>
                            <option value="">Select PFA...</option>
                            <option value="Access Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Access Pensions' ? 'selected' : '' }}>Access Pensions</option>
                            <option value="ARM Pension" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'ARM Pension' ? 'selected' : '' }}>ARM Pension</option>
                            <option value="AXA Mansard Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'AXA Mansard Pensions' ? 'selected' : '' }}>AXA Mansard Pensions</option>
                            <option value="Crusader Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Crusader Pensions' ? 'selected' : '' }}>Crusader Pensions</option>
                            <option value="Fidelity Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Fidelity Pensions' ? 'selected' : '' }}>Fidelity Pensions</option>
                            <option value="First Guarantee Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'First Guarantee Pensions' ? 'selected' : '' }}>First Guarantee Pensions</option>
                            <option value="Future Unity Glanvills Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Future Unity Glanvills Pensions' ? 'selected' : '' }}>Future Unity Glanvills Pensions</option>
                            <option value="IEI-Anchor Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'IEI-Anchor Pensions' ? 'selected' : '' }}>IEI-Anchor Pensions</option>
                            <option value="Leadway Pensure PFA" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Leadway Pensure PFA' ? 'selected' : '' }}>Leadway Pensure PFA</option>
                            <option value="Nigerian University Pension" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Nigerian University Pension' ? 'selected' : '' }}>Nigerian University Pension</option>
                            <option value="NLPC Pension Fund Administrators" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'NLPC Pension Fund Administrators' ? 'selected' : '' }}>NLPC Pension Fund Administrators</option>
                            <option value="Oak Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Oak Pensions' ? 'selected' : '' }}>Oak Pensions</option>
                            <option value="PAL Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'PAL Pensions' ? 'selected' : '' }}>PAL Pensions</option>
                            <option value="Pension Alliance Limited" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Pension Alliance Limited' ? 'selected' : '' }}>Pension Alliance Limited</option>
                            <option value="Premium Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Premium Pensions' ? 'selected' : '' }}>Premium Pensions</option>
                            <option value="Radix Pension Managers" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Radix Pension Managers' ? 'selected' : '' }}>Radix Pension Managers</option>
                            <option value="Sigma Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Sigma Pensions' ? 'selected' : '' }}>Sigma Pensions</option>
                            <option value="Stanbic IBTC Pension Managers" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Stanbic IBTC Pension Managers' ? 'selected' : '' }}>Stanbic IBTC Pension Managers</option>
                            <option value="Tangerine Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Tangerine Pensions' ? 'selected' : '' }}>Tangerine Pensions</option>
                            <option value="Trustfund Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Trustfund Pensions' ? 'selected' : '' }}>Trustfund Pensions</option>
                            <option value="Veritas Glanvills Pensions" {{ old('pfa_name', $savedData['pfa_name'] ?? '') == 'Veritas Glanvills Pensions' ? 'selected' : '' }}>Veritas Glanvills Pensions</option>
                        </select>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">RSA Number <span class="text-danger">*</span></label>
                        <input type="text" name="rsa_number" class="kt-input" value="{{ old('rsa_number', $savedData['rsa_number'] ?? '') }}" pattern="PEN[0-9]{12}" placeholder="PEN123456789012" required/>
                        <small class="text-muted">Format: PEN followed by 12 digits</small>
                        <span class="error-message text-danger text-sm hidden"></span>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step2') }}'" class="kt-btn kt-btn-secondary">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Next: Next of Kin</button>
                </div>
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

function validateStep3() {
    let isValid = true;
    
    const requiredFields = {
        'bank_name': 'Bank Name is required',
        'bank_account_number': 'Bank Account Number is required',
        'pfa_name': 'PFA Name is required',
        'rsa_number': 'RSA Number is required'
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

    // Validate bank account number (should be 10 digits)
    const accountNumber = document.querySelector('[name="bank_account_number"]')?.value?.trim();
    if (accountNumber && (!/^\d{10}$/.test(accountNumber))) {
        showError('bank_account_number', 'Bank Account Number must be exactly 10 digits');
        isValid = false;
    }

    // Validate RSA number format (PEN followed by 12 digits)
    const rsaNumber = document.querySelector('[name="rsa_number"]')?.value?.trim();
    if (rsaNumber && !/^PEN\d{12}$/.test(rsaNumber)) {
        showError('rsa_number', 'RSA Number must be in format PEN followed by 12 digits (e.g., PEN123456789012)');
        isValid = false;
    }

    return isValid;
}

// Form submission handler
document.getElementById('onboarding-step3-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateStep3()) {
        const firstError = document.querySelector('.error-message:not(.hidden)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }
    
    this.submit();
});

// Clear errors on input
document.querySelectorAll('#onboarding-step3-form input, #onboarding-step3-form select').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
    });
    input.addEventListener('change', function() {
        clearError(this.name);
    });
});
</script>
@endpush
@endsection



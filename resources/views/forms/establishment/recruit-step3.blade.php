@extends('layouts.app')

@section('title', 'Add New Recruit - Step 3: Banking Information')
@section('page-title', 'Add New Recruit - Step 3: Banking Information')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.new-recruits') }}">New Recruits</a>
    <span>/</span>
    <span class="text-primary">Step 3: Banking Information</span>
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
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #068b57; color: white;">3</div>
                    <span class="text-xs sm:text-sm font-medium" style="color: #068b57;">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">4</div>
                    <span class="text-xs sm:text-sm" style="color: #6c757d;">Next of Kin</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full flex items-center justify-center font-semibold text-sm" style="background-color: #6c757d; color: white;">5</div>
                    <span class="text-xs sm:text-sm" style="color: #6c757d;">Preview</span>
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
                    <strong class="text-danger">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            <form id="recruit-step3-form" method="POST" action="{{ route('establishment.new-recruits.step3.save') }}" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Bank Name <span class="text-danger">*</span></label>
                        <select name="bank_name" class="kt-input" required>
                            <option value="">Select Bank...</option>
                            <option value="Access Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Access Bank Limited' ? 'selected' : '' }}>Access Bank Limited</option>
                            <option value="Citibank Nigeria Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Citibank Nigeria Limited' ? 'selected' : '' }}>Citibank Nigeria Limited</option>
                            <option value="Ecobank Nigeria Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Ecobank Nigeria Limited' ? 'selected' : '' }}>Ecobank Nigeria Limited</option>
                            <option value="Fidelity Bank Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Fidelity Bank Plc' ? 'selected' : '' }}>Fidelity Bank Plc</option>
                            <option value="First Bank of Nigeria Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'First Bank of Nigeria Limited' ? 'selected' : '' }}>First Bank of Nigeria Limited</option>
                            <option value="First City Monument Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'First City Monument Bank Limited' ? 'selected' : '' }}>First City Monument Bank Limited (FCMB)</option>
                            <option value="Globus Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Globus Bank Limited' ? 'selected' : '' }}>Globus Bank Limited</option>
                            <option value="Guaranty Trust Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Guaranty Trust Bank Limited' ? 'selected' : '' }}>Guaranty Trust Bank Limited (GTBank)</option>
                            <option value="Heritage Bank Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Heritage Bank Plc' ? 'selected' : '' }}>Heritage Bank Plc</option>
                            <option value="Keystone Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Keystone Bank Limited' ? 'selected' : '' }}>Keystone Bank Limited</option>
                            <option value="Optimus Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Optimus Bank Limited' ? 'selected' : '' }}>Optimus Bank Limited</option>
                            <option value="Parallex Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Parallex Bank Limited' ? 'selected' : '' }}>Parallex Bank Limited</option>
                            <option value="Polaris Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Polaris Bank Limited' ? 'selected' : '' }}>Polaris Bank Limited</option>
                            <option value="Premium Trust Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Premium Trust Bank Limited' ? 'selected' : '' }}>Premium Trust Bank Limited</option>
                            <option value="Providus Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Providus Bank Limited' ? 'selected' : '' }}>Providus Bank Limited</option>
                            <option value="Stanbic IBTC Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Stanbic IBTC Bank Limited' ? 'selected' : '' }}>Stanbic IBTC Bank Limited</option>
                            <option value="Standard Chartered Bank Nigeria Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Standard Chartered Bank Nigeria Limited' ? 'selected' : '' }}>Standard Chartered Bank Nigeria Limited</option>
                            <option value="Sterling Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Sterling Bank Limited' ? 'selected' : '' }}>Sterling Bank Limited</option>
                            <option value="SunTrust Bank Nigeria Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'SunTrust Bank Nigeria Limited' ? 'selected' : '' }}>SunTrust Bank Nigeria Limited</option>
                            <option value="Titan Trust Bank Limited" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Titan Trust Bank Limited' ? 'selected' : '' }}>Titan Trust Bank Limited</option>
                            <option value="Union Bank of Nigeria Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Union Bank of Nigeria Plc' ? 'selected' : '' }}>Union Bank of Nigeria Plc</option>
                            <option value="United Bank for Africa Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'United Bank for Africa Plc' ? 'selected' : '' }}>United Bank for Africa Plc (UBA)</option>
                            <option value="Unity Bank Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Unity Bank Plc' ? 'selected' : '' }}>Unity Bank Plc</option>
                            <option value="Wema Bank Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Wema Bank Plc' ? 'selected' : '' }}>Wema Bank Plc</option>
                            <option value="Zenith Bank Plc" {{ old('bank_name', $savedData['bank_name'] ?? '') == 'Zenith Bank Plc' ? 'selected' : '' }}>Zenith Bank Plc</option>
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
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-5 border-t border-input">
                    <button type="button" onclick="window.location.href='{{ route('establishment.new-recruits.step2') }}'" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</button>
                    <button type="submit" class="kt-btn kt-btn-primary w-full sm:flex-1 whitespace-nowrap">Next: Next of Kin</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure all asterisks in onboarding forms are red */
    .kt-form-label span.text-danger,
    .kt-form-label .text-danger,
    label span.text-danger,
    label .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush

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
document.getElementById('recruit-step3-form').addEventListener('submit', function(e) {
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
document.querySelectorAll('#recruit-step3-form input, #recruit-step3-form select').forEach(input => {
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



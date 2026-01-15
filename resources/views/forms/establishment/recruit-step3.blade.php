@extends('layouts.public')

@section('title', 'Recruit Onboarding - Step 3: Banking Information')

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
            
            <form id="recruit-step3-form" method="POST" action="{{ route('recruit.onboarding.step3.save') }}" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                <input type="hidden" name="token" value="{{ request('token') ?? session('recruit_onboarding_token') }}">
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Bank Name <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="bank_name" id="bank_name_id" value="{{ old('bank_name', $savedData['bank_name'] ?? '') }}" required>
                            <button type="button" 
                                    id="bank_name_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="bank_name_select_text">{{ old('bank_name', $savedData['bank_name'] ?? '') ? old('bank_name', $savedData['bank_name'] ?? '') : 'Select Bank...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="bank_name_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="bank_name_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search bank..."
                                           autocomplete="off">
                                </div>
                                <div id="bank_name_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
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
                        <div class="relative">
                            <input type="hidden" name="pfa_name" id="pfa_name_id" value="{{ old('pfa_name', $savedData['pfa_name'] ?? '') }}" required>
                            <button type="button" 
                                    id="pfa_name_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="pfa_name_select_text">{{ old('pfa_name', $savedData['pfa_name'] ?? '') ? old('pfa_name', $savedData['pfa_name'] ?? '') : 'Select PFA...' }}</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="pfa_name_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="pfa_name_search_input" 
                                           class="kt-input w-full pl-10" 
                                           placeholder="Search PFA..."
                                           autocomplete="off">
                                </div>
                                <div id="pfa_name_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
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
                    <a href="{{ route('recruit.onboarding.step2', ['token' => request('token') ?? session('recruit_onboarding_token')]) }}" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</a>
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
// Reusable function to create searchable select
function createSearchableSelect(config) {
    const {
        triggerId,
        hiddenInputId,
        dropdownId,
        searchInputId,
        optionsContainerId,
        displayTextId,
        options,
        displayFn,
        onSelect,
        placeholder = 'Select...',
        searchPlaceholder = 'Search...'
    } = config;

    const trigger = document.getElementById(triggerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const dropdown = document.getElementById(dropdownId);
    const searchInput = document.getElementById(searchInputId);
    const optionsContainer = document.getElementById(optionsContainerId);
    const displayText = document.getElementById(displayTextId);

    if (!trigger || !hiddenInput || !dropdown || !searchInput || !optionsContainer || !displayText) {
        return;
    }

    let selectedOption = null;
    let filteredOptions = [...options];

    // Render options
    function renderOptions(opts) {
        if (opts.length === 0) {
            optionsContainer.innerHTML = '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>';
            return;
        }

        optionsContainer.innerHTML = opts.map(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            const value = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
            return `
                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                     data-id="${value}" 
                     data-name="${display}">
                    <div class="text-sm text-foreground">${display}</div>
                </div>
            `;
        }).join('');

        // Add click handlers
        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                selectedOption = options.find(o => {
                    const optValue = o.id !== undefined ? o.id : (o.value !== undefined ? o.value : o);
                    return String(optValue) === String(id);
                });
                
                if (selectedOption || id === '') {
                    hiddenInput.value = id;
                    displayText.textContent = name;
                    dropdown.classList.add('hidden');
                    searchInput.value = '';
                    filteredOptions = [...options];
                    renderOptions(filteredOptions);
                    
                    if (onSelect) onSelect(selectedOption || {id: id, name: name});
                }
            });
        });
    }

    // Initial render
    renderOptions(filteredOptions);

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filteredOptions = options.filter(opt => {
            const display = displayFn ? displayFn(opt) : (opt.name || opt.id || opt);
            return String(display).toLowerCase().includes(searchTerm);
        });
        renderOptions(filteredOptions);
    });

    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            setTimeout(() => searchInput.focus(), 100);
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// Initialize searchable selects
document.addEventListener('DOMContentLoaded', function() {
    // Bank name options
    const bankOptions = [
        {id: '', name: 'Select Bank...'},
        {id: 'Access Bank Limited', name: 'Access Bank Limited'},
        {id: 'Citibank Nigeria Limited', name: 'Citibank Nigeria Limited'},
        {id: 'Ecobank Nigeria Limited', name: 'Ecobank Nigeria Limited'},
        {id: 'Fidelity Bank Plc', name: 'Fidelity Bank Plc'},
        {id: 'First Bank of Nigeria Limited', name: 'First Bank of Nigeria Limited'},
        {id: 'First City Monument Bank Limited', name: 'First City Monument Bank Limited (FCMB)'},
        {id: 'Globus Bank Limited', name: 'Globus Bank Limited'},
        {id: 'Guaranty Trust Bank Limited', name: 'Guaranty Trust Bank Limited (GTBank)'},
        {id: 'Heritage Bank Plc', name: 'Heritage Bank Plc'},
        {id: 'Keystone Bank Limited', name: 'Keystone Bank Limited'},
        {id: 'Optimus Bank Limited', name: 'Optimus Bank Limited'},
        {id: 'Parallex Bank Limited', name: 'Parallex Bank Limited'},
        {id: 'Polaris Bank Limited', name: 'Polaris Bank Limited'},
        {id: 'Premium Trust Bank Limited', name: 'Premium Trust Bank Limited'},
        {id: 'Providus Bank Limited', name: 'Providus Bank Limited'},
        {id: 'Stanbic IBTC Bank Limited', name: 'Stanbic IBTC Bank Limited'},
        {id: 'Standard Chartered Bank Nigeria Limited', name: 'Standard Chartered Bank Nigeria Limited'},
        {id: 'Sterling Bank Limited', name: 'Sterling Bank Limited'},
        {id: 'SunTrust Bank Nigeria Limited', name: 'SunTrust Bank Nigeria Limited'},
        {id: 'Titan Trust Bank Limited', name: 'Titan Trust Bank Limited'},
        {id: 'Union Bank of Nigeria Plc', name: 'Union Bank of Nigeria Plc'},
        {id: 'United Bank for Africa Plc', name: 'United Bank for Africa Plc (UBA)'},
        {id: 'Unity Bank Plc', name: 'Unity Bank Plc'},
        {id: 'Wema Bank Plc', name: 'Wema Bank Plc'},
        {id: 'Zenith Bank Plc', name: 'Zenith Bank Plc'}
    ];

    // PFA name options
    const pfaOptions = [
        {id: '', name: 'Select PFA...'},
        {id: 'Access Pensions', name: 'Access Pensions'},
        {id: 'ARM Pension', name: 'ARM Pension'},
        {id: 'AXA Mansard Pensions', name: 'AXA Mansard Pensions'},
        {id: 'Crusader Pensions', name: 'Crusader Pensions'},
        {id: 'Fidelity Pensions', name: 'Fidelity Pensions'},
        {id: 'First Guarantee Pensions', name: 'First Guarantee Pensions'},
        {id: 'Future Unity Glanvills Pensions', name: 'Future Unity Glanvills Pensions'},
        {id: 'IEI-Anchor Pensions', name: 'IEI-Anchor Pensions'},
        {id: 'Leadway Pensure PFA', name: 'Leadway Pensure PFA'},
        {id: 'Nigerian University Pension', name: 'Nigerian University Pension'},
        {id: 'NLPC Pension Fund Administrators', name: 'NLPC Pension Fund Administrators'},
        {id: 'Oak Pensions', name: 'Oak Pensions'},
        {id: 'PAL Pensions', name: 'PAL Pensions'},
        {id: 'Pension Alliance Limited', name: 'Pension Alliance Limited'},
        {id: 'Premium Pensions', name: 'Premium Pensions'},
        {id: 'Radix Pension Managers', name: 'Radix Pension Managers'},
        {id: 'Sigma Pensions', name: 'Sigma Pensions'},
        {id: 'Stanbic IBTC Pension Managers', name: 'Stanbic IBTC Pension Managers'},
        {id: 'Tangerine Pensions', name: 'Tangerine Pensions'},
        {id: 'Trustfund Pensions', name: 'Trustfund Pensions'},
        {id: 'Veritas Glanvills Pensions', name: 'Veritas Glanvills Pensions'}
    ];

    // Initialize bank name select
    if (document.getElementById('bank_name_select_trigger')) {
        createSearchableSelect({
            triggerId: 'bank_name_select_trigger',
            hiddenInputId: 'bank_name_id',
            dropdownId: 'bank_name_dropdown',
            searchInputId: 'bank_name_search_input',
            optionsContainerId: 'bank_name_options',
            displayTextId: 'bank_name_select_text',
            options: bankOptions,
            placeholder: 'Select Bank...',
            searchPlaceholder: 'Search bank...'
        });
    }

    // Initialize PFA name select
    if (document.getElementById('pfa_name_select_trigger')) {
        createSearchableSelect({
            triggerId: 'pfa_name_select_trigger',
            hiddenInputId: 'pfa_name_id',
            dropdownId: 'pfa_name_dropdown',
            searchInputId: 'pfa_name_search_input',
            optionsContainerId: 'pfa_name_options',
            displayTextId: 'pfa_name_select_text',
            options: pfaOptions,
            placeholder: 'Select PFA...',
            searchPlaceholder: 'Search PFA...'
        });
    }
});

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
document.querySelectorAll('#recruit-step3-form input[type="text"], #recruit-step3-form input[type="number"], #recruit-step3-form input[type="hidden"]').forEach(input => {
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



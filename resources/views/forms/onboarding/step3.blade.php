@extends('layouts.app')

@section('title', 'Onboarding - Step 3: Banking Information')
@section('page-title', 'Onboarding - Step 3: Banking Information')

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
                    <div class="size-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold text-sm">3</div>
                    <span class="text-xs sm:text-sm font-medium">Banking Information</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-8 rounded-full bg-muted text-secondary-foreground flex items-center justify-center font-semibold text-sm">4</div>
                    <span class="text-xs sm:text-sm text-secondary-foreground">Next of Kin</span>
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
            
            <form id="onboarding-step3-form" method="POST" action="{{ route('onboarding.step3.save') }}" class="flex flex-col gap-5 w-full overflow-hidden">
                @csrf
                
                <div class="grid lg:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label">Bank Name <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="hidden" name="bank_name" id="bank_name" value="{{ old('bank_name', $savedData['bank_name'] ?? '') }}" required>
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
                            <input type="hidden" name="pfa_name" id="pfa_name" value="{{ old('pfa_name', $savedData['pfa_name'] ?? '') }}" required>
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
                    <button type="button" onclick="window.location.href='{{ route('onboarding.step2') }}'" class="kt-btn kt-btn-secondary w-full sm:flex-1 whitespace-nowrap">Previous</button>
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

// Validation functions
function showError(field, message) {
    const input = document.querySelector(`[name="${field}"]`);
    if (!input) return;
    
    // For select fields (bank_name, pfa_name), show error on the trigger button
    if (field === 'bank_name' || field === 'pfa_name') {
        const triggerId = field === 'bank_name' ? 'bank_name_select_trigger' : 'pfa_name_select_trigger';
        const trigger = document.getElementById(triggerId);
        const errorSpan = trigger?.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.remove('hidden');
            trigger?.classList.add('border-danger');
        }
    } else {
        const errorSpan = input?.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.remove('hidden');
            input?.classList.add('border-danger');
        }
    }
}

function clearError(field) {
    const input = document.querySelector(`[name="${field}"]`);
    if (!input) return;
    
    // For select fields (bank_name, pfa_name), clear error on the trigger button
    if (field === 'bank_name' || field === 'pfa_name') {
        const triggerId = field === 'bank_name' ? 'bank_name_select_trigger' : 'pfa_name_select_trigger';
        const trigger = document.getElementById(triggerId);
        const errorSpan = trigger?.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            trigger?.classList.remove('border-danger');
        }
    } else {
        const errorSpan = input?.parentElement?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.classList.add('hidden');
            input?.classList.remove('border-danger');
        }
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

// Initialize selects on page load
document.addEventListener('DOMContentLoaded', function() {
    // Bank names list
    const banks = [
        'Access Bank Limited',
        'Citibank Nigeria Limited',
        'Ecobank Nigeria Limited',
        'Fidelity Bank Plc',
        'First Bank of Nigeria Limited',
        'First City Monument Bank Limited',
        'Globus Bank Limited',
        'Guaranty Trust Bank Limited',
        'Heritage Bank Plc',
        'Keystone Bank Limited',
        'Optimus Bank Limited',
        'Parallex Bank Limited',
        'Polaris Bank Limited',
        'Premium Trust Bank Limited',
        'Providus Bank Limited',
        'Stanbic IBTC Bank Limited',
        'Standard Chartered Bank Nigeria Limited',
        'Sterling Bank Limited',
        'SunTrust Bank Nigeria Limited',
        'Titan Trust Bank Limited',
        'Union Bank of Nigeria Plc',
        'United Bank for Africa Plc',
        'Unity Bank Plc',
        'Wema Bank Plc',
        'Zenith Bank Plc'
    ];
    
    // PFA names list
    const pfas = [
        'Access Pensions',
        'ARM Pension',
        'AXA Mansard Pensions',
        'Crusader Pensions',
        'Fidelity Pensions',
        'First Guarantee Pensions',
        'Future Unity Glanvills Pensions',
        'IEI-Anchor Pensions',
        'Leadway Pensure PFA',
        'Nigerian University Pension',
        'NLPC Pension Fund Administrators',
        'Oak Pensions',
        'PAL Pensions',
        'Pension Alliance Limited',
        'Premium Pensions',
        'Radix Pension Managers',
        'Sigma Pensions',
        'Stanbic IBTC Pension Managers',
        'Tangerine Pensions',
        'Trustfund Pensions',
        'Veritas Glanvills Pensions'
    ];
    
    // Initialize Bank Name select
    const bankOptions = [
        {id: '', name: 'Select Bank...'},
        ...banks.map(bank => ({id: bank, name: bank}))
    ];
    
    createSearchableSelect({
        triggerId: 'bank_name_select_trigger',
        hiddenInputId: 'bank_name',
        dropdownId: 'bank_name_dropdown',
        searchInputId: 'bank_name_search_input',
        optionsContainerId: 'bank_name_options',
        displayTextId: 'bank_name_select_text',
        options: bankOptions,
        placeholder: 'Select Bank...',
        searchPlaceholder: 'Search bank...'
    });
    
    // Set initial value if saved
    const savedBankName = '{{ old('bank_name', $savedData['bank_name'] ?? '') }}';
    if (savedBankName) {
        document.getElementById('bank_name').value = savedBankName;
        document.getElementById('bank_name_select_text').textContent = savedBankName;
    }
    
    // Initialize PFA Name select
    const pfaOptions = [
        {id: '', name: 'Select PFA...'},
        ...pfas.map(pfa => ({id: pfa, name: pfa}))
    ];
    
    createSearchableSelect({
        triggerId: 'pfa_name_select_trigger',
        hiddenInputId: 'pfa_name',
        dropdownId: 'pfa_name_dropdown',
        searchInputId: 'pfa_name_search_input',
        optionsContainerId: 'pfa_name_options',
        displayTextId: 'pfa_name_select_text',
        options: pfaOptions,
        placeholder: 'Select PFA...',
        searchPlaceholder: 'Search PFA...'
    });
    
    // Set initial value if saved
    const savedPfaName = '{{ old('pfa_name', $savedData['pfa_name'] ?? '') }}';
    if (savedPfaName) {
        document.getElementById('pfa_name').value = savedPfaName;
        document.getElementById('pfa_name_select_text').textContent = savedPfaName;
    }
});

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

// Clear errors on input - handle both regular inputs and hidden inputs for selects
document.querySelectorAll('#onboarding-step3-form input').forEach(input => {
    input.addEventListener('input', function() {
        clearError(this.name);
        // If this is a hidden input for a select, also clear error on the trigger button
        if (this.type === 'hidden' && (this.name === 'bank_name' || this.name === 'pfa_name')) {
            const triggerId = this.name === 'bank_name' ? 'bank_name_select_trigger' : 'pfa_name_select_trigger';
            const trigger = document.getElementById(triggerId);
            const errorSpan = trigger?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = '';
                errorSpan.classList.add('hidden');
                trigger?.classList.remove('border-danger');
            }
        }
    });
    input.addEventListener('change', function() {
        clearError(this.name);
        // If this is a hidden input for a select, also clear error on the trigger button
        if (this.type === 'hidden' && (this.name === 'bank_name' || this.name === 'pfa_name')) {
            const triggerId = this.name === 'bank_name' ? 'bank_name_select_trigger' : 'pfa_name_select_trigger';
            const trigger = document.getElementById(triggerId);
            const errorSpan = trigger?.parentElement?.querySelector('.error-message');
            if (errorSpan) {
                errorSpan.textContent = '';
                errorSpan.classList.add('hidden');
                trigger?.classList.remove('border-danger');
            }
        }
    });
});
</script>
@endpush
@endsection



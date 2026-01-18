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
                        <input type="text" id="bank_account_number" name="bank_account_number" class="kt-input" value="{{ old('bank_account_number', $savedData['bank_account_number'] ?? '') }}" maxlength="50" required/>
                        <small id="bank_account_help" class="text-muted">Select a bank to see the required account number digits.</small>
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
                        <input type="text" id="rsa_number" name="rsa_number" class="kt-input" value="{{ old('rsa_number', $savedData['rsa_number'] ?? '') }}" maxlength="50" placeholder="" required/>
                        <small id="rsa_help" class="text-muted">Select a PFA to see the required RSA format.</small>
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
const BANKS_FROM_SERVER = @json($banks ?? []);
const PFAS_FROM_SERVER = @json($pfas ?? []);

function escapeRegExp(str) {
    return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

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
    const banksFromServer = BANKS_FROM_SERVER || [];
    const pfasFromServer = PFAS_FROM_SERVER || [];

    const bankAccountInput = document.getElementById('bank_account_number');
    const rsaInput = document.getElementById('rsa_number');
    const bankHelp = document.getElementById('bank_account_help');
    const rsaHelp = document.getElementById('rsa_help');

    function applyBankDigits(digits) {
        const n = Number(digits) || 10;
        if (bankAccountInput) bankAccountInput.maxLength = n;
        if (bankHelp) bankHelp.textContent = `Account number must be exactly ${n} digits for the selected bank.`;
    }

    function applyRsaFormat(prefix, digits) {
        const p = String(prefix || 'PEN').toUpperCase();
        const n = Number(digits) || 12;
        const maxLen = p.length + n;
        if (rsaInput) {
            rsaInput.maxLength = maxLen;
            rsaInput.placeholder = `${p}${'0'.repeat(n)}`;
        }
        if (rsaHelp) rsaHelp.textContent = `RSA must be ${p} followed by ${n} digits (e.g., ${p}${'0'.repeat(n)}).`;
    }

    // Bank name options
    const bankOptions = [
        {id: '', name: 'Select Bank...'},
        ...banksFromServer.map(bank => ({
            id: bank.name,
            name: bank.name,
            account_number_digits: bank.account_number_digits,
        }))
    ];

    // PFA name options
    const pfaOptions = [
        {id: '', name: 'Select PFA...'},
        ...pfasFromServer.map(pfa => ({
            id: pfa.name,
            name: pfa.name,
            rsa_prefix: pfa.rsa_prefix,
            rsa_digits: pfa.rsa_digits,
        }))
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
            searchPlaceholder: 'Search bank...',
            onSelect: (bank) => applyBankDigits(bank?.account_number_digits),
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
            searchPlaceholder: 'Search PFA...',
            onSelect: (pfa) => applyRsaFormat(pfa?.rsa_prefix, pfa?.rsa_digits),
        });
    }

    // Apply initial constraints based on saved values (if any)
    const savedBankName = '{{ old('bank_name', $savedData['bank_name'] ?? '') }}';
    if (savedBankName) {
        const bank = bankOptions.find(b => b.id === savedBankName);
        applyBankDigits(bank?.account_number_digits);
    } else {
        applyBankDigits(10);
    }

    const savedPfaName = '{{ old('pfa_name', $savedData['pfa_name'] ?? '') }}';
    if (savedPfaName) {
        const pfa = pfaOptions.find(p => p.id === savedPfaName);
        applyRsaFormat(pfa?.rsa_prefix, pfa?.rsa_digits);
    } else {
        applyRsaFormat('PEN', 12);
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

    // Validate bank account number (digits depend on selected bank)
    const bankName = document.querySelector('[name="bank_name"]')?.value?.trim();
    const accountNumber = document.querySelector('[name="bank_account_number"]')?.value?.trim();
    const bank = (BANKS_FROM_SERVER || []).find(b => b.name === bankName);
    const bankDigits = Number(bank?.account_number_digits) || 10;
    if (accountNumber && !(new RegExp(`^\\d{${bankDigits}}$`).test(accountNumber))) {
        showError('bank_account_number', `Bank Account Number must be exactly ${bankDigits} digits`);
        isValid = false;
    }

    // Validate RSA number format (depends on selected PFA)
    const pfaName = document.querySelector('[name="pfa_name"]')?.value?.trim();
    const rsaNumber = document.querySelector('[name="rsa_number"]')?.value?.trim();
    const pfa = (PFAS_FROM_SERVER || []).find(p => p.name === pfaName);
    const prefix = String((pfa?.rsa_prefix || 'PEN')).toUpperCase();
    const rsaDigits = Number(pfa?.rsa_digits) || 12;
    const rsaRegex = new RegExp(`^${escapeRegExp(prefix)}\\d{${rsaDigits}}$`);
    if (rsaNumber && !rsaRegex.test(rsaNumber)) {
        showError('rsa_number', `RSA Number must be in format ${prefix} followed by ${rsaDigits} digits (e.g., ${prefix}${'0'.repeat(rsaDigits)})`);
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



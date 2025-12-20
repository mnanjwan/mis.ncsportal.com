@extends('layouts.app')

@section('title', 'Request Account Change')
@section('page-title', 'Request Account Number / RSA PIN Change')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.account-change.index') }}">Account Changes</a>
    <span>/</span>
    <span class="text-primary">Request Change</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Current Information Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Current Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Bank Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $currentBankName ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Account Number</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $currentAccountNumber ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Sort Code</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $currentSortCode ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current PFA Name</span>
                        <p class="text-sm font-semibold text-foreground mt-1">
                            {{ $currentPfaName ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current RSA PIN</span>
                        <p class="text-sm font-semibold text-foreground font-mono mt-1">
                            {{ $currentRsaPin ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Request Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Request Change</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('officer.account-change.store') }}" method="POST" id="changeRequestForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Bank Name Change -->
                        <div>
                            <label for="new_bank_name" class="block text-sm font-medium text-foreground mb-2">
                                New Bank Name
                            </label>
                            <input type="text" 
                                   id="new_bank_name" 
                                   name="new_bank_name" 
                                   value="{{ old('new_bank_name') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter new bank name"
                                   maxlength="255">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Leave blank if you don't want to change bank name
                            </p>
                        </div>

                        <!-- Account Number Change -->
                        <div>
                            <label for="new_account_number" class="block text-sm font-medium text-foreground mb-2">
                                New Account Number
                            </label>
                            <input type="text" 
                                   id="new_account_number" 
                                   name="new_account_number" 
                                   value="{{ old('new_account_number') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter new account number"
                                   maxlength="50">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Leave blank if you don't want to change account number
                            </p>
                        </div>

                        <!-- Sort Code Change -->
                        <div>
                            <label for="new_sort_code" class="block text-sm font-medium text-foreground mb-2">
                                New Sort Code
                            </label>
                            <input type="text" 
                                   id="new_sort_code" 
                                   name="new_sort_code" 
                                   value="{{ old('new_sort_code') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter new sort code"
                                   maxlength="50">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Leave blank if you don't want to change sort code
                            </p>
                        </div>

                        <!-- PFA Name Change -->
                        <div>
                            <label for="new_pfa_name" class="block text-sm font-medium text-foreground mb-2">
                                New PFA Name
                            </label>
                            <input type="text" 
                                   id="new_pfa_name" 
                                   name="new_pfa_name" 
                                   value="{{ old('new_pfa_name') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter new PFA name"
                                   maxlength="255">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Leave blank if you don't want to change PFA name
                            </p>
                        </div>

                        <!-- RSA PIN Change -->
                        <div>
                            <label for="new_rsa_pin" class="block text-sm font-medium text-foreground mb-2">
                                New RSA PIN
                            </label>
                            <input type="text" 
                                   id="new_rsa_pin" 
                                   name="new_rsa_pin" 
                                   value="{{ old('new_rsa_pin') }}"
                                   class="kt-input w-full" 
                                   placeholder="PEN123456789012"
                                   maxlength="15"
                                   pattern="PEN\d{12}">
                            <p class="text-xs text-secondary-foreground mt-1">
                                RSA PIN must have prefix PEN followed by 12 digits (e.g., PEN123456789012)
                            </p>
                            <p class="text-xs text-secondary-foreground mt-1">
                                Leave blank if you don't want to change RSA PIN
                            </p>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-foreground mb-2">
                                Reason for Change (Optional)
                            </label>
                            <textarea id="reason" 
                                      name="reason" 
                                      rows="4"
                                      class="kt-input w-full"
                                      placeholder="Enter reason for this change (optional)">{{ old('reason') }}</textarea>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>You must provide at least one change (Bank Name, Account Number, Sort Code, PFA Name, or RSA PIN)</li>
                                            <li>Your request will be reviewed by the Accounts Section</li>
                                            <li>You will be notified once your request is processed</li>
                                            <li>RSA PIN must have the prefix PEN followed by 12 digits</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('officer.account-change.index') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('changeRequestForm');
                
                form.addEventListener('submit', function (e) {
                    const bankName = document.getElementById('new_bank_name').value.trim();
                    const accountNumber = document.getElementById('new_account_number').value.trim();
                    const sortCode = document.getElementById('new_sort_code').value.trim();
                    const pfaName = document.getElementById('new_pfa_name').value.trim();
                    const rsaPin = document.getElementById('new_rsa_pin').value.trim();
                    
                    if (!bankName && !accountNumber && !sortCode && !pfaName && !rsaPin) {
                        e.preventDefault();
                        alert('Please provide at least one change (Bank Name, Account Number, Sort Code, PFA Name, or RSA PIN).');
                        return false;
                    }
                });
            });
        </script>
    @endpush
@endsection

@extends('layouts.app')

@section('title', 'Raise Emolument')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.emoluments') }}">Emoluments</a>
    <span>/</span>
    <span class="text-primary">Raise</span>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Raise Emolument</h1>
                <p class="text-sm text-gray-600 mt-1">Submit your annual emolument for processing</p>
            </div>
            <a href="{{ route('officer.emoluments') }}" class="kt-btn kt-btn-secondary">
                <i class="ki-filled ki-left"></i>
                Back to Emoluments
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emolument Information</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('emolument.store') }}" method="POST" id="raiseEmolumentForm">
                    @csrf

                    <!-- Timeline Selection -->
                    <div class="mb-6">
                        <label for="timeline_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Emolument Timeline <span class="text-red-500">*</span>
                        </label>
                        <select id="timeline_id" name="timeline_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent">
                            <option value="">Select Timeline</option>
                            @forelse($timelines as $timeline)
                                <option value="{{ $timeline->id }}" {{ old('timeline_id') == $timeline->id ? 'selected' : '' }}>
                                    {{ $timeline->year }} ({{ $timeline->start_date->format('d M Y') }} to
                                    {{ $timeline->end_date->format('d M Y') }})
                                </option>
                            @empty
                                <option value="">No active timeline available</option>
                            @endforelse
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select the active emolument timeline</p>
                    </div>

                    <!-- Bank Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bank_name" name="bank_name" required
                                value="{{ old('bank_name', $officer->bank_name ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent"
                                placeholder="Enter bank name">
                        </div>

                        <div>
                            <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bank_account_number" name="bank_account_number" required
                                value="{{ old('bank_account_number', $officer->bank_account_number ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent"
                                placeholder="Enter account number">
                        </div>
                    </div>

                    <!-- PFA Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="pfa_name" class="block text-sm font-medium text-gray-700 mb-2">
                                PFA Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="pfa_name" name="pfa_name" required
                                value="{{ old('pfa_name', $officer->pfa_name ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent"
                                placeholder="Enter PFA name">
                        </div>

                        <div>
                            <label for="rsa_pin" class="block text-sm font-medium text-gray-700 mb-2">
                                RSA PIN <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="rsa_pin" name="rsa_pin" required
                                value="{{ old('rsa_pin', $officer->rsa_number ?? '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent"
                                placeholder="Enter RSA PIN">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes (Optional)
                        </label>
                        <textarea id="notes" name="notes" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#088a56] focus:border-transparent"
                            placeholder="Enter any additional information">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('officer.emoluments') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i>
                            Submit Emolument
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('raiseEmolumentForm');
                let isSubmitting = false;

                form.addEventListener('submit', function (e) {
                    // If already confirmed, allow submission
                    if (isSubmitting) {
                        return true;
                    }

                    // Prevent default submission
                    e.preventDefault();

                    Swal.fire({
                        title: 'Confirm Submission',
                        text: 'Are you sure you want to submit this emolument?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            isSubmitting = true;
                            // Use HTMLFormElement.prototype.submit() to bypass event listener
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
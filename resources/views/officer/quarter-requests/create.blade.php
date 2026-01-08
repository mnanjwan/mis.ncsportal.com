@extends('layouts.app')

@section('title', 'Request Quarter')
@section('page-title', 'Request Quarter')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Request Quarter</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
        <div class="xl:col-span-2 space-y-5">
            <!-- Quarter Request Info Card -->
            <div class="kt-card bg-info/10 border border-info/20">
                <div class="kt-card-content p-5">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-2xl text-info"></i>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-mono">Quarter Request Information</span>
                            <span class="text-xs text-secondary-foreground">
                                Submit a request to be allocated accommodation. You can specify a preferred quarter type or leave it as "Any" for the Building Unit to assign.
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Your request will be reviewed by Building Unit. You will be notified once a decision is made.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Quarter Request Info Card -->

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

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form class="kt-card" id="request-quarter-form" method="POST">
                @csrf
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Quarter Request Form</h3>
                </div>
                <div class="kt-card-content space-y-5">
                    <!-- Preferred Quarter Type -->
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono">Preferred Quarter Type</label>
                        <select class="kt-input" id="preferred-quarter-type" name="preferred_quarter_type">
                            <option value="">Any</option>
                            <option value="Single Room">Single Room</option>
                            <option value="One Bedroom">One Bedroom</option>
                            <option value="Two Bedroom">Two Bedroom</option>
                            <option value="Three Bedroom">Three Bedroom</option>
                            <option value="Four Bedroom">Four Bedroom</option>
                            <option value="Duplex">Duplex</option>
                            <option value="Bungalow">Bungalow</option>
                            <option value="Other">Other</option>
                        </select>
                        <span class="text-xs text-secondary-foreground">Select your preferred quarter type (optional)</span>
                    </div>

                    <!-- Info Alert -->
                    <div class="kt-alert kt-alert-info">
                        <i class="ki-filled ki-information"></i>
                        <div>
                            <strong>Note:</strong> If you don't specify a preferred type, Building Unit will assign an available quarter based on availability and eligibility.
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                    <a class="kt-btn kt-btn-outline" href="{{ route('officer.dashboard') }}">Cancel</a>
                    <button class="kt-btn kt-btn-primary" type="submit" id="submit-btn">
                        Submit Request
                        <i class="ki-filled ki-check text-base"></i>
                    </button>
                </div>
            </form>
            <!-- End of Form -->
        </div>
        <div class="xl:col-span-1">
            <!-- Quarter Request Rules Card -->
            <div class="kt-card bg-accent/50">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Request Guidelines</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3 text-sm">
                        <p class="text-xs text-secondary-foreground">
                            When requesting a quarter, please note the following guidelines:
                        </p>
                        <div class="kt-card shadow-none bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-xs text-secondary-foreground">
                                    <strong class="text-mono">Important:</strong>
                                </p>
                                <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                    <li>You can only have one pending request at a time</li>
                                    <li>Building Unit will review and approve or reject your request</li>
                                    <li>If approved, you'll receive a quarter allocation to accept</li>
                                    <li>You must accept the allocation to finalize the assignment</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-secondary-foreground">
                            Your request will be reviewed by Building Unit in your command.
                        </p>
                        <p class="text-xs text-secondary-foreground">
                            You will receive a notification once your request has been processed.
                        </p>
                    </div>
                </div>
            </div>
            <!-- End of Quarter Request Rules Card -->
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10" id="confirm-modal-icon">
                        <i class="ki-filled ki-information text-warning text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground" id="confirm-modal-title">Confirm Action</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground" id="confirm-modal-message">
                    Are you sure you want to proceed?
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" id="confirm-modal-cancel">
                    Cancel
                </button>
                <button class="kt-btn kt-btn-primary" id="confirm-modal-confirm">
                    <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                    <span>Confirm</span>
                </button>
            </div>
        </div>
    </div>
    <!-- End of Confirmation Modal -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('request-quarter-form').addEventListener('submit', handleSubmit);
            });

            async function handleSubmit(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submit-btn');
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ki-filled ki-loader"></i> Submitting...';

                try {
                    const token = window.API_CONFIG?.token;
                    if (!token) {
                        throw new Error('API token not found');
                    }

                    const formData = {
                        preferred_quarter_type: document.getElementById('preferred-quarter-type').value || null,
                    };

                    const res = await fetch('/api/v1/quarters/request', {
                        method: 'POST',
                        headers: { 
                            'Authorization': 'Bearer ' + token, 
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok && data.success) {
                        // Show success message and redirect
                        showConfirmModal(
                            'Success',
                            'Quarter request submitted successfully!',
                            () => {
                                window.location.href = '{{ route("officer.quarter-requests") }}';
                            },
                            'success'
                        );
                    } else {
                        const errorMsg = data.message || 'Failed to submit request';
                        
                        if (data.meta?.code === 'PENDING_REQUEST_EXISTS') {
                            showConfirmModal(
                                'Pending Request Exists',
                                'You already have a pending quarter request. Please wait for it to be processed.',
                                () => {},
                                'warning'
                            );
                        } else {
                            showConfirmModal(
                                'Error',
                                errorMsg,
                                () => {},
                                'error'
                            );
                        }
                    }
                } catch (error) {
                    console.error('Error submitting request:', error);
                    showConfirmModal(
                        'Error',
                        'An error occurred while submitting your request. Please try again.',
                        () => {},
                        'error'
                    );
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }
            }

            function showConfirmModal(title, message, onConfirm, type = 'warning') {
                const modal = document.getElementById('confirm-modal');
                const modalTitle = document.getElementById('confirm-modal-title');
                const modalMessage = document.getElementById('confirm-modal-message');
                const confirmBtn = document.getElementById('confirm-modal-confirm');
                const cancelBtn = document.getElementById('confirm-modal-cancel');
                const iconDiv = document.getElementById('confirm-modal-icon');

                // Set title and message
                modalTitle.textContent = title;
                modalMessage.textContent = message;

                // Set icon color based on type
                if (type === 'error') {
                    iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-danger/10';
                    iconDiv.innerHTML = '<i class="ki-filled ki-information text-danger text-xl"></i>';
                    confirmBtn.className = 'kt-btn kt-btn-danger';
                } else if (type === 'success') {
                    iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-success/10';
                    iconDiv.innerHTML = '<i class="ki-filled ki-check-circle text-success text-xl"></i>';
                    confirmBtn.className = 'kt-btn kt-btn-success';
                } else {
                    iconDiv.className = 'flex items-center justify-center size-10 rounded-full bg-warning/10';
                    iconDiv.innerHTML = '<i class="ki-filled ki-information text-warning text-xl"></i>';
                    confirmBtn.className = 'kt-btn kt-btn-primary';
                }

                // Set up confirm handler
                confirmBtn.onclick = () => {
                    onConfirm();
                    // Close modal - trigger dismiss event
                    if (type !== 'success') {
                        cancelBtn.click();
                    }
                };

                // Show modal - trigger the kt-modal show
                const event = new CustomEvent('kt-modal-show', { bubbles: true });
                modal.dispatchEvent(event);
                
                // Alternative: manually show modal if event doesn't work
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        </script>
    @endpush
@endsection

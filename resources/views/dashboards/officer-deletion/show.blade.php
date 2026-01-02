@extends('layouts.app')

@section('title', 'Delete Officer - ' . ($officer->initials ?? '') . ' ' . ($officer->surname ?? ''))
@section('page-title', 'Delete Officer')

@section('breadcrumbs')
    @if(auth()->user()->hasRole('HRD'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    @elseif(auth()->user()->hasRole('Establishment'))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    @endif
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.index') : route('establishment.officers.delete.index') }}">Delete Officer</a>
    <span>/</span>
    <span class="text-primary">View Officer</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Warning Banner -->
        <div class="kt-card border-red-500 bg-red-50 dark:bg-red-950/20">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-red-600 text-xl flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-semibold text-red-900 dark:text-red-100 mb-1">⚠️ Destructive and Irreversible Action</h3>
                        <p class="text-sm text-red-800 dark:text-red-200">
                            Deleting this officer will permanently remove all associated records including profile, postings, applications, courses, and more. This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.index') : route('establishment.officers.delete.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Officers List
            </a>
        </div>

        @if(!$canDelete && count($deletionBlockers) > 0)
            <!-- Deletion Blockers -->
            <div class="kt-card border-yellow-500 bg-yellow-50 dark:bg-yellow-950/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-start gap-3">
                        <i class="ki-filled ki-information-2 text-yellow-600 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <h3 class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Cannot Delete Officer</h3>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200 mb-2">
                                This officer cannot be deleted due to the following reasons:
                            </p>
                            <ul class="list-disc list-inside text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                                @foreach($deletionBlockers as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-2">
                                Please resolve these issues before attempting to delete the officer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5">
                    <div class="kt-avatar size-24">
                        <div class="kt-avatar-image">
                            @if($officer->getProfilePictureUrlFull())
                                <img alt="avatar" src="{{ $officer->getProfilePictureUrlFull() }}" />
                            @else
                                <div class="flex items-center justify-center size-24 rounded-full bg-primary/10 text-primary font-bold text-xl">
                                    {{ strtoupper(($officer->initials[0] ?? '') . ($officer->surname[0] ?? '')) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 grow">
                        <h2 class="text-2xl font-semibold text-mono">
                            {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="text-secondary-foreground">
                                Service Number: <span class="font-semibold text-mono">{{ $officer->service_number ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Rank: <span class="font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                            </span>
                            <span class="text-secondary-foreground">
                                Command: <span class="font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officer Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-7.5">
            <!-- Personal Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Personal Information</h3>
                </div>
                <div class="kt-card-content">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Date of Birth</dt>
                            <dd class="text-sm text-foreground">{{ $officer->date_of_birth ? $officer->date_of_birth->format('d/m/Y') : 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Sex</dt>
                            <dd class="text-sm text-foreground">{{ $officer->sex ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Email</dt>
                            <dd class="text-sm text-foreground">{{ $officer->email ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Phone Number</dt>
                            <dd class="text-sm text-foreground">{{ $officer->phone_number ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Service Information -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Service Information</h3>
                </div>
                <div class="kt-card-content">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Date of First Appointment</dt>
                            <dd class="text-sm text-foreground">{{ $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('d/m/Y') : 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Current Unit</dt>
                            <dd class="text-sm text-foreground">{{ $currentUnit ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-secondary-foreground">Status</dt>
                            <dd class="text-sm text-foreground">
                                @if($officer->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Associated Records Summary -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Associated Records (Will be Deleted)</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->postings->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Postings</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->leaveApplications->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Leave Applications</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->passApplications->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Pass Applications</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->courses->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Course Nominations</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->dutyRosterAssignments->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Roster Assignments</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->queries->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Queries</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->emoluments->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Emoluments</div>
                    </div>
                    <div class="text-center p-4 bg-muted rounded-lg">
                        <div class="text-2xl font-bold text-foreground">{{ $officer->documents->count() }}</div>
                        <div class="text-sm text-secondary-foreground">Documents</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Button -->
        @if($canDelete)
            <div class="kt-card border-red-500">
                <div class="kt-card-content p-5">
                    <div class="flex flex-col items-center gap-4">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-red-600 mb-2">Permanent Deletion</h3>
                            <p class="text-sm text-secondary-foreground mb-4">
                                This action will permanently delete this officer and all associated records. This cannot be undone.
                            </p>
                        </div>
                        <button onclick="openDeleteModal()" class="kt-btn kt-btn-danger">
                            <i class="ki-filled ki-trash"></i> DELETE OFFICER (Permanent)
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-background rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                        <i class="ki-filled ki-information-2 text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Confirm Permanent Deletion</h3>
                </div>
                
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-950/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-sm text-red-900 dark:text-red-100 font-medium mb-2">
                        This action will permanently delete:
                    </p>
                    <ul class="text-sm text-red-800 dark:text-red-200 list-disc list-inside space-y-1">
                        <li>Officer record and profile</li>
                        <li>All posting history</li>
                        <li>All leave and pass applications</li>
                        <li>All roster assignments</li>
                        <li>All APER records</li>
                        <li>All course nominations</li>
                        <li>All notifications and login credentials</li>
                    </ul>
                    <p class="text-sm text-red-900 dark:text-red-100 font-medium mt-2">
                        This action cannot be undone.
                    </p>
                </div>

                <!-- Error Message Area (hidden by default) -->
                <div id="errorMessage" class="mb-4 hidden p-4 bg-red-50 dark:bg-red-950/20 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="flex items-start gap-3">
                        <i class="ki-filled ki-information-2 text-red-600 text-xl flex-shrink-0 mt-0.5"></i>
                        <div class="flex-1">
                            <div class="font-semibold text-red-900 dark:text-red-100 mb-1">Deletion Failed</div>
                            <div class="text-sm text-red-800 dark:text-red-200" id="errorMessageText"></div>
                        </div>
                        <button onclick="document.getElementById('errorMessage').classList.add('hidden')" class="text-red-600 hover:text-red-800">
                            <i class="ki-filled ki-cross"></i>
                        </button>
                    </div>
                </div>

                <form id="deleteForm" method="POST" action="{{ auth()->user()->hasRole('HRD') ? route('hrd.officers.delete.destroy', $officer->id) : route('establishment.officers.delete.destroy', $officer->id) }}">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-secondary-foreground mb-2">
                            Type <strong>DELETE</strong> to confirm:
                        </label>
                        <input type="text" 
                               name="confirmation_text" 
                               id="confirmation_text"
                               class="kt-input w-full" 
                               placeholder="Type DELETE here"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-secondary-foreground mb-2">
                            Reason for deletion (optional):
                        </label>
                        <textarea name="reason" 
                                  id="reason"
                                  class="kt-input w-full" 
                                  rows="3"
                                  placeholder="Enter reason for deletion..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" 
                                   name="understand" 
                                   id="understand"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                   required>
                            <span class="text-sm text-secondary-foreground">
                                I understand this action is irreversible
                            </span>
                        </label>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button type="button" 
                                onclick="closeDeleteModal()" 
                                id="cancelButton"
                                class="kt-btn kt-btn-outline">
                            Cancel
                        </button>
                        <button type="submit" 
                                id="deleteButton"
                                class="kt-btn kt-btn-danger"
                                disabled>
                            <i class="ki-filled ki-trash"></i> <span id="deleteButtonText">Delete Permanently</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const deleteButton = document.getElementById('deleteButton');
            const deleteButtonText = document.getElementById('deleteButtonText');
            const cancelButton = document.getElementById('cancelButton');
            const confirmationInput = document.getElementById('confirmation_text');
            const errorMessage = document.getElementById('errorMessage');
            
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
            document.body.style.overflow = '';
            deleteForm.reset();
            
            // Hide error message
            if (errorMessage) {
                errorMessage.classList.add('hidden');
            }
            
            // Reset button state
            if (deleteButton) {
                deleteButton.disabled = true;
                deleteButton.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-green-600', 'hover:bg-green-700');
                deleteButton.classList.add('kt-btn-danger');
            }
            if (deleteButtonText) {
                deleteButtonText.innerHTML = '<i class="ki-filled ki-trash"></i> Delete Permanently';
            }
            
            // Re-enable form elements
            if (cancelButton) cancelButton.disabled = false;
            if (confirmationInput) confirmationInput.disabled = false;
            const reasonInput = document.getElementById('reason');
            if (reasonInput) reasonInput.disabled = false;
            const understandCheckbox = document.getElementById('understand');
            if (understandCheckbox) understandCheckbox.disabled = false;
        }

        // Enable delete button only when DELETE is typed and checkbox is checked
        document.addEventListener('DOMContentLoaded', function() {
            const confirmationInput = document.getElementById('confirmation_text');
            const understandCheckbox = document.getElementById('understand');
            const deleteButton = document.getElementById('deleteButton');
            const deleteButtonText = document.getElementById('deleteButtonText');
            const deleteForm = document.getElementById('deleteForm');
            const cancelButton = document.getElementById('cancelButton');

            function checkDeleteConditions() {
                const confirmationText = confirmationInput.value.trim();
                const isConfirmed = confirmationText === 'DELETE';
                const isUnderstood = understandCheckbox.checked;
                
                deleteButton.disabled = !(isConfirmed && isUnderstood);
            }

            confirmationInput.addEventListener('input', checkDeleteConditions);
            understandCheckbox.addEventListener('change', checkDeleteConditions);

            // Handle form submission via AJAX
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Hide any previous error messages
                const errorMessage = document.getElementById('errorMessage');
                if (errorMessage) {
                    errorMessage.classList.add('hidden');
                }

                // Get form data BEFORE disabling fields (disabled fields don't submit)
                const formData = new FormData(deleteForm);
                // Explicitly ensure confirmation_text is included
                const confirmationValue = confirmationInput.value.trim();
                if (confirmationValue) {
                    formData.set('confirmation_text', confirmationValue);
                }
                // Ensure _method is set for DELETE
                formData.set('_method', 'DELETE');
                const url = deleteForm.action;

                // Now disable form elements during deletion
                deleteButton.disabled = true;
                cancelButton.disabled = true;
                confirmationInput.disabled = true;
                document.getElementById('reason').disabled = true;
                document.getElementById('understand').disabled = true;

                // Update button to show loading state
                deleteButtonText.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deleting...</span>';
                deleteButton.classList.add('opacity-75', 'cursor-not-allowed');

                // Send AJAX request
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!response.ok) {
                        if (contentType && contentType.includes('application/json')) {
                            return response.json().then(data => {
                                // Handle Laravel validation errors (422 status)
                                if (data.errors) {
                                    const errorMessages = [];
                                    for (const field in data.errors) {
                                        if (Array.isArray(data.errors[field])) {
                                            errorMessages.push(...data.errors[field]);
                                        } else {
                                            errorMessages.push(data.errors[field]);
                                        }
                                    }
                                    throw new Error(errorMessages.join('. ') || data.message || 'Validation failed');
                                }
                                // Use the actual error message from the server
                                const errorMsg = data.message || data.error || 'An error occurred';
                                throw new Error(errorMsg);
                            });
                        } else {
                            // If not JSON, get text response
                            return response.text().then(text => {
                                // Try to extract error message from HTML if possible
                                const errorMatch = text.match(/<div[^>]*class="[^"]*error[^"]*"[^>]*>([^<]+)<\/div>/i);
                                if (errorMatch) {
                                    throw new Error(errorMatch[1]);
                                }
                                throw new Error('Server error: ' + response.statusText + ' (Status: ' + response.status + ')');
                            });
                        }
                    }
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If response is not JSON, it might be a redirect
                        throw new Error('Unexpected response format');
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        deleteButtonText.innerHTML = '<i class="ki-filled ki-check"></i> Deleted Successfully';
                        deleteButton.classList.remove('kt-btn-danger');
                        deleteButton.classList.add('bg-green-600', 'hover:bg-green-700');

                        // Close modal after a brief delay
                        setTimeout(() => {
                            closeDeleteModal();
                            
                            // Show success message and redirect
                            if (data.redirect_url) {
                                // Create a temporary success message element
                                const successDiv = document.createElement('div');
                                successDiv.className = 'fixed top-4 right-4 z-50 bg-green-600 text-white px-6 py-4 rounded-lg shadow-2xl border border-green-700';
                                successDiv.style.opacity = '1';
                                successDiv.style.backgroundColor = '#16a34a'; // green-600
                                successDiv.innerHTML = `
                                    <div class="flex items-center gap-3">
                                        <i class="ki-filled ki-check text-xl text-white font-bold"></i>
                                        <span class="text-white font-semibold text-base">${data.message}</span>
                                    </div>
                                `;
                                document.body.appendChild(successDiv);

                                // Redirect after showing message
                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 2000);
                            }
                        }, 1000);
                    }
                })
                .catch(error => {
                    // Re-enable form elements
                    deleteButton.disabled = false;
                    cancelButton.disabled = false;
                    confirmationInput.disabled = false;
                    document.getElementById('reason').disabled = false;
                    document.getElementById('understand').disabled = false;

                    // Reset button
                    deleteButtonText.innerHTML = '<i class="ki-filled ki-trash"></i> Delete Permanently';
                    deleteButton.classList.remove('opacity-75', 'cursor-not-allowed');

                    // Show error message in the modal
                    const errorMessage = document.getElementById('errorMessage');
                    const errorMessageText = document.getElementById('errorMessageText');
                    if (errorMessage && errorMessageText) {
                        errorMessageText.textContent = error.message || 'An error occurred while deleting the officer. Please try again.';
                        errorMessage.classList.remove('hidden');
                        
                        // Scroll to error message
                        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
        });

        // Close modal on outside click
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endsection


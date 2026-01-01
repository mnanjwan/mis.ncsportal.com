@extends('layouts.app')

@section('title', 'Review APER Form')
@section('page-title', 'Review APER Form')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.aper-forms.review') }}">APER Forms Review</a>
    <span>/</span>
    <span class="text-primary">Review Form</span>
@endsection

@section('content')
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
                    <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Officer Information -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officer Information</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Name</p>
                        <p class="text-sm font-medium">{{ $form->officer->initials }} {{ $form->officer->surname }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Service Number</p>
                        <p class="text-sm font-medium">{{ $form->officer->service_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Year</p>
                        <p class="text-sm font-medium">{{ $form->year }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Information -->
        <div class="kt-card bg-warning/10 border border-warning/20">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-warning">Officer's Rejection Reason</h3>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-foreground">{{ $form->rejection_reason }}</p>
                <p class="text-xs text-secondary-foreground mt-2">
                    Rejected on: {{ $form->rejected_at ? $form->rejected_at->format('d/m/Y H:i') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Form Details -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">APER Form Details</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Reporting Officer</p>
                        <p class="text-sm">{{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary-foreground mb-1">Countersigning Officer</p>
                        <p class="text-sm">{{ $form->countersigningOfficer ? $form->countersigningOfficer->email : 'Not Assigned' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('staff-officer.aper-forms.review') }}" class="kt-btn kt-btn-ghost">
                        <i class="ki-filled ki-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('staff-officer.aper-forms.export', $form->id) }}" class="kt-btn kt-btn-primary" target="_blank">
                        <i class="ki-filled ki-file-down"></i> View Full Form
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4">
                    <!-- Reassign Option -->
                    <div class="p-4 border border-border rounded-lg">
                        <h4 class="text-sm font-semibold mb-2">Option 1: Reassign</h4>
                        <p class="text-xs text-secondary-foreground mb-4">
                            Reassign the form to a different Reporting Officer or Countersigning Officer to restart the process.
                        </p>
                        <div class="flex gap-2">
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-warning" onclick="showReassignModal('reporting', {{ $form->id }})">
                                <i class="ki-filled ki-user-edit"></i> Reassign Reporting Officer
                            </button>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-warning" onclick="showReassignModal('countersigning', {{ $form->id }})">
                                <i class="ki-filled ki-user-edit"></i> Reassign Countersigning Officer
                            </button>
                        </div>
                    </div>

                    <!-- Finalize Option -->
                    <div class="p-4 border border-danger/20 rounded-lg bg-danger/5">
                        <h4 class="text-sm font-semibold mb-2 text-danger">Option 2: Finalize (Reject)</h4>
                        <p class="text-xs text-secondary-foreground mb-4">
                            Finalize this form. HRD will be able to access it and marks will be awarded. This action cannot be undone.
                        </p>
                        <form method="POST" action="{{ route('staff-officer.aper-forms.staff-officer-reject', $form->id) }}" 
                              onsubmit="return confirm('Are you sure you want to finalize this form? HRD will be able to access it and marks will be awarded.');">
                            @csrf
                            <div class="mb-3">
                                <label class="kt-label">Rejection Reason (Optional)</label>
                                <textarea name="staff_officer_rejection_reason" 
                                          class="kt-input" 
                                          rows="3" 
                                          placeholder="Optional reason for finalizing this form..."></textarea>
                            </div>
                            <button type="submit" class="kt-btn kt-btn-danger">
                                <i class="ki-filled ki-cross-circle"></i> Finalize Form
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reassign Modal -->
    <div id="reassign-modal" class="kt-modal hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeReassignModal()"></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="kt-modal-content max-w-[600px] relative bg-background rounded-lg shadow-xl w-full">
                <div class="kt-modal-header">
                    <h3 class="kt-modal-title" id="reassign-modal-title">Reassign Officer</h3>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-icon" onclick="closeReassignModal()">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <form id="reassign-form" method="POST">
                    @csrf
                    <div class="kt-modal-body">
                        <div class="mb-4">
                            <label class="kt-form-label">Search Officer by Email or Service Number</label>
                            <input type="text" 
                                   id="officer_search" 
                                   class="kt-input" 
                                   placeholder="Type email or service number to search..."
                                   autocomplete="off">
                            <p class="text-xs text-secondary-foreground mt-1">
                                Start typing to search for officers in the same command
                            </p>
                        </div>
                        
                        <div id="officer_results" class="mb-4 hidden">
                            <label class="kt-form-label">Select Officer <span class="text-danger">*</span></label>
                            <select name="reporting_officer_id" id="reporting_officer_id" class="kt-input hidden">
                                <option value="">-- Select Reporting Officer --</option>
                            </select>
                            <select name="countersigning_officer_id" id="countersigning_officer_id" class="kt-input hidden">
                                <option value="">-- Select Countersigning Officer --</option>
                            </select>
                            <div id="officer_list" class="border border-border rounded-lg max-h-60 overflow-y-auto"></div>
                        </div>

                        <input type="hidden" id="current_form_id">
                        <input type="hidden" id="current_reassign_type">
                    </div>
                    <div class="kt-modal-footer">
                        <button type="button" class="kt-btn kt-btn-secondary" onclick="closeReassignModal()">Cancel</button>
                        <button type="submit" class="kt-btn kt-btn-primary" id="reassign-submit-btn" disabled>
                            <i class="ki-filled ki-check"></i> Reassign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout = null;
        let selectedOfficerId = null;
        let currentFormId = null;
        let currentReassignType = null;

        function showReassignModal(type, formId) {
            currentFormId = formId;
            currentReassignType = type;
            selectedOfficerId = null;
            
            const modal = document.getElementById('reassign-modal');
            const form = document.getElementById('reassign-form');
            const title = document.getElementById('reassign-modal-title');
            const searchInput = document.getElementById('officer_search');
            const reportingSelect = document.getElementById('reporting_officer_id');
            const countersigningSelect = document.getElementById('countersigning_officer_id');
            const submitBtn = document.getElementById('reassign-submit-btn');
            
            // Set form action based on type
            if (type === 'reporting') {
                form.action = `/staff-officer/aper-forms/${formId}/reassign-reporting-officer`;
                title.textContent = 'Reassign Reporting Officer';
                reportingSelect.classList.remove('hidden');
                countersigningSelect.classList.add('hidden');
                countersigningSelect.removeAttribute('name');
                reportingSelect.setAttribute('name', 'reporting_officer_id');
            } else {
                form.action = `/staff-officer/aper-forms/${formId}/reassign-countersigning-officer`;
                title.textContent = 'Reassign Countersigning Officer';
                countersigningSelect.classList.remove('hidden');
                reportingSelect.classList.add('hidden');
                reportingSelect.removeAttribute('name');
                countersigningSelect.setAttribute('name', 'countersigning_officer_id');
            }
            
            // Reset form
            searchInput.value = '';
            document.getElementById('officer_results').classList.add('hidden');
            document.getElementById('officer_list').innerHTML = '';
            submitBtn.disabled = true;
            selectedOfficerId = null;
            
            // Show modal
            modal.classList.remove('hidden');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus search input
            setTimeout(() => searchInput.focus(), 100);
        }

        function closeReassignModal() {
            const modal = document.getElementById('reassign-modal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Reset form
            document.getElementById('officer_search').value = '';
            document.getElementById('officer_results').classList.add('hidden');
            document.getElementById('officer_list').innerHTML = '';
            document.getElementById('reassign-submit-btn').disabled = true;
            selectedOfficerId = null;
        }

        // Search functionality
        document.getElementById('officer_search').addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                document.getElementById('officer_results').classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                searchOfficers(query);
            }, 300);
        });

        function searchOfficers(query) {
            // Get command ID from the form's officer
            const commandId = {{ $form->officer->present_station }};
            
            // Use fetch to search for users/officers
            fetch(`/staff-officer/aper-forms/search-users?q=${encodeURIComponent(query)}&command_id=${commandId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayOfficers(data);
            })
            .catch(error => {
                console.error('Error searching officers:', error);
                // Fallback: Show error message
                document.getElementById('officer_list').innerHTML = 
                    '<div class="p-4 text-center text-secondary-foreground">Error searching officers. Please try again.</div>';
                document.getElementById('officer_results').classList.remove('hidden');
            });
        }

        function displayOfficers(officers) {
            const resultsDiv = document.getElementById('officer_results');
            const listDiv = document.getElementById('officer_list');
            
            if (!officers || officers.length === 0) {
                listDiv.innerHTML = '<div class="p-4 text-center text-secondary-foreground">No officers found</div>';
                resultsDiv.classList.remove('hidden');
                return;
            }
            
            let html = '<div class="divide-y divide-border">';
            officers.forEach(officer => {
                const displayName = officer.officer ? 
                    `${officer.officer.initials} ${officer.officer.surname} (${officer.email})` : 
                    officer.email;
                html += `
                    <div class="p-3 hover:bg-muted/50 cursor-pointer transition-colors" 
                         onclick="selectOfficer(${officer.id}, '${displayName.replace(/'/g, "\\'")}')">
                        <div class="font-medium text-foreground">${displayName}</div>
                        ${officer.officer ? `<div class="text-xs text-secondary-foreground">Service: ${officer.officer.service_number || 'N/A'}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
            
            listDiv.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        }

        function selectOfficer(userId, displayName) {
            selectedOfficerId = userId;
            
            const reportingSelect = document.getElementById('reporting_officer_id');
            const countersigningSelect = document.getElementById('countersigning_officer_id');
            const submitBtn = document.getElementById('reassign-submit-btn');
            
            // Clear previous selections
            reportingSelect.innerHTML = '<option value="">-- Select Reporting Officer --</option>';
            countersigningSelect.innerHTML = '<option value="">-- Select Countersigning Officer --</option>';
            
            if (currentReassignType === 'reporting') {
                reportingSelect.innerHTML = `<option value="${userId}" selected>${displayName}</option>`;
            } else {
                countersigningSelect.innerHTML = `<option value="${userId}" selected>${displayName}</option>`;
            }
            
            submitBtn.disabled = false;
            
            // Highlight selected
            document.querySelectorAll('#officer_list > div > div').forEach(div => {
                div.classList.remove('bg-primary/10', 'border-l-4', 'border-primary');
            });
            event.target.closest('.p-3').classList.add('bg-primary/10', 'border-l-4', 'border-primary');
        }
    </script>
@endsection


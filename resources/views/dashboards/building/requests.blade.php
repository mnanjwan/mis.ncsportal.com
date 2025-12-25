@extends('layouts.app')

@section('title', 'Quarter Requests')
@section('page-title', 'Quarter Requests Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('building.dashboard') }}">Building Unit</a>
    <span>/</span>
    <span class="text-primary">Quarter Requests</span>
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Quarter Requests</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 min-w-[250px] w-full md:w-auto">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <input type="text" 
                                   id="search-input"
                                   class="kt-input w-full" 
                                   placeholder="Search by officer name, service number...">
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select id="filter-status" class="kt-input w-full" onchange="loadRequests()">
                                <option value="">All Requests</option>
                                <option value="PENDING">Pending</option>
                                <option value="APPROVED">Approved</option>
                                <option value="REJECTED">Rejected</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="button" onclick="loadRequests()" class="kt-btn kt-btn-primary w-full md:w-auto">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            <button type="button" onclick="clearFilters()" class="kt-btn kt-btn-outline w-full md:w-auto" id="clear-btn" style="display: none;">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quarter Requests Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">All Quarter Requests</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Mobile scroll hint -->
                <div class="block md:hidden px-4 py-3 bg-muted/50 border-b border-border">
                    <div class="flex items-center gap-2 text-xs text-secondary-foreground">
                        <i class="ki-filled ki-arrow-left-right"></i>
                        <span>Swipe left to view more columns</span>
                    </div>
                </div>

                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Request Date
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Officer Details
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Service Number
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Preferred Type
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Status
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="requests-list">
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                    <p class="text-secondary-foreground">Loading requests...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approve-modal" class="kt-modal" style="display: none;">
        <div class="kt-modal-content w-full max-w-[500px] mx-4 md:mx-auto">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Approve Quarter Request</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" onclick="closeApproveModal()">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form id="approve-form" class="kt-modal-body">
                <input type="hidden" id="approve-request-id">
                
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Quarter <span class="text-danger">*</span></label>
                        <select id="approve-quarter-id" name="quarter_id" class="kt-input w-full" required>
                            <option value="">Loading available quarters...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Allocation Date</label>
                        <input type="date" id="approve-allocation-date" name="allocation_date" class="kt-input w-full" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="kt-modal-footer py-4 px-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary w-full sm:w-auto" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary w-full sm:w-auto">Approve & Allocate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="kt-modal" style="display: none;">
        <div class="kt-modal-content w-full max-w-[500px] mx-4 md:mx-auto">
            <div class="kt-modal-header py-4 px-5">
                <h3 class="text-lg font-semibold text-foreground">Reject Quarter Request</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" onclick="closeRejectModal()">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form id="reject-form" class="kt-modal-body">
                <input type="hidden" id="reject-request-id">
                
                <div class="kt-alert kt-alert-warning mb-4">
                    <i class="ki-filled ki-information"></i>
                    <div>
                        <strong>Note:</strong> You can only reject a request once. After rejection, it cannot be rejected again.
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea id="reject-reason" name="rejection_reason" class="kt-textarea w-full" rows="4" required placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>

                <div class="kt-modal-footer py-4 px-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary w-full sm:w-auto" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger w-full sm:w-auto">Reject Request</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    @push('scripts')
    <script>
    let requestsMap = {};

    document.addEventListener('DOMContentLoaded', async () => {
        await loadRequests();
        document.getElementById('approve-form').addEventListener('submit', handleApprove);
        document.getElementById('reject-form').addEventListener('submit', handleReject);
        
        // Search on Enter key
        document.getElementById('search-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                loadRequests();
            }
        });
    });

    async function loadRequests() {
        try {
            const token = window.API_CONFIG?.token;
            if (!token) {
                console.error('API token not found');
                showError('Authentication error. Please refresh the page.');
                return;
            }

            const filter = document.getElementById('filter-status').value;
            const search = document.getElementById('search-input').value.trim();
            let url = '/api/v1/quarters/requests';
            const params = new URLSearchParams();
            
            if (filter) {
                params.append('status', filter);
            }
            if (search) {
                params.append('search', search);
            }
            
            if (params.toString()) {
                url += '?' + params.toString();
            }

            const res = await fetch(url, {
                headers: { 
                    'Authorization': 'Bearer ' + token, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await res.json();
            
            if (res.ok && data.success) {
                const requests = data.data || [];
                requestsMap = {};
                requests.forEach(req => {
                    requestsMap[req.id] = req;
                });
                renderRequests(requests);
                updateClearButton();
            } else {
                const errorMsg = data.message || 'Failed to load requests';
                console.error('API Error:', errorMsg);
                
                if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                    showError('You must be assigned to a command to view requests. Please contact HRD.');
                    renderRequests([]);
                } else {
                    showError(errorMsg);
                    renderRequests([]);
                }
            }
        } catch (error) {
            console.error('Error loading requests:', error);
            showError('An error occurred while loading requests');
            renderRequests([]);
        }
    }

    function renderRequests(requests) {
        const tbody = document.getElementById('requests-list');
        
        if (requests.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-12 text-center">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No quarter requests found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = requests.map(request => {
            const statusBadge = getStatusBadge(request.status);
            const date = new Date(request.created_at).toLocaleDateString('en-GB');
            const officer = request.officer || {};
            const officerName = `${officer.initials || ''} ${officer.surname || ''}`.trim() || 'N/A';
            const serviceNumber = officer.service_number || 'N/A';
            const preferredType = request.preferred_quarter_type || 'Any';
            const avatarInitials = ((officer.initials?.[0] || '') + (officer.surname?.[0] || '')).toUpperCase();

            let actions = '';
            if (request.status === 'PENDING') {
                actions = `
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="openApproveModal(${request.id})" class="kt-btn kt-btn-sm kt-btn-success" title="Approve Request">
                            <i class="ki-filled ki-check"></i> Approve
                        </button>
                        <button onclick="openRejectModal(${request.id})" class="kt-btn kt-btn-sm kt-btn-danger" title="Reject Request">
                            <i class="ki-filled ki-cross"></i> Reject
                        </button>
                    </div>
                `;
            } else if (request.status === 'APPROVED') {
                const quarterInfo = request.quarter ? `${request.quarter.quarter_number} (${request.quarter.quarter_type})` : 'N/A';
                actions = `<span class="text-success text-sm">Approved - ${quarterInfo}</span>`;
            } else if (request.status === 'REJECTED') {
                actions = `<span class="text-danger text-sm">Rejected</span>`;
            }

            return `
                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                        ${date}
                    </td>
                    <td class="py-3 px-4" style="white-space: nowrap;">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                ${avatarInitials || 'N/A'}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-foreground">${officerName}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4" style="white-space: nowrap;">
                        <span class="text-sm font-mono text-foreground">${serviceNumber}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                        ${preferredType}
                    </td>
                    <td class="py-3 px-4" style="white-space: nowrap;">
                        ${statusBadge}
                    </td>
                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                        ${actions}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'PENDING': '<span class="kt-badge kt-badge-warning">Pending</span>',
            'APPROVED': '<span class="kt-badge kt-badge-success">Approved</span>',
            'REJECTED': '<span class="kt-badge kt-badge-danger">Rejected</span>'
        };
        return badges[status] || status;
    }

    function updateClearButton() {
        const search = document.getElementById('search-input').value.trim();
        const filter = document.getElementById('filter-status').value;
        const clearBtn = document.getElementById('clear-btn');
        
        if (search || filter) {
            clearBtn.style.display = 'block';
        } else {
            clearBtn.style.display = 'none';
        }
    }

    function clearFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-status').value = '';
        loadRequests();
    }

    async function openApproveModal(requestId) {
        const request = requestsMap[requestId];
        if (!request) {
            showError('Request not found');
            return;
        }

        document.getElementById('approve-request-id').value = requestId;
        await loadAvailableQuarters();
        
        // Show modal
        const modal = document.getElementById('approve-modal');
        modal.style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approve-modal').style.display = 'none';
        document.getElementById('approve-form').reset();
    }

    async function openRejectModal(requestId) {
        const request = requestsMap[requestId];
        if (!request) {
            showError('Request not found');
            return;
        }

        if (request.status === 'REJECTED') {
            showError('This request has already been rejected and cannot be rejected again');
            return;
        }

        document.getElementById('reject-request-id').value = requestId;
        document.getElementById('reject-reason').value = '';
        
        // Show modal
        const modal = document.getElementById('reject-modal');
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('reject-modal').style.display = 'none';
        document.getElementById('reject-form').reset();
    }

    async function loadAvailableQuarters() {
        try {
            const token = window.API_CONFIG?.token;
            if (!token) {
                console.error('API token not found');
                return;
            }

            const res = await fetch('/api/v1/quarters?is_occupied=0', {
                headers: { 
                    'Authorization': 'Bearer ' + token, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await res.json();
            
            if (res.ok && data.success) {
                const quarters = data.data || [];
                const select = document.getElementById('approve-quarter-id');
                select.innerHTML = '<option value="">Select a quarter</option>';
                
                quarters.forEach(quarter => {
                    const option = document.createElement('option');
                    option.value = quarter.id;
                    option.textContent = `${quarter.quarter_number} (${quarter.quarter_type})`;
                    select.appendChild(option);
                });
            } else {
                console.error('Failed to load quarters:', data.message);
                document.getElementById('approve-quarter-id').innerHTML = '<option value="">Error loading quarters</option>';
            }
        } catch (error) {
            console.error('Error loading quarters:', error);
            document.getElementById('approve-quarter-id').innerHTML = '<option value="">Error loading quarters</option>';
        }
    }

    async function handleApprove(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ki-filled ki-loader"></i> Approving...';

        try {
            const token = window.API_CONFIG?.token;
            if (!token) {
                throw new Error('API token not found');
            }

            const requestId = document.getElementById('approve-request-id').value;
            const formData = {
                quarter_id: document.getElementById('approve-quarter-id').value,
                allocation_date: document.getElementById('approve-allocation-date').value || new Date().toISOString().split('T')[0],
            };

            const res = await fetch(`/api/v1/quarters/requests/${requestId}/approve`, {
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
                alert('Request approved and quarter allocated successfully!');
                closeApproveModal();
                await loadRequests();
            } else {
                const errorMsg = data.message || 'Failed to approve request';
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Error approving request:', error);
            alert('An error occurred while approving the request');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async function handleReject(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ki-filled ki-loader"></i> Rejecting...';

        try {
            const token = window.API_CONFIG?.token;
            if (!token) {
                throw new Error('API token not found');
            }

            const requestId = document.getElementById('reject-request-id').value;
            const formData = {
                rejection_reason: document.getElementById('reject-reason').value,
            };

            const res = await fetch(`/api/v1/quarters/requests/${requestId}/reject`, {
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
                alert('Request rejected successfully!');
                closeRejectModal();
                await loadRequests();
            } else {
                const errorMsg = data.message || 'Failed to reject request';
                
                if (data.meta?.code === 'ALREADY_REJECTED') {
                    alert('This request has already been rejected and cannot be rejected again');
                } else {
                    alert(errorMsg);
                }
            }
        } catch (error) {
            console.error('Error rejecting request:', error);
            alert('An error occurred while rejecting the request');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    function showError(message) {
        console.error(message);
        alert(message);
    }
    </script>
    @endpush
@endsection


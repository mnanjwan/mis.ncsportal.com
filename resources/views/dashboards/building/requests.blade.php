@extends('layouts.app')

@section('title', 'Quarter Requests')
@section('page-title', 'Quarter Requests Management')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filter -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <select id="filter-status" class="kt-select" onchange="loadRequests()">
                    <option value="">All Requests</option>
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quarter Requests</h3>
        </div>
        <div class="kt-card-content">
            <div class="overflow-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>Request Date</th>
                            <th>Officer</th>
                            <th>Service Number</th>
                            <th>Preferred Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requests-list">
                        <tr>
                            <td colspan="6" class="text-center py-8 text-secondary-foreground">
                                Loading requests...
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
    <div class="kt-modal-content">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Approve Quarter Request</h3>
            <button type="button" class="kt-modal-close" onclick="closeApproveModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="approve-form" class="kt-modal-body">
            <input type="hidden" id="approve-request-id">
            
            <div class="flex flex-col gap-2 mb-4">
                <label class="kt-form-label">Select Quarter <span class="text-danger">*</span></label>
                <select id="approve-quarter-id" name="quarter_id" class="kt-select" required>
                    <option value="">Loading available quarters...</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label class="kt-form-label">Allocation Date</label>
                <input type="date" id="approve-allocation-date" name="allocation_date" class="kt-input" value="{{ date('Y-m-d') }}">
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" class="kt-btn kt-btn-secondary" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Approve & Allocate</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="kt-modal" style="display: none;">
    <div class="kt-modal-content">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Reject Quarter Request</h3>
            <button type="button" class="kt-modal-close" onclick="closeRejectModal()">
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

            <div class="flex flex-col gap-2 mb-4">
                <label class="kt-form-label">Rejection Reason <span class="text-danger">*</span></label>
                <textarea id="reject-reason" name="rejection_reason" class="kt-textarea" rows="4" required placeholder="Enter reason for rejection..."></textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" class="kt-btn kt-btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-danger">Reject Request</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let requestsMap = {};

document.addEventListener('DOMContentLoaded', async () => {
    await loadRequests();
    document.getElementById('approve-form').addEventListener('submit', handleApprove);
    document.getElementById('reject-form').addEventListener('submit', handleReject);
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
        let url = '/api/v1/quarters/requests';
        if (filter) {
            url += `?status=${filter}`;
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
                <td colspan="6" class="text-center py-8 text-secondary-foreground">
                    No quarter requests found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = requests.map(request => {
        const statusBadge = getStatusBadge(request.status);
        const date = new Date(request.created_at).toLocaleDateString();
        const officerName = request.officer ? `${request.officer.initials} ${request.officer.surname}` : 'N/A';
        const serviceNumber = request.officer?.service_number || 'N/A';
        const preferredType = request.preferred_quarter_type || 'Any';

        let actions = '';
        if (request.status === 'PENDING') {
            actions = `
                <div class="flex gap-2">
                    <button onclick="openApproveModal(${request.id})" class="kt-btn kt-btn-sm kt-btn-success">
                        <i class="ki-filled ki-check"></i> Approve
                    </button>
                    <button onclick="openRejectModal(${request.id})" class="kt-btn kt-btn-sm kt-btn-danger">
                        <i class="ki-filled ki-cross"></i> Reject
                    </button>
                </div>
            `;
        } else if (request.status === 'APPROVED') {
            const quarterInfo = request.quarter ? `${request.quarter.quarter_number} (${request.quarter.quarter_type})` : 'N/A';
            actions = `<span class="text-success">Approved - ${quarterInfo}</span>`;
        } else if (request.status === 'REJECTED') {
            actions = `<span class="text-danger">Rejected</span>`;
        }

        return `
            <tr>
                <td>${date}</td>
                <td>${officerName}</td>
                <td>${serviceNumber}</td>
                <td>${preferredType}</td>
                <td>${statusBadge}</td>
                <td>${actions}</td>
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

async function openApproveModal(requestId) {
    const request = requestsMap[requestId];
    if (!request) {
        showError('Request not found');
        return;
    }

    document.getElementById('approve-request-id').value = requestId;
    await loadAvailableQuarters();
    document.getElementById('approve-modal').style.display = 'flex';
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
    document.getElementById('reject-modal').style.display = 'flex';
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



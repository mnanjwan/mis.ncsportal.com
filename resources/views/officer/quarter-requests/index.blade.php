@extends('layouts.app')

@section('title', 'My Quarter Requests')
@section('page-title', 'My Quarter Requests')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Actions -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('officer.quarter-requests.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Request Quarter
                </a>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">My Quarter Requests</h3>
        </div>
        <div class="kt-card-content">
            <div class="overflow-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>Request Date</th>
                            <th>Preferred Type</th>
                            <th>Status</th>
                            <th>Quarter Allocated</th>
                            <th>Rejection Reason</th>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadRequests();
});

async function loadRequests() {
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            console.error('API token not found');
            showError('Authentication error. Please refresh the page.');
            return;
        }

        const res = await fetch('/api/v1/quarters/my-requests', {
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            const requests = data.data || [];
            renderRequests(requests);
        } else {
            const errorMsg = data.message || 'Failed to load requests';
            console.error('API Error:', errorMsg);
            showError(errorMsg);
            renderRequests([]);
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
                    No quarter requests found. <a href="{{ route('officer.quarter-requests.create') }}" class="text-primary hover:underline">Create one</a>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = requests.map(request => {
        const statusBadge = getStatusBadge(request.status);
        const date = new Date(request.created_at).toLocaleDateString();
        const quarterInfo = request.quarter ? `${request.quarter.quarter_number} (${request.quarter.quarter_type})` : '-';
        const preferredType = request.preferred_quarter_type || 'Any';
        const rejectionReason = request.rejection_reason || '-';

        return `
            <tr>
                <td>${date}</td>
                <td>${preferredType}</td>
                <td>${statusBadge}</td>
                <td>${quarterInfo}</td>
                <td>${rejectionReason}</td>
                <td>
                    ${request.status === 'PENDING' ? '<span class="text-secondary-foreground">Pending review</span>' : ''}
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

function showError(message) {
    // You can implement a toast notification here
    console.error(message);
    alert(message);
}
</script>
@endpush






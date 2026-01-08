@extends('layouts.app')

@section('title', 'My Quarter Requests')
@section('page-title', 'My Quarter Requests')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
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
        <!-- Quarter Requests List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quarter Request History</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('officer.quarter-requests.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Request Quarter
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 800px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Request Date
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Preferred Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Quarter Allocated
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Rejection Reason
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="requests-list">
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-loader text-2xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground">Loading requests...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4" id="requests-mobile-list">
                        <div class="text-center py-12">
                            <i class="ki-filled ki-loader text-2xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">Loading requests...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="confirm-modal">
        <div class="kt-modal-content max-w-[400px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
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
        const mobileList = document.getElementById('requests-mobile-list');
        
        if (requests.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-12 text-center">
                        <i class="ki-filled ki-home-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No quarter requests found</p>
                        <a href="{{ route('officer.quarter-requests.create') }}" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Request Quarter
                        </a>
                    </td>
                </tr>
            `;
            
            mobileList.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-filled ki-home-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground mb-4">No quarter requests found</p>
                    <a href="{{ route('officer.quarter-requests.create') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Request Quarter
                    </a>
                </div>
            `;
            return;
        }

        // Desktop table view
        tbody.innerHTML = requests.map(request => {
            const statusBadge = getStatusBadge(request.status);
            const date = new Date(request.created_at).toLocaleDateString('en-GB', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            const quarterInfo = request.quarter ? `${request.quarter.quarter_number} (${request.quarter.quarter_type})` : '-';
            const preferredType = request.preferred_quarter_type || 'Any';
            const rejectionReason = request.rejection_reason || '-';

            return `
                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                        ${date}
                    </td>
                    <td class="py-3 px-4 text-sm text-foreground" style="white-space: nowrap;">
                        ${preferredType}
                    </td>
                    <td class="py-3 px-4" style="white-space: nowrap;">
                        ${statusBadge}
                    </td>
                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                        ${quarterInfo}
                    </td>
                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                        ${rejectionReason}
                    </td>
                    <td class="py-3 px-4" style="white-space: nowrap;">
                        ${request.status === 'PENDING' ? '<span class="text-xs text-secondary-foreground">Pending review</span>' : ''}
                    </td>
                </tr>
            `;
        }).join('');

        // Mobile card view
        mobileList.innerHTML = requests.map(request => {
            const statusBadge = getStatusBadge(request.status);
            const date = new Date(request.created_at).toLocaleDateString('en-GB', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            const quarterInfo = request.quarter ? `${request.quarter.quarter_number} (${request.quarter.quarter_type})` : 'Not allocated';
            const preferredType = request.preferred_quarter_type || 'Any';
            const rejectionReason = request.rejection_reason ? `<div class="text-xs text-danger mt-1">${request.rejection_reason}</div>` : '';

            return `
                <div class="flex items-start justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-home-2 text-info text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-foreground">${preferredType}</span>
                                ${statusBadge}
                            </div>
                            <span class="text-xs text-secondary-foreground">
                                Requested: ${date}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Quarter: ${quarterInfo}
                            </span>
                            ${rejectionReason}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'PENDING': '<span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>',
            'APPROVED': '<span class="kt-badge kt-badge-success kt-badge-sm">Approved</span>',
            'REJECTED': '<span class="kt-badge kt-badge-danger kt-badge-sm">Rejected</span>'
        };
        return badges[status] || status;
    }

    function showError(message) {
        // Show error using alert for now, can be replaced with toast notification
        console.error(message);
        showConfirmModal(
            'Error',
            message,
            () => {},
            'error'
        );
    }

    function showConfirmModal(title, message, onConfirm, type = 'warning') {
        const modal = document.getElementById('confirm-modal');
        const modalTitle = document.getElementById('confirm-modal-title');
        const modalMessage = document.getElementById('confirm-modal-message');
        const confirmBtn = document.getElementById('confirm-modal-confirm');
        const cancelBtn = document.getElementById('confirm-modal-cancel');
        const iconDiv = modal.querySelector('.flex.items-center.gap-3 .flex.items-center.justify-center');

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
            cancelBtn.click();
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

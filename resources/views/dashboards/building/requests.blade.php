@extends('layouts.app')

@section('title', 'Quarter Requests')
@section('page-title', 'Quarter Requests Management')

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
                            <input type="text" id="search-input" class="kt-input w-full"
                                placeholder="Search by officer name, service number...">
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <div class="relative">
                                <input type="hidden" id="filter-status" value="">
                                <button type="button" 
                                        id="status_select_trigger" 
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="status_select_text">All Requests</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="status_dropdown" 
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text" 
                                               id="status_search_input" 
                                               class="kt-input w-full pl-10" 
                                               placeholder="Search status..."
                                               autocomplete="off">
                                    </div>
                                    <div id="status_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="button" onclick="loadRequests(1)" class="kt-btn kt-btn-primary w-full md:w-auto">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            <button type="button" onclick="clearFilters()" class="kt-btn kt-btn-outline w-full md:w-auto"
                                id="clear-btn" style="display: none;">
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
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
                                    <a href="javascript:void(0)" onclick="sortTable('created_at')"
                                        class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                        Request Date
                                        <span id="sort-icon-created_at" class="sort-icon opacity-50">
                                            <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                        </span>
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
                                    <a href="javascript:void(0)" onclick="sortTable('surname')"
                                        class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                        Officer Details
                                        <span id="sort-icon-surname" class="sort-icon opacity-50">
                                            <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                        </span>
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
                                    <a href="javascript:void(0)" onclick="sortTable('service_number')"
                                        class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                        Service Number
                                        <span id="sort-icon-service_number" class="sort-icon opacity-50">
                                            <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                        </span>
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
                                    <a href="javascript:void(0)" onclick="sortTable('preferred_quarter_type')"
                                        class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                        Preferred Type
                                        <span id="sort-icon-preferred_quarter_type" class="sort-icon opacity-50">
                                            <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                        </span>
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
                                    <a href="javascript:void(0)" onclick="sortTable('status')"
                                        class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                        Status
                                        <span id="sort-icon-status" class="sort-icon opacity-50">
                                            <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                        </span>
                                    </a>
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground"
                                    style="white-space: nowrap;">
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

                <!-- Pagination -->
                <div id="pagination" class="mt-6 pt-4 border-t border-border px-4 pb-4"></div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approve-modal" class="kt-modal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                        <i class="ki-filled ki-check text-success text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Approve Quarter Request</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form id="approve-form">
                <input type="hidden" id="approve-request-id">
                <div class="kt-modal-body py-5 px-5">
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Select Quarter <span
                                    class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="text" id="approve-quarter-search" class="kt-input w-full"
                                    placeholder="Search quarters by number or type..." autocomplete="off">
                                <input type="hidden" id="approve-quarter-id" name="quarter_id">
                                <div id="approve-quarter-dropdown"
                                    class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                            <div id="selected-approve-quarter" class="hidden mt-2 p-2 bg-muted/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium" id="selected-approve-quarter-name"></span>
                                        <span class="text-xs text-secondary-foreground"
                                            id="selected-approve-quarter-details"></span>
                                    </div>
                                    <button type="button" id="clear-approve-quarter"
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                        <i class="ki-filled ki-cross"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Allocation Date</label>
                            <input type="date" id="approve-allocation-date" name="allocation_date" class="kt-input w-full"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Approve & Allocate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="kt-modal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                        <i class="ki-filled ki-cross text-danger text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Reject Quarter Request</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <form id="reject-form">
                <input type="hidden" id="reject-request-id">
                <div class="kt-modal-body py-5 px-5">
                    <div class="kt-alert kt-alert-warning mb-4">
                        <i class="ki-filled ki-information"></i>
                        <div>
                            <strong>Note:</strong> You can only reject a request once. After rejection, it cannot be
                            rejected again.
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Rejection Reason <span
                                    class="text-danger">*</span></label>
                            <textarea id="reject-reason" name="rejection_reason" class="kt-textarea w-full" rows="4"
                                required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger">Reject Request</button>
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
            let quartersCache = [];
            let currentPage = 1;
            let currentSort = 'created_at';
            let currentOrder = 'desc';

            document.addEventListener('DOMContentLoaded', async () => {
                console.log('DOMContentLoaded - Initializing requests page');

                // Show loading state
                const tbody = document.getElementById('requests-list');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                <p class="text-secondary-foreground">Loading requests...</p>
                            </td>
                        </tr>
                    `;
                }

                try {
                    console.log('Calling loadRequests()...');
                    await loadRequests();
                    console.log('loadRequests() completed');
                } catch (error) {
                    console.error('Error initializing requests page:', error);
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-information-2 text-4xl text-danger mb-4"></i>
                                    <p class="text-danger">Failed to load requests. Please refresh the page.</p>
                                    <p class="text-xs text-secondary-foreground mt-2">${error.message || 'Unknown error'}</p>
                                </td>
                            </tr>
                        `;
                    }
                }

                const approveForm = document.getElementById('approve-form');
                const rejectForm = document.getElementById('reject-form');

                if (approveForm) {
                    approveForm.addEventListener('submit', handleApprove);
                }
                if (rejectForm) {
                    rejectForm.addEventListener('submit', handleReject);
                }

                // Search on Enter key
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            loadRequests(1);
                        }
                    });
                }
            });

            function sortTable(column) {
                if (currentSort === column) {
                    // Toggle order if same column
                    currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    // New column, default to ascending
                    currentSort = column;
                    currentOrder = 'asc';
                }

                // Update all sort icons
                document.querySelectorAll('.sort-icon').forEach(icon => {
                    icon.innerHTML = '<i class="ki-filled ki-arrow-up-down text-xs"></i>';
                    icon.classList.add('opacity-50');
                    icon.classList.remove('text-primary');
                });

                // Update current sort icon
                const currentIcon = document.getElementById(`sort-icon-${column}`);
                if (currentIcon) {
                    currentIcon.innerHTML = `<i class="ki-filled ki-arrow-${currentOrder === 'asc' ? 'up' : 'down'} text-xs"></i>`;
                    currentIcon.classList.remove('opacity-50');
                    currentIcon.classList.add('text-primary');
                }

                // Reload with new sort
                loadRequests(1);
            }

            async function loadRequests(page = 1) {
                const tbody = document.getElementById('requests-list');

                // Show loading state
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                <p class="text-secondary-foreground">Loading requests...</p>
                            </td>
                        </tr>
                    `;
                }

                try {
                    const filter = document.getElementById('filter-status')?.value || '';
                    const search = document.getElementById('search-input')?.value?.trim() || '';
                    let url = '/api/v1/quarters/requests';
                    const params = new URLSearchParams();

                    params.append('page', page);
                    params.append('per_page', '20');
                    params.append('sort', currentSort);
                    params.append('order', currentOrder);

                    if (filter) {
                        params.append('status', filter);
                    }
                    if (search) {
                        params.append('search', search);
                    }

                    if (params.toString()) {
                        url += '?' + params.toString();
                    }

                    // Prepare headers
                    const headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    // Add Bearer token if available
                    const token = window.API_CONFIG?.token;
                    if (token) {
                        headers['Authorization'] = 'Bearer ' + token;
                    }

                    // Add CSRF token for Sanctum session-based auth
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                        headers['X-CSRF-TOKEN'] = csrfToken;
                        headers['X-XSRF-TOKEN'] = csrfToken;
                    }

                    console.log('Fetching from URL:', url);
                    console.log('Headers:', {
                        'Accept': headers['Accept'],
                        'Content-Type': headers['Content-Type'],
                        'X-Requested-With': headers['X-Requested-With'],
                        'Authorization': headers['Authorization'] ? 'Bearer ***' : 'none',
                        'X-CSRF-TOKEN': headers['X-CSRF-TOKEN'] ? 'present' : 'none'
                    });

                    const res = await fetch(url, {
                        method: 'GET',
                        headers: headers,
                        credentials: 'same-origin' // Include cookies for session-based auth
                    });

                    console.log('Response received:', res.status, res.statusText);

                    console.log('Response status:', res.status, res.statusText);

                    if (!res.ok) {
                        // Try to get error message from response
                        let errorData;
                        const contentType = res.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            try {
                                errorData = await res.json();
                                console.error('API Error Response:', errorData);
                            } catch (e) {
                                console.error('Failed to parse error response as JSON:', e);
                                errorData = { message: `HTTP ${res.status}: ${res.statusText}` };
                            }
                        } else {
                            const text = await res.text();
                            console.error('Non-JSON error response:', text);
                            errorData = { message: `HTTP ${res.status}: ${res.statusText}` };
                        }
                        throw new Error(errorData.message || `Failed to load requests: ${res.status}`);
                    }

                    const data = await res.json();
                    console.log('API Response received:', data);
                    console.log('Response keys:', Object.keys(data));

                    if (data.success !== undefined && !data.success) {
                        const errorMsg = data.message || 'Failed to load requests';
                        console.error('API returned success=false:', errorMsg, data);

                        if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                            showError('You must be assigned to a command to view requests. Please contact HRD.');
                        } else {
                            showError(errorMsg);
                        }
                        renderRequests([]);
                        renderPagination(null);
                        return;
                    }

                    // Handle response - could be success: true or just data directly
                    const requests = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
                    currentPage = page;
                    requestsMap = {};
                    requests.forEach(req => {
                        requestsMap[req.id] = req;
                    });

                    console.log('Processing requests:', requests.length, 'items');
                    console.log('Requests data:', requests);

                    renderRequests(requests);
                    renderPagination(data.meta || data.pagination);
                    updateClearButton();

                    console.log('Successfully loaded and rendered requests');

                } catch (error) {
                    console.error('Error loading requests:', error);
                    console.error('Error stack:', error.stack);
                    const errorMsg = error.message || 'An error occurred while loading requests';
                    console.error('Displaying error:', errorMsg);

                    // Show error in table
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="ki-filled ki-information-2 text-4xl text-danger mb-4"></i>
                                    <p class="text-danger font-medium">Failed to load requests</p>
                                    <p class="text-xs text-secondary-foreground mt-2">${errorMsg}</p>
                                    <button onclick="loadRequests(${page})" class="kt-btn kt-btn-sm kt-btn-primary mt-3">
                                        <i class="ki-filled ki-arrows-circle"></i> Retry
                                    </button>
                                </td>
                            </tr>
                        `;
                    }

                    showError(errorMsg);
                    renderPagination(null);
                }
            }

            function renderPagination(meta) {
                const pagination = document.getElementById('pagination');

                if (!meta || meta.last_page <= 1) {
                    pagination.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-secondary-foreground">
                                Showing ${meta?.from || 0} to ${meta?.to || 0} of ${meta?.total || 0} requests
                            </div>
                        </div>
                    `;
                    return;
                }

                const current = meta.current_page || 1;
                const last = meta.last_page || 1;
                const total = meta.total || 0;
                const from = meta.from || 0;
                const to = meta.to || 0;

                let html = `
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-secondary-foreground">
                            Showing <span class="font-medium">${from}</span> to <span class="font-medium">${to}</span> of <span class="font-medium">${total}</span> requests
                        </div>
                        <div class="flex items-center gap-1 flex-wrap justify-center">
                `;

                // First & Previous buttons
                if (current > 1) {
                    html += `
                        <button onclick="loadRequests(1)" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
                            <i class="ki-filled ki-double-left"></i>
                        </button>
                        <button onclick="loadRequests(${current - 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
                            <i class="ki-filled ki-left"></i> Previous
                        </button>
                    `;
                }

                // Page numbers
                let startPage = Math.max(1, current - 2);
                let endPage = Math.min(last, current + 2);

                // Adjust if we're near the beginning
                if (current <= 3) {
                    endPage = Math.min(5, last);
                }

                // Adjust if we're near the end
                if (current >= last - 2) {
                    startPage = Math.max(1, last - 4);
                }

                // Show first page if not in range
                if (startPage > 1) {
                    html += `<button onclick="loadRequests(1)" class="kt-btn kt-btn-sm kt-btn-secondary">1</button>`;
                    if (startPage > 2) {
                        html += `<span class="px-2 text-secondary-foreground">...</span>`;
                    }
                }

                // Page number buttons
                for (let i = startPage; i <= endPage; i++) {
                    if (i === current) {
                        html += `<button class="kt-btn kt-btn-sm kt-btn-primary" disabled>${i}</button>`;
                    } else {
                        html += `<button onclick="loadRequests(${i})" class="kt-btn kt-btn-sm kt-btn-secondary">${i}</button>`;
                    }
                }

                // Show last page if not in range
                if (endPage < last) {
                    if (endPage < last - 1) {
                        html += `<span class="px-2 text-secondary-foreground">...</span>`;
                    }
                    html += `<button onclick="loadRequests(${last})" class="kt-btn kt-btn-sm kt-btn-secondary">${last}</button>`;
                }

                // Next & Last buttons
                if (current < last) {
                    html += `
                        <button onclick="loadRequests(${current + 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
                            Next <i class="ki-filled ki-right"></i>
                        </button>
                        <button onclick="loadRequests(${last})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
                            <i class="ki-filled ki-double-right"></i>
                        </button>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;

                pagination.innerHTML = html;
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
                loadRequests(1);
            }

            async function openApproveModal(requestId) {
                const request = requestsMap[requestId];
                if (!request) {
                    showError('Request not found');
                    return;
                }

                document.getElementById('approve-request-id').value = requestId;

                // Clear previous selection
                document.getElementById('approve-quarter-id').value = '';
                document.getElementById('approve-quarter-search').value = '';
                document.getElementById('selected-approve-quarter').classList.add('hidden');

                await loadAvailableQuarters();

                // Show modal using KTModal system
                const modal = document.getElementById('approve-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            function closeApproveModal() {
                const modal = document.getElementById('approve-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    modal.style.display = 'none';
                }
                document.getElementById('approve-form').reset();
                document.getElementById('approve-quarter-id').value = '';
                document.getElementById('approve-quarter-search').value = '';
                document.getElementById('selected-approve-quarter').classList.add('hidden');
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

                // Show modal using KTModal system
                const modal = document.getElementById('reject-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
                    modalInstance.show();
                } else {
                    modal.style.display = 'flex';
                }
            }

            function closeRejectModal() {
                const modal = document.getElementById('reject-modal');
                if (typeof KTModal !== 'undefined') {
                    const modalInstance = KTModal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    modal.style.display = 'none';
                }
                document.getElementById('reject-form').reset();
            }

            let quarterSelectInitialized = false;

            // Create searchable select function (similar to Manning Request pattern)
            // Uses global quartersCache variable so it always has latest data
            function setupApproveQuarterSelect() {
                if (quarterSelectInitialized) {
                    return; // Already initialized
                }

                const searchInput = document.getElementById('approve-quarter-search');
                const hiddenInput = document.getElementById('approve-quarter-id');
                const dropdown = document.getElementById('approve-quarter-dropdown');
                const selectedDiv = document.getElementById('selected-approve-quarter');
                const selectedName = document.getElementById('selected-approve-quarter-name');
                const selectedDetails = document.getElementById('selected-approve-quarter-details');

                if (!searchInput || !hiddenInput || !dropdown) {
                    return; // Elements not found
                }

                let selectedOption = null;

                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase().trim();

                    if (searchTerm.length === 0) {
                        dropdown.classList.add('hidden');
                        return;
                    }

                    // Use global quartersCache - always has latest data
                    const filtered = quartersCache.filter(opt => {
                        return ['quarter_number', 'quarter_type'].some(field => {
                            const value = String(opt[field] || '').toLowerCase();
                            return value.includes(searchTerm);
                        });
                    });

                    if (filtered.length === 0) {
                        dropdown.innerHTML = '<div class="p-3 text-secondary-foreground">No results found</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    dropdown.innerHTML = filtered.map(opt => {
                        const name = opt.display_name || 'N/A';
                        const details = opt.quarter_type || '';
                        return '<div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-0" data-id="' + opt.id + '" data-name="' + name.replace(/'/g, "&#39;") + '" data-details="' + details.replace(/'/g, "&#39;") + '">' +
                            '<div class="font-medium text-sm">' + name + '</div>' +
                            (details ? '<div class="text-xs text-secondary-foreground">' + details + '</div>' : '') +
                            '</div>';
                    }).join('');
                    dropdown.classList.remove('hidden');
                });

                dropdown.addEventListener('click', function (e) {
                    const option = e.target.closest('[data-id]');
                    if (option) {
                        // Use global quartersCache - always has latest data
                        const foundOption = quartersCache.find(o => o.id == option.dataset.id);
                        if (foundOption) {
                            selectedOption = foundOption;
                            hiddenInput.value = selectedOption.id;
                            searchInput.value = selectedOption.display_name || '';
                            if (selectedName) selectedName.textContent = selectedOption.display_name || '';
                            if (selectedDetails) selectedDetails.textContent = selectedOption.quarter_type || '';
                            if (selectedDiv) selectedDiv.classList.remove('hidden');
                            dropdown.classList.add('hidden');
                        }
                    }
                });

                // Clear selection
                document.getElementById('clear-approve-quarter')?.addEventListener('click', function () {
                    selectedOption = null;
                    hiddenInput.value = '';
                    searchInput.value = '';
                    if (selectedDiv) selectedDiv.classList.add('hidden');
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });

                quarterSelectInitialized = true;
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
                        quartersCache = (data.data || []).map(q => ({
                            id: q.id,
                            quarter_number: q.quarter_number || 'N/A',
                            quarter_type: q.quarter_type || 'N/A',
                            display_name: `${q.quarter_number || 'N/A'} (${q.quarter_type || 'N/A'})`
                        }));

                        // Setup the select with new data (only once)
                        setupApproveQuarterSelect();
                    } else {
                        console.error('Failed to load quarters:', data.message);
                        quartersCache = [];
                    }
                } catch (error) {
                    console.error('Error loading quarters:', error);
                    quartersCache = [];
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
                        await loadRequests(currentPage);
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
                        await loadRequests(currentPage);
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
                console.error('Error:', message);

                // Show error notification at top of page
                const notification = document.createElement('div');
                notification.className = 'kt-card bg-danger/10 border border-danger/20 mb-4';
                notification.innerHTML = `
                    <div class="kt-card-content p-4">
                        <div class="flex items-center gap-3">
                            <i class="ki-filled ki-information text-danger text-xl"></i>
                            <p class="text-sm text-danger font-medium">${message}</p>
                        </div>
                    </div>
                `;

                const content = document.querySelector('.grid.gap-5');
                if (content) {
                    content.insertBefore(notification, content.firstChild);
                    setTimeout(() => notification.remove(), 5000);
                } else {
                    alert(message);
                }
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

            // Initialize status select on page load
            document.addEventListener('DOMContentLoaded', function() {
                const statusOptions = [
                    {id: '', name: 'All Requests'},
                    {id: 'PENDING', name: 'Pending'},
                    {id: 'APPROVED', name: 'Approved'},
                    {id: 'REJECTED', name: 'Rejected'}
                ];

                createSearchableSelect({
                    triggerId: 'status_select_trigger',
                    hiddenInputId: 'filter-status',
                    dropdownId: 'status_dropdown',
                    searchInputId: 'status_search_input',
                    optionsContainerId: 'status_options',
                    displayTextId: 'status_select_text',
                    options: statusOptions,
                    placeholder: 'All Requests',
                    searchPlaceholder: 'Search status...',
                    onSelect: function() {
                        loadRequests(1);
                    }
                });
            });
        </script>
    @endpush
@endsection
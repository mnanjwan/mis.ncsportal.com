@extends('layouts.app')

@section('title', 'Manage Officers Quartered Status')
@section('page-title', 'Manage Officers Quartered Status')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filter Officers</h3>
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
                               placeholder="Search by service number, name...">
                    </div>

                    <!-- Quartered Status Select -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Quartered Status</label>
                        <select id="filter-quartered" class="kt-input w-full">
                            <option value="">All Status</option>
                            <option value="1">Quartered</option>
                            <option value="0">Not Quartered</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" onclick="loadOfficers()" class="kt-btn kt-btn-primary w-full md:w-auto">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="kt-card" id="bulk-actions" style="display: none;">
        <div class="kt-card-content">
            <div class="flex items-center justify-between">
                <span class="text-sm text-secondary-foreground">
                    <span id="selected-count">0</span> officer(s) selected
                </span>
                <div class="flex gap-2">
                    <button onclick="showBulkUpdateModal(true)" class="kt-btn kt-btn-sm kt-btn-success">
                        <i class="ki-filled ki-check"></i> Set as Quartered
                    </button>
                    <button onclick="showBulkUpdateModal(false)" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-cross"></i> Set as Not Quartered
                    </button>
                    <button onclick="clearSelection()" class="kt-btn kt-btn-sm kt-btn-secondary">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Officers List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers in Command</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Table with horizontal scroll wrapper -->
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 900px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll()" />
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('service_number')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Service Number
                                    <span id="sort-icon-service_number" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('surname')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Name
                                    <span id="sort-icon-surname" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('substantive_rank')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Rank
                                    <span id="sort-icon-substantive_rank" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('quartered')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Quartered Status
                                    <span id="sort-icon-quartered" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="officers-list">
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                <p class="text-secondary-foreground">Loading officers...</p>
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
let currentPage = 1;
let selectedOfficers = new Set();
let allOfficers = [];
let currentSort = 'service_number';
let currentOrder = 'asc';

document.addEventListener('DOMContentLoaded', async () => {
    // Initialize sort icon
    const defaultSortIcon = document.getElementById('sort-icon-service_number');
    if (defaultSortIcon) {
        defaultSortIcon.innerHTML = '<i class="ki-filled ki-arrow-up text-xs"></i>';
        defaultSortIcon.classList.remove('opacity-50');
        defaultSortIcon.classList.add('text-primary');
    }
    
    loadOfficers();
    
    // Search on Enter key
    document.getElementById('search-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            loadOfficers(1);
        }
    });
    
    // Initialize modal functionality
    await loadModalQuarters();
    await loadModalOfficers();
    setupModalQuarterSelect();
    
    // Setup form submit
    const allocateForm = document.getElementById('allocate-quarter-form');
    if (allocateForm) {
        allocateForm.addEventListener('submit', handleModalSubmit);
    }
});

async function loadOfficers(page = 1) {
    try {
        const token = window.API_CONFIG.token;
        const search = document.getElementById('search-input').value;
        const quartered = document.getElementById('filter-quartered').value;
        
        let url = `/api/v1/officers?per_page=20&page=${page}&sort=${currentSort}&order=${currentOrder}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (quartered !== '') url += `&quartered=${quartered}`;
        
        const res = await fetch(url, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            allOfficers = data.data || [];
            currentPage = page;
            
            renderOfficers(allOfficers);
            renderPagination(data.meta);
        } else {
            const errorMsg = data.message || 'Failed to load officers';
            console.error('API Error:', errorMsg);
            
            if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                showError('You must be assigned to a command to view officers. Please contact HRD.');
                renderOfficers([]);
            } else {
                showError(errorMsg);
                renderOfficers([]);
            }
        }
    } catch (error) {
        console.error('Error loading officers:', error);
        showError('Error loading officers');
    }
}

function renderOfficers(officers) {
    const tbody = document.getElementById('officers-list');
    
    if (officers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-12 text-center">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = officers.map(officer => `
        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
            <td class="py-3 px-4" style="white-space: nowrap;">
                <input type="checkbox" class="officer-checkbox" 
                    value="${officer.id}" 
                    onchange="updateSelection(${officer.id}, this.checked)" />
            </td>
            <td class="py-3 px-4" style="white-space: nowrap;">
                <span class="text-sm font-mono text-foreground">${officer.service_number || 'N/A'}</span>
            </td>
            <td class="py-3 px-4" style="white-space: nowrap;">
                <span class="text-sm font-medium text-foreground">${(officer.initials || '') + ' ' + (officer.surname || '')}</span>
            </td>
            <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                ${officer.substantive_rank || 'N/A'}
            </td>
            <td class="py-3 px-4" style="white-space: nowrap;">
                ${officer.has_pending_allocation ? `
                    <span class="kt-badge kt-badge-warning kt-badge-sm">
                        <i class="ki-filled ki-time"></i> Pending${officer.pending_quarter_display ? ' (' + officer.pending_quarter_display + ')' : ''}
                    </span>
                ` : officer.quartered ? `
                    <span class="kt-badge kt-badge-success kt-badge-sm">
                        Yes${officer.current_quarter_display ? ' (' + officer.current_quarter_display + ')' : ''}
                    </span>
                ` : `
                    <span class="kt-badge kt-badge-secondary kt-badge-sm">
                        No
                    </span>
                `}
            </td>
            <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                <div class="flex items-center justify-end gap-2">
                    ${officer.has_pending_allocation ? `
                        <span class="kt-badge kt-badge-warning kt-badge-sm" title="Officer has a pending allocation waiting for acceptance">
                            <i class="ki-filled ki-time"></i> Pending
                        </span>
                    ` : officer.quartered ? `
                        <button onclick="openRemoveQuarterModal(${officer.id}, '${((officer.initials || '') + ' ' + (officer.surname || '')).replace(/'/g, "&#39;")}', '${(officer.service_number || 'N/A').replace(/'/g, "&#39;")}')" 
                                class="kt-btn kt-btn-sm kt-btn-danger" 
                                title="Remove Quarter">
                            <i class="ki-filled ki-cross"></i> Remove
                        </button>
                    ` : `
                        <button onclick="openAllocateQuarterModal(${officer.id}, '${((officer.initials || '') + ' ' + (officer.surname || '')).replace(/'/g, "&#39;")}', '${(officer.service_number || 'N/A').replace(/'/g, "&#39;")}')" 
                                class="kt-btn kt-btn-sm kt-btn-primary" 
                                title="Allocate Quarter">
                            <i class="ki-filled ki-check"></i> Quarter
                        </button>
                    `}
                </div>
            </td>
        </tr>
    `).join('');
}

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
    loadOfficers(1);
}

function renderPagination(meta) {
    const pagination = document.getElementById('pagination');
    
    if (!meta || meta.last_page <= 1) {
        pagination.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-secondary-foreground">
                    Showing ${meta?.from || 0} to ${meta?.to || 0} of ${meta?.total || 0} officers
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
                Showing <span class="font-medium">${from}</span> to <span class="font-medium">${to}</span> of <span class="font-medium">${total}</span> officers
        </div>
            <div class="flex items-center gap-1 flex-wrap justify-center">
    `;
    
    // First & Previous buttons
    if (current > 1) {
        html += `
            <button onclick="loadOfficers(1)" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
                <i class="ki-filled ki-double-left"></i>
            </button>
            <button onclick="loadOfficers(${current - 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
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
        html += `<button onclick="loadOfficers(1)" class="kt-btn kt-btn-sm kt-btn-secondary">1</button>`;
        if (startPage > 2) {
            html += `<span class="px-2 text-secondary-foreground">...</span>`;
        }
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<button class="kt-btn kt-btn-sm kt-btn-primary" disabled>${i}</button>`;
        } else {
            html += `<button onclick="loadOfficers(${i})" class="kt-btn kt-btn-sm kt-btn-secondary">${i}</button>`;
        }
    }
    
    // Show last page if not in range
    if (endPage < last) {
        if (endPage < last - 1) {
            html += `<span class="px-2 text-secondary-foreground">...</span>`;
        }
        html += `<button onclick="loadOfficers(${last})" class="kt-btn kt-btn-sm kt-btn-secondary">${last}</button>`;
    }
    
    // Next & Last buttons
    if (current < last) {
        html += `
            <button onclick="loadOfficers(${current + 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
                Next <i class="ki-filled ki-right"></i>
            </button>
            <button onclick="loadOfficers(${last})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
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


function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.officer-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        updateSelection(parseInt(checkbox.value), selectAll.checked);
    });
}

function updateSelection(officerId, checked) {
    if (checked) {
        selectedOfficers.add(officerId);
    } else {
        selectedOfficers.delete(officerId);
    }
    
    document.getElementById('select-all').checked = selectedOfficers.size === allOfficers.length;
    updateBulkActions();
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (selectedOfficers.size > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = selectedOfficers.size;
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    selectedOfficers.clear();
    document.querySelectorAll('.officer-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all').checked = false;
    updateBulkActions();
}

function showBulkUpdateModal(quartered) {
    if (selectedOfficers.size === 0) {
        showError('Please select at least one officer');
        return;
    }
    
    const status = quartered ? 'quartered' : 'not quartered';
    document.getElementById('bulk-update-status').textContent = status;
    document.getElementById('bulk-update-count').textContent = selectedOfficers.size;
    document.getElementById('bulk-update-form').dataset.quartered = quartered;
    
    const modal = document.getElementById('bulk-update-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    } else {
        modal.style.display = 'flex';
    }
}

async function bulkUpdateQuartered(quartered) {
    
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/officers/bulk-update-quartered-status', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                officer_ids: Array.from(selectedOfficers),
                quartered: quartered
            })
        });
        
        if (res.ok) {
            const data = await res.json();
            if (data.success) {
                showSuccess(data.message || 'Quartered status updated successfully');
                clearSelection();
                loadOfficers(currentPage);
            }
        } else {
            const error = await res.json();
            showError(error.message || 'Failed to update quartered status');
        }
    } catch (error) {
        console.error('Error bulk updating quartered status:', error);
        showError('Error updating quartered status');
    }
}

function showSuccess(message) {
    // Create success notification card
    const notification = document.createElement('div');
    notification.className = 'kt-card bg-success/10 border border-success/20 mb-4';
    notification.innerHTML = `
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success font-medium">${message}</p>
            </div>
        </div>
    `;
    
    // Insert at top of content
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    } else {
        alert(message);
    }
}

function showError(message) {
    // Create error notification card
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
    
    // Insert at top of content
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    } else {
        alert(message);
    }
}
</script>
@endpush

<!-- Bulk Update Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="bulk-update-modal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                    <i class="ki-filled ki-information text-info text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Confirm Bulk Update</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to set <strong id="bulk-update-count">0</strong> officer(s) as <strong id="bulk-update-status">quartered</strong>?
            </p>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <button type="button" onclick="processBulkUpdate()" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-check"></i> Confirm
            </button>
        </div>
    </div>
</div>

<script>
function processBulkUpdate() {
    const form = document.getElementById('bulk-update-form');
    const quartered = form.dataset.quartered === 'true';
    
    // Close modal
    const modal = document.getElementById('bulk-update-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    } else {
        modal.style.display = 'none';
    }
    
    // Process the update
    bulkUpdateQuartered(quartered);
}
</script>

<!-- Hidden form to store bulk update data -->
<form id="bulk-update-form" style="display: none;" data-quartered=""></form>

<!-- Allocate Quarter Modal -->
<div id="allocate-quarter-modal" class="kt-modal" data-kt-modal="true">
    <div class="kt-modal-content max-w-[600px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-check text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Allocate Quarter to Officer</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeAllocateQuarterModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="allocate-quarter-form">
            <div class="kt-modal-body py-5 px-5">
                <div class="flex flex-col gap-5">
                    <!-- Officer Selection (Pre-selected) -->
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Officer</label>
                        <div id="modal-selected-officer" class="p-3 bg-muted/50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium" id="modal-officer-name"></span>
                                    <span class="text-xs text-secondary-foreground" id="modal-officer-service-number"></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="modal-officer-id" name="officer_id" />
                    </div>

                    <!-- Quarter Selection -->
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Select Quarter <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" id="modal-quarter-search" 
                                placeholder="Search quarters by number or type..."
                                class="kt-input w-full" 
                                autocomplete="off" />
                            <div id="modal-quarter-results" class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                        </div>
                        <input type="hidden" id="modal-quarter-id" name="quarter_id" />
                        <div id="modal-selected-quarter" class="hidden mt-2 p-2 bg-muted/50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium" id="modal-quarter-display"></span>
                                    <span class="text-xs text-secondary-foreground" id="modal-quarter-details"></span>
                                </div>
                                <button type="button" onclick="clearModalQuarter()" class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="toggleModalQuarterList()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-list"></i> Show All Available Quarters
                            </button>
                        </div>
                        <div id="modal-all-quarters-list" class="hidden mt-2 max-h-60 overflow-y-auto border border-input rounded-lg p-2"></div>
                    </div>

                    <!-- Allocation Date -->
                    <div class="flex flex-col gap-2">
                        <label class="kt-form-label">Allocation Date</label>
                        <input type="date" id="modal-allocation-date" name="allocation_date" 
                            class="kt-input" 
                            value="{{ date('Y-m-d') }}" 
                            required />
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" onclick="closeAllocateQuarterModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i> Allocate Quarter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Remove Quarter Modal -->
<div id="remove-quarter-modal" class="kt-modal" data-kt-modal="true">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                    <i class="ki-filled ki-cross text-danger text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Remove Quarter</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeRemoveQuarterModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <div class="kt-alert kt-alert-warning mb-4">
                <i class="ki-filled ki-information"></i>
                <div>
                    <strong>Confirm Removal:</strong> Are you sure you want to remove the quarter from this officer?
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <div class="p-3 bg-muted/50 rounded-lg">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium" id="remove-officer-name"></span>
                        <span class="text-xs text-secondary-foreground" id="remove-officer-service-number"></span>
                    </div>
                </div>
            </div>
            <input type="hidden" id="remove-officer-id" />
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" onclick="closeRemoveQuarterModal()">Cancel</button>
            <button type="button" onclick="confirmRemoveQuarter()" class="kt-btn kt-btn-danger">
                <i class="ki-filled ki-cross"></i> Remove Quarter
            </button>
        </div>
    </div>
</div>

<script>
// Allocate Quarter Modal Variables
let modalOfficersCache = [];
let modalQuartersCache = [];
let modalAllQuartersListVisible = false;


async function loadModalQuarters() {
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
            modalQuartersCache = (data.data || []).map(q => ({
                id: q.id,
                quarter_number: q.quarter_number || 'N/A',
                quarter_type: q.quarter_type || 'N/A',
                display_name: `${q.quarter_number || 'N/A'} (${q.quarter_type || 'N/A'})`
            }));
            renderModalAllQuartersList();
        } else {
            modalQuartersCache = [];
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        modalQuartersCache = [];
    }
}

async function loadModalOfficers() {
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            console.error('API token not found');
            return;
        }

        const res = await fetch('/api/v1/officers?per_page=1000', {
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            modalOfficersCache = (data.data || []).map(o => ({
                id: o.id,
                service_number: o.service_number || 'N/A',
                initials: o.initials || '',
                surname: o.surname || '',
                substantive_rank: o.substantive_rank || 'N/A',
                display_name: `${o.initials || ''} ${o.surname || ''}`.trim() || 'N/A',
                display_details: `${o.service_number || 'N/A'} - ${o.substantive_rank || 'N/A'}`
            }));
        } else {
            modalOfficersCache = [];
        }
    } catch (error) {
        console.error('Error loading officers:', error);
        modalOfficersCache = [];
    }
}

let modalQuarterSelectInitialized = false;

function setupModalQuarterSelect() {
    if (modalQuarterSelectInitialized) {
        return; // Already initialized - the closure will use the updated modalQuartersCache
    }
    
    const searchInput = document.getElementById('modal-quarter-search');
    const hiddenInput = document.getElementById('modal-quarter-id');
    const dropdown = document.getElementById('modal-quarter-results');
    const selectedDiv = document.getElementById('modal-selected-quarter');
    const selectedName = document.getElementById('modal-quarter-display');
    const selectedDetails = document.getElementById('modal-quarter-details');

    if (!searchInput || !hiddenInput || !dropdown) {
        return; // Elements not found
    }

    // Use a closure that references the global modalQuartersCache so it always has latest data
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        
        // Use global modalQuartersCache - always has latest data
        const filtered = modalQuartersCache.filter(opt => {
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

    dropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-id]');
        if (option) {
            const optionId = parseInt(option.dataset.id);
            // Use global modalQuartersCache - always has latest data
            const foundOption = modalQuartersCache.find(o => parseInt(o.id) === optionId);
            if (foundOption) {
                // Ensure we get the actual quarter ID element and set it correctly
                const quarterIdInputElement = document.getElementById('modal-quarter-id');
                if (quarterIdInputElement) {
                    quarterIdInputElement.value = foundOption.id.toString();
                    console.log('Quarter selected - Setting quarter ID:', foundOption.id, 'Input element value:', quarterIdInputElement.value);
                }
                
                searchInput.value = foundOption.display_name || '';
                if (selectedName) selectedName.textContent = foundOption.display_name || '';
                if (selectedDetails) selectedDetails.textContent = foundOption.quarter_type || '';
                if (selectedDiv) selectedDiv.classList.remove('hidden');
                dropdown.classList.add('hidden');
            } else {
                console.error('Quarter option not found in cache for ID:', optionId);
            }
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    
    modalQuarterSelectInitialized = true;
}

function renderModalAllQuartersList() {
    const listDiv = document.getElementById('modal-all-quarters-list');
    
    if (modalQuartersCache.length === 0) {
        listDiv.innerHTML = '<div class="p-3 text-secondary-foreground text-center">No available quarters</div>';
        return;
    }
    
    listDiv.innerHTML = modalQuartersCache.map(q => `
        <div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-b-0 rounded mb-1" 
            onclick="selectModalQuarter(${q.id}, '${q.quarter_number || 'N/A'}', '${q.quarter_type || 'N/A'}')">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">${q.quarter_number || 'N/A'}</div>
                    <div class="text-sm text-secondary-foreground">${q.quarter_type || 'N/A'}</div>
                </div>
                <span class="kt-badge kt-badge-success kt-badge-sm">Available</span>
            </div>
        </div>
    `).join('');
}

function toggleModalQuarterList() {
    const listDiv = document.getElementById('modal-all-quarters-list');
    modalAllQuartersListVisible = !modalAllQuartersListVisible;
    
    if (modalAllQuartersListVisible) {
        listDiv.classList.remove('hidden');
        renderModalAllQuartersList();
    } else {
        listDiv.classList.add('hidden');
    }
}

function selectModalQuarter(id, number, type) {
    const quarterIdInput = document.getElementById('modal-quarter-id');
    const quarterDisplay = document.getElementById('modal-quarter-display');
    const quarterDetails = document.getElementById('modal-quarter-details');
    const selectedQuarterDiv = document.getElementById('modal-selected-quarter');
    const quarterSearch = document.getElementById('modal-quarter-search');
    
    quarterIdInput.value = id.toString();
    quarterDisplay.textContent = number;
    quarterDetails.textContent = type;
    selectedQuarterDiv.classList.remove('hidden');
    quarterSearch.value = number;
    document.getElementById('modal-quarter-results').classList.add('hidden');
    document.getElementById('modal-all-quarters-list').classList.add('hidden');
    modalAllQuartersListVisible = false;
    
    // Debug log
    console.log('Quarter selected - ID:', id, 'Hidden input value:', quarterIdInput.value);
}

function clearModalQuarter() {
    document.getElementById('modal-quarter-id').value = '';
    document.getElementById('modal-selected-quarter').classList.add('hidden');
    document.getElementById('modal-quarter-search').value = '';
}

async function openAllocateQuarterModal(officerId, officerName, serviceNumber) {
    // Set officer details (pre-selected)
    document.getElementById('modal-officer-id').value = officerId;
    document.getElementById('modal-officer-name').textContent = officerName;
    document.getElementById('modal-officer-service-number').textContent = serviceNumber;
    
    // Clear quarter selection
    document.getElementById('modal-quarter-id').value = '';
    document.getElementById('modal-quarter-search').value = '';
    document.getElementById('modal-selected-quarter').classList.add('hidden');
    document.getElementById('modal-all-quarters-list').classList.add('hidden');
    modalAllQuartersListVisible = false;
    
    // Set allocation date to today (using JavaScript)
    const today = new Date();
    const dateString = today.toISOString().split('T')[0];
    document.getElementById('modal-allocation-date').value = dateString;
    
    // Reload quarters to ensure we have latest data
    await loadModalQuarters();
    
    // Show modal (searchable select is already set up and will use updated modalQuartersCache)
    const modal = document.getElementById('allocate-quarter-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    } else {
        modal.style.display = 'flex';
    }
}

function closeAllocateQuarterModal() {
    const modal = document.getElementById('allocate-quarter-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    } else {
        modal.style.display = 'none';
    }
    
    // Clear form fields manually (don't use reset() as it might interfere with hidden inputs)
    document.getElementById('modal-officer-id').value = '';
    document.getElementById('modal-quarter-id').value = '';
    document.getElementById('modal-quarter-search').value = '';
    document.getElementById('modal-officer-name').textContent = '';
    document.getElementById('modal-officer-service-number').textContent = '';
    document.getElementById('modal-selected-quarter').classList.add('hidden');
    document.getElementById('modal-all-quarters-list').classList.add('hidden');
    modalAllQuartersListVisible = false;
}

async function handleModalSubmit(e) {
    e.preventDefault();
    
    const officerIdInput = document.getElementById('modal-officer-id');
    const quarterIdInput = document.getElementById('modal-quarter-id');
    const allocationDateInput = document.getElementById('modal-allocation-date');
    
    const officerId = officerIdInput?.value?.trim();
    const quarterId = quarterIdInput?.value?.trim();
    const allocationDate = allocationDateInput?.value?.trim();
    
    // Debug logging
    console.log('Modal Submit - Officer ID:', officerId, 'Input:', officerIdInput);
    console.log('Modal Submit - Quarter ID:', quarterId, 'Input:', quarterIdInput);
    console.log('Modal Submit - Allocation Date:', allocationDate);
    
    // Check if quarter is selected (visible in selected div)
    const selectedQuarterDiv = document.getElementById('modal-selected-quarter');
    const isQuarterSelected = selectedQuarterDiv && !selectedQuarterDiv.classList.contains('hidden');
    
    console.log('Quarter selected div visible:', isQuarterSelected);
    
    if (!officerId) {
        showError('Officer is required');
        return;
    }
    
    if (!quarterId || !isQuarterSelected) {
        showError('Please select a quarter');
        // Highlight the quarter field
        const quarterSearch = document.getElementById('modal-quarter-search');
        if (quarterSearch) {
            quarterSearch.focus();
            quarterSearch.classList.add('border-danger');
            setTimeout(() => quarterSearch.classList.remove('border-danger'), 3000);
        }
        return;
    }
    
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            showError('Authentication required. Please refresh the page.');
            return;
        }

        const payload = {
            officer_id: parseInt(officerId),
            quarter_id: parseInt(quarterId),
            allocation_date: allocationDate || null
        };
        
        console.log('Sending allocation request:', payload);

        const res = await fetch('/api/v1/quarters/allocate', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        console.log('Allocation response:', data);
        
        if (res.ok && data.success) {
            showSuccess('Quarter allocated successfully!');
            closeAllocateQuarterModal();
            await loadOfficers(currentPage);
        } else {
            const errorMsg = data.message || 'Failed to allocate quarter';
            console.error('API Error:', errorMsg, data);
            showError(errorMsg);
        }
    } catch (error) {
        console.error('Error allocating quarter:', error);
        showError('Error allocating quarter. Please try again.');
    }
}

function openRemoveQuarterModal(officerId, officerName, serviceNumber) {
    document.getElementById('remove-officer-id').value = officerId;
    document.getElementById('remove-officer-name').textContent = officerName;
    document.getElementById('remove-officer-service-number').textContent = serviceNumber;
    
    const modal = document.getElementById('remove-quarter-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    } else {
        modal.style.display = 'flex';
    }
}

function closeRemoveQuarterModal() {
    const modal = document.getElementById('remove-quarter-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    } else {
        modal.style.display = 'none';
    }
}

async function confirmRemoveQuarter() {
    const officerId = document.getElementById('remove-officer-id').value;
    
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            showError('Authentication required. Please refresh the page.');
            return;
        }

        // Get the officer's current quarter allocation
        const quartersRes = await fetch(`/api/v1/officers/${officerId}/quarters`, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });
        
        if (!quartersRes.ok) {
            showError('Failed to fetch quarter allocations');
            return;
        }
        
        const quartersData = await quartersRes.json();
        const currentAllocation = quartersData.data?.find(a => a.is_current && a.status === 'ACCEPTED');
        
        if (!currentAllocation) {
            showError('No active quarter allocation found for this officer');
            closeRemoveQuarterModal();
            await loadOfficers(currentPage);
            return;
        }
        
        // Deallocate the quarter using the deallocate endpoint
        const res = await fetch(`/api/v1/quarters/${currentAllocation.quarter_id}/deallocate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                officer_id: parseInt(officerId)
            })
        });
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            showSuccess('Quarter removed successfully!');
            closeRemoveQuarterModal();
            await loadOfficers(currentPage);
        } else {
            const errorMsg = data.message || 'Failed to remove quarter';
            showError(errorMsg);
        }
    } catch (error) {
        console.error('Error removing quarter:', error);
        showError('Error removing quarter. Please try again.');
    }
}
</script>
@endsection


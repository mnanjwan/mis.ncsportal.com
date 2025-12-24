@extends('layouts.app')

@section('title', 'Manage Officers Quartered Status')
@section('page-title', 'Manage Officers Quartered Status')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="search-input" placeholder="Search by service number, name..." 
                        class="kt-input w-full" />
                </div>
                <div class="flex gap-2">
                    <select id="filter-quartered" class="kt-select">
                        <option value="">All Status</option>
                        <option value="1">Quartered</option>
                        <option value="0">Not Quartered</option>
                    </select>
                    <button onclick="loadOfficers()" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-magnifier"></i> Search
                    </button>
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
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers in Command</h3>
        </div>
        <div class="kt-card-content">
            <div class="overflow-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll()" />
                            </th>
                            <th>Service Number</th>
                            <th>Name</th>
                            <th>Rank</th>
                            <th>Quartered Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="officers-list">
                        <tr>
                            <td colspan="6" class="text-center py-8 text-secondary-foreground">
                                Loading officers...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="mt-4 flex items-center justify-between"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;
let selectedOfficers = new Set();
let allOfficers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadOfficers();
    
    // Search on Enter key
    document.getElementById('search-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            loadOfficers();
        }
    });
});

async function loadOfficers(page = 1) {
    try {
        const token = window.API_CONFIG.token;
        const search = document.getElementById('search-input').value;
        const quartered = document.getElementById('filter-quartered').value;
        
        let url = `/api/v1/officers?per_page=20&page=${page}`;
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
                <td colspan="6" class="text-center py-8 text-secondary-foreground">
                    No officers found
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = officers.map(officer => `
        <tr>
            <td>
                <input type="checkbox" class="officer-checkbox" 
                    value="${officer.id}" 
                    onchange="updateSelection(${officer.id}, this.checked)" />
            </td>
            <td class="font-mono">${officer.service_number || 'N/A'}</td>
            <td>${(officer.initials || '') + ' ' + (officer.surname || '')}</td>
            <td>${officer.substantive_rank || 'N/A'}</td>
            <td>
                <span class="kt-badge kt-badge-${officer.quartered ? 'success' : 'secondary'} kt-badge-sm">
                    ${officer.quartered ? 'Yes' : 'No'}
                </span>
            </td>
            <td>
                <select class="kt-select kt-select-sm" 
                    onchange="updateQuarteredStatus(${officer.id}, this.value)"
                    value="${officer.quartered ? '1' : '0'}">
                    <option value="0" ${!officer.quartered ? 'selected' : ''}>No</option>
                    <option value="1" ${officer.quartered ? 'selected' : ''}>Yes</option>
                </select>
            </td>
        </tr>
    `).join('');
}

function renderPagination(meta) {
    const pagination = document.getElementById('pagination');
    
    if (!meta || meta.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = `
        <div class="text-sm text-secondary-foreground">
            Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total || 0} officers
        </div>
        <div class="flex gap-2">
    `;
    
    if (meta.prev) {
        html += `<button onclick="loadOfficers(${currentPage - 1})" class="kt-btn kt-btn-sm">Previous</button>`;
    }
    
    if (meta.next) {
        html += `<button onclick="loadOfficers(${currentPage + 1})" class="kt-btn kt-btn-sm">Next</button>`;
    }
    
    html += '</div>';
    pagination.innerHTML = html;
}

async function updateQuarteredStatus(officerId, value) {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch(`/api/v1/officers/${officerId}/quartered-status`, {
            method: 'PATCH',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quartered: value === '1' })
        });
        
        if (res.ok) {
            const data = await res.json();
            if (data.success) {
                showSuccess('Quartered status updated successfully');
                loadOfficers(currentPage);
            }
        } else {
            const error = await res.json();
            showError(error.message || 'Failed to update quartered status');
            loadOfficers(currentPage);
        }
    } catch (error) {
        console.error('Error updating quartered status:', error);
        showError('Error updating quartered status');
        loadOfficers(currentPage);
    }
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
@endsection


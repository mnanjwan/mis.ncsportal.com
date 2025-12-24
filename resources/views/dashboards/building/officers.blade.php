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
                    <button onclick="bulkUpdateQuartered(true)" class="kt-btn kt-btn-sm kt-btn-success">
                        <i class="ki-filled ki-check"></i> Set as Quartered
                    </button>
                    <button onclick="bulkUpdateQuartered(false)" class="kt-btn kt-btn-sm kt-btn-secondary">
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
        
        if (res.ok) {
            const data = await res.json();
            allOfficers = data.data || [];
            currentPage = page;
            
            renderOfficers(allOfficers);
            renderPagination(data.meta);
        } else {
            showError('Failed to load officers');
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

async function bulkUpdateQuartered(quartered) {
    if (selectedOfficers.size === 0) {
        showError('Please select at least one officer');
        return;
    }
    
    const status = quartered ? 'quartered' : 'not quartered';
    if (!confirm(`Are you sure you want to set ${selectedOfficers.size} officer(s) as ${status}?`)) {
        return;
    }
    
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
    // You can use SweetAlert or any notification library here
    alert(message);
}

function showError(message) {
    // You can use SweetAlert or any notification library here
    alert(message);
}
</script>
@endpush
@endsection


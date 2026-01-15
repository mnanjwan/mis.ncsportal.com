@extends('layouts.app')

@section('title', 'Quarters Management')
@section('page-title', 'Quarters Management')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Actions Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quick Actions</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('building.quarters.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Create New Quarter
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filter Quarters</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- Status Select -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                        <div class="relative">
                            <input type="hidden" id="filter-status" value="">
                            <button type="button" 
                                    id="status_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="status_select_text">All Quarters</span>
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
                        <button type="button" onclick="loadQuarters(1)" class="kt-btn kt-btn-primary w-full md:w-auto">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarters List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quarters List</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Table with horizontal scroll wrapper -->
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 900px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('quarter_number')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Quarter Number
                                    <span id="sort-icon-quarter_number" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('quarter_type')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Type
                                    <span id="sort-icon-quarter_type" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('status')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Status
                                    <span id="sort-icon-status" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="javascript:void(0)" onclick="sortTable('occupied_by')" class="flex items-center gap-1 hover:text-primary transition-colors cursor-pointer">
                                    Occupied By
                                    <span id="sort-icon-occupied_by" class="sort-icon opacity-50">
                                        <i class="ki-filled ki-arrow-up-down text-xs"></i>
                                    </span>
                                </a>
                            </th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="quarters-list">
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <i class="ki-filled ki-loader text-4xl text-muted-foreground mb-4 animate-spin"></i>
                                <p class="text-secondary-foreground">Loading quarters...</p>
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
let allocationsMap = {};

document.addEventListener('DOMContentLoaded', async () => {
    await loadQuarters();
});

let currentPage = 1;
let currentSort = 'quarter_number';
let currentOrder = 'asc';

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
    loadQuarters(1);
}

async function loadQuarters(page = 1) {
    try {
        const token = window.API_CONFIG.token;
        const filter = document.getElementById('filter-status').value;
        let url = `/api/v1/quarters?per_page=20&page=${page}&sort=${currentSort}&order=${currentOrder}`;
        if (filter !== '') {
            url += `&is_occupied=${filter}`;
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
            const quarters = data.data || [];
            currentPage = page;
            renderQuarters(quarters);
            renderPagination(data.meta);
        } else {
            const errorMsg = data.message || 'Failed to load quarters';
            console.error('API Error:', errorMsg);
            
            if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                showError('You must be assigned to a command to view quarters. Please contact HRD.');
                renderQuarters([]);
                renderPagination(null);
            } else {
                showError(errorMsg);
                renderQuarters([]);
                renderPagination(null);
            }
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        showError('Error loading quarters');
        renderPagination(null);
    }
}

function renderPagination(meta) {
    const pagination = document.getElementById('pagination');
    
    if (!meta || meta.last_page <= 1) {
        pagination.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-secondary-foreground">
                    Showing ${meta?.from || 0} to ${meta?.to || 0} of ${meta?.total || 0} quarters
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
                Showing <span class="font-medium">${from}</span> to <span class="font-medium">${to}</span> of <span class="font-medium">${total}</span> quarters
            </div>
            <div class="flex items-center gap-1 flex-wrap justify-center">
    `;
    
    // First & Previous buttons
    if (current > 1) {
        html += `
            <button onclick="loadQuarters(1)" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
                <i class="ki-filled ki-double-left"></i>
            </button>
            <button onclick="loadQuarters(${current - 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === 1 ? 'disabled' : ''}>
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
        html += `<button onclick="loadQuarters(1)" class="kt-btn kt-btn-sm kt-btn-secondary">1</button>`;
        if (startPage > 2) {
            html += `<span class="px-2 text-secondary-foreground">...</span>`;
        }
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<button class="kt-btn kt-btn-sm kt-btn-primary" disabled>${i}</button>`;
        } else {
            html += `<button onclick="loadQuarters(${i})" class="kt-btn kt-btn-sm kt-btn-secondary">${i}</button>`;
        }
    }
    
    // Show last page if not in range
    if (endPage < last) {
        if (endPage < last - 1) {
            html += `<span class="px-2 text-secondary-foreground">...</span>`;
        }
        html += `<button onclick="loadQuarters(${last})" class="kt-btn kt-btn-sm kt-btn-secondary">${last}</button>`;
    }
    
    // Next & Last buttons
    if (current < last) {
        html += `
            <button onclick="loadQuarters(${current + 1})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
                Next <i class="ki-filled ki-right"></i>
            </button>
            <button onclick="loadQuarters(${last})" class="kt-btn kt-btn-sm kt-btn-secondary" ${current === last ? 'disabled' : ''}>
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

async function loadAllocations() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const quarters = data.data || [];
            quarters.forEach(q => {
                if (q.officer) {
                    allocationsMap[q.id] = q.officer;
                }
            });
        }
    } catch (error) {
        console.error('Error loading allocations:', error);
    }
}

function renderQuarters(quarters) {
    const tbody = document.getElementById('quarters-list');
    
    if (quarters.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="py-12 text-center">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No quarters found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = quarters.map(quarter => {
        const officer = quarter.officer;
        return `
            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                <td class="py-3 px-4" style="white-space: nowrap;">
                    <span class="text-sm font-mono font-semibold text-foreground">${quarter.quarter_number || 'N/A'}</span>
                </td>
                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                    ${quarter.quarter_type || 'N/A'}
                </td>
                <td class="py-3 px-4" style="white-space: nowrap;">
                    <span class="kt-badge kt-badge-${quarter.is_occupied ? 'success' : 'secondary'} kt-badge-sm">
                        ${quarter.is_occupied ? 'Occupied' : 'Available'}
                    </span>
                </td>
                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                    ${officer ? `
                        <div class="flex flex-col">
                            <span class="font-semibold text-foreground">${(officer.initials || '') + ' ' + (officer.surname || '')}</span>
                            <span class="text-xs text-secondary-foreground">${officer.service_number || 'N/A'}</span>
                        </div>
                    ` : '<span class="text-secondary-foreground">-</span>'}
                </td>
                <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                    ${quarter.is_occupied && officer ? `
                        <button onclick="showDeallocateModal(${quarter.id}, ${officer.id}, '${(officer.initials || '') + ' ' + (officer.surname || '')}', '${quarter.quarter_number || 'N/A'}')" 
                            class="kt-btn kt-btn-sm kt-btn-danger">
                            <i class="ki-filled ki-cross"></i> Deallocate
                        </button>
                    ` : '<span class="text-secondary-foreground">-</span>'}
                </td>
            </tr>
        `;
    }).join('');
}

function showDeallocateModal(quarterId, officerId, officerName, quarterNumber) {
    document.getElementById('deallocate-officer-name').textContent = officerName || 'Officer';
    document.getElementById('deallocate-quarter-number').textContent = quarterNumber || 'Quarter';
    document.getElementById('deallocate-form').dataset.quarterId = quarterId;
    document.getElementById('deallocate-form').dataset.officerId = officerId;
    
    const modal = document.getElementById('deallocate-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    } else {
        modal.style.display = 'flex';
    }
}

async function deallocateQuarter(quarterId, officerId) {
    
    try {
        const token = window.API_CONFIG.token;
        
        // Find the allocation ID first
        const res = await fetch(`/api/v1/quarters`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const quarter = data.data.find(q => q.id === quarterId);
            
            if (!quarter || !quarter.officer) {
                showError('Allocation not found');
                return;
            }
            
            // We need to get the allocation ID - for now, let's use a workaround
            // In a real scenario, you'd need an endpoint to get allocation by quarter_id
            const deallocateRes = await fetch(`/api/v1/quarters/${quarterId}/deallocate`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ officer_id: officerId })
            });
            
            if (deallocateRes.ok) {
                const result = await deallocateRes.json();
                if (result.success) {
                    showSuccess('Quarter deallocated successfully');
                    loadQuarters(currentPage);
                }
            } else {
                const error = await deallocateRes.json();
                showError(error.message || 'Failed to deallocate quarter');
            }
        }
    } catch (error) {
        console.error('Error deallocating quarter:', error);
        showError('Error deallocating quarter');
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

function processDeallocation() {
    const form = document.getElementById('deallocate-form');
    const quarterId = form.dataset.quarterId;
    const officerId = form.dataset.officerId;
    
    // Close modal
    const modal = document.getElementById('deallocate-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    } else {
        modal.style.display = 'none';
    }
    
    // Process deallocation
    deallocateQuarter(quarterId, officerId);
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
        {id: '', name: 'All Quarters'},
        {id: '0', name: 'Available'},
        {id: '1', name: 'Occupied'}
    ];

    createSearchableSelect({
        triggerId: 'status_select_trigger',
        hiddenInputId: 'filter-status',
        dropdownId: 'status_dropdown',
        searchInputId: 'status_search_input',
        optionsContainerId: 'status_options',
        displayTextId: 'status_select_text',
        options: statusOptions,
        placeholder: 'All Quarters',
        searchPlaceholder: 'Search status...',
        onSelect: function() {
            loadQuarters(1);
        }
    });
});
</script>
@endpush

<!-- Deallocate Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="deallocate-modal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Confirm Deallocation</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to deallocate <strong id="deallocate-quarter-number">Quarter</strong> from <strong id="deallocate-officer-name">Officer</strong>?
            </p>
            <p class="text-xs text-secondary-foreground mt-2">
                This will update the officer's quartered status and make the quarter available for allocation.
            </p>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <button type="button" onclick="processDeallocation()" class="kt-btn kt-btn-danger">
                <i class="ki-filled ki-cross"></i> Deallocate
            </button>
        </div>
    </div>
</div>

<form id="deallocate-form" style="display: none;" data-quarter-id="" data-officer-id=""></form>
@endsection



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
                <a href="{{ route('building.quarters.allocate') }}" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Allocate Quarter
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
                        <select id="filter-status" class="kt-input w-full" onchange="loadQuarters()">
                            <option value="">All Quarters</option>
                            <option value="0">Available</option>
                            <option value="1">Occupied</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" onclick="loadQuarters()" class="kt-btn kt-btn-primary w-full md:w-auto">
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
                                Quarter Number
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Type
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Status
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Occupied By
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

async function loadQuarters() {
    try {
        const token = window.API_CONFIG.token;
        const filter = document.getElementById('filter-status').value;
        let url = '/api/v1/quarters?per_page=100';
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
            renderQuarters(quarters);
        } else {
            const errorMsg = data.message || 'Failed to load quarters';
            console.error('API Error:', errorMsg);
            
            if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                showError('You must be assigned to a command to view quarters. Please contact HRD.');
                renderQuarters([]);
            } else {
                showError(errorMsg);
                renderQuarters([]);
            }
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        showError('Error loading quarters');
    }
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
                    loadQuarters();
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



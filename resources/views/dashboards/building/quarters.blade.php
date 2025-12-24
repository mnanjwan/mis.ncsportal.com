@extends('layouts.app')

@section('title', 'Quarters Management')
@section('page-title', 'Quarters Management')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Actions -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('building.quarters.create') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Create New Quarter
                </a>
                <a href="{{ route('building.quarters.allocate') }}" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Allocate Quarter
                </a>
                <select id="filter-status" class="kt-select" onchange="loadQuarters()">
                    <option value="">All Quarters</option>
                    <option value="0">Available</option>
                    <option value="1">Occupied</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Quarters List -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Quarters List</h3>
        </div>
        <div class="kt-card-content">
            <div class="overflow-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>Quarter Number</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Occupied By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="quarters-list">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-secondary-foreground">
                                Loading quarters...
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
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const quarters = data.data || [];
            renderQuarters(quarters);
        } else {
            showError('Failed to load quarters');
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
                <td colspan="5" class="text-center py-8 text-secondary-foreground">
                    No quarters found
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = quarters.map(quarter => {
        const officer = quarter.officer;
        return `
            <tr>
                <td class="font-mono font-semibold">${quarter.quarter_number || 'N/A'}</td>
                <td>${quarter.quarter_type || 'N/A'}</td>
                <td>
                    <span class="kt-badge kt-badge-${quarter.is_occupied ? 'success' : 'secondary'} kt-badge-sm">
                        ${quarter.is_occupied ? 'Occupied' : 'Available'}
                    </span>
                </td>
                <td>
                    ${officer ? `
                        <div class="flex flex-col">
                            <span class="font-semibold">${(officer.initials || '') + ' ' + (officer.surname || '')}</span>
                            <span class="text-xs text-secondary-foreground">${officer.service_number || 'N/A'}</span>
                        </div>
                    ` : '<span class="text-secondary-foreground">-</span>'}
                </td>
                <td>
                    ${quarter.is_occupied && officer ? `
                        <button onclick="deallocateQuarter(${quarter.id}, ${officer.id})" 
                            class="kt-btn kt-btn-sm kt-btn-danger">
                            <i class="ki-filled ki-cross"></i> Deallocate
                        </button>
                    ` : '<span class="text-secondary-foreground">-</span>'}
                </td>
            </tr>
        `;
    }).join('');
}

async function deallocateQuarter(quarterId, officerId) {
    if (!confirm('Are you sure you want to deallocate this quarter?')) {
        return;
    }
    
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
    alert(message);
}

function showError(message) {
    alert(message);
}
</script>
@endpush
@endsection



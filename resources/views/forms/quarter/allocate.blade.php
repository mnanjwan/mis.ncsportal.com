@extends('layouts.app')

@section('title', 'Allocate Quarter')
@section('page-title', 'Allocate Quarter')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Allocate Quarter to Officer</h3>
        </div>
        <div class="kt-card-content">
            <form id="allocate-form" class="flex flex-col gap-5">
                <!-- Officer Selection -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Select Officer</label>
                    <div class="relative">
                        <input type="text" id="officer-search" 
                            placeholder="Search by service number or name..."
                            class="kt-input w-full" 
                            autocomplete="off" />
                        <div id="officer-results" class="hidden absolute z-10 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="officer-id" name="officer_id" />
                    <div id="selected-officer" class="hidden mt-2 p-3 bg-muted rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-semibold" id="officer-name"></span>
                                <span class="text-sm text-secondary-foreground ml-2" id="officer-service-number"></span>
                            </div>
                            <button type="button" onclick="clearOfficer()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quarter Selection -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Select Quarter</label>
                    <select id="quarter-id" name="quarter_id" class="kt-select" required>
                        <option value="">Loading quarters...</option>
                    </select>
                    <div id="quarter-info" class="hidden mt-2 p-3 bg-muted rounded-lg">
                        <div class="text-sm">
                            <div><strong>Type:</strong> <span id="quarter-type"></span></div>
                            <div><strong>Status:</strong> <span id="quarter-status"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Allocation Date -->
                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Allocation Date</label>
                    <input type="date" id="allocation-date" name="allocation_date" 
                        class="kt-input" 
                        value="{{ date('Y-m-d') }}" 
                        required />
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Allocate Quarter
                    </button>
                    <a href="{{ route('building.quarters') }}" class="kt-btn kt-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let officersCache = [];
let quartersCache = [];

document.addEventListener('DOMContentLoaded', () => {
    loadQuarters();
    setupOfficerSearch();
    
    document.getElementById('quarter-id').addEventListener('change', (e) => {
        updateQuarterInfo(e.target.value);
    });
    
    document.getElementById('allocate-form').addEventListener('submit', handleSubmit);
});

async function loadQuarters() {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters?is_occupied=0', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            quartersCache = data.data || [];
            
            const select = document.getElementById('quarter-id');
            if (quartersCache.length === 0) {
                select.innerHTML = '<option value="">No available quarters</option>';
            } else {
                select.innerHTML = '<option value="">Select a quarter</option>' +
                    quartersCache.map(q => `
                        <option value="${q.id}" data-type="${q.quarter_type || 'N/A'}" 
                            data-occupied="${q.is_occupied}">
                            ${q.quarter_number} - ${q.quarter_type || 'N/A'}
                        </option>
                    `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
    }
}

function setupOfficerSearch() {
    const searchInput = document.getElementById('officer-search');
    const resultsDiv = document.getElementById('officer-results');
    let searchTimeout;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchOfficers(query);
        }, 300);
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.classList.add('hidden');
        }
    });
}

async function searchOfficers(query) {
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch(`/api/v1/officers?search=${encodeURIComponent(query)}&per_page=10`, {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        
        if (res.ok) {
            const data = await res.json();
            const officers = data.data || [];
            renderOfficerResults(officers);
        }
    } catch (error) {
        console.error('Error searching officers:', error);
    }
}

function renderOfficerResults(officers) {
    const resultsDiv = document.getElementById('officer-results');
    
    if (officers.length === 0) {
        resultsDiv.innerHTML = '<div class="p-3 text-secondary-foreground">No officers found</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    resultsDiv.innerHTML = officers.map(officer => `
        <div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-b-0" 
            onclick="selectOfficer(${officer.id}, '${(officer.initials || '') + ' ' + (officer.surname || '')}', '${officer.service_number || 'N/A'}')">
            <div class="font-semibold">${(officer.initials || '') + ' ' + (officer.surname || '')}</div>
            <div class="text-sm text-secondary-foreground">${officer.service_number || 'N/A'} - ${officer.substantive_rank || 'N/A'}</div>
        </div>
    `).join('');
    
    resultsDiv.classList.remove('hidden');
}

function selectOfficer(id, name, serviceNumber) {
    document.getElementById('officer-id').value = id;
    document.getElementById('officer-name').textContent = name;
    document.getElementById('officer-service-number').textContent = serviceNumber;
    document.getElementById('selected-officer').classList.remove('hidden');
    document.getElementById('officer-search').value = '';
    document.getElementById('officer-results').classList.add('hidden');
}

function clearOfficer() {
    document.getElementById('officer-id').value = '';
    document.getElementById('selected-officer').classList.add('hidden');
    document.getElementById('officer-search').value = '';
}

function updateQuarterInfo(quarterId) {
    const quarterInfo = document.getElementById('quarter-info');
    const quarter = quartersCache.find(q => q.id == quarterId);
    
    if (quarter) {
        document.getElementById('quarter-type').textContent = quarter.quarter_type || 'N/A';
        document.getElementById('quarter-status').textContent = quarter.is_occupied ? 'Occupied' : 'Available';
        quarterInfo.classList.remove('hidden');
    } else {
        quarterInfo.classList.add('hidden');
    }
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const officerId = document.getElementById('officer-id').value;
    const quarterId = document.getElementById('quarter-id').value;
    const allocationDate = document.getElementById('allocation-date').value;
    
    if (!officerId) {
        alert('Please select an officer');
        return;
    }
    
    if (!quarterId) {
        alert('Please select a quarter');
        return;
    }
    
    try {
        const token = window.API_CONFIG.token;
        const res = await fetch('/api/v1/quarters/allocate', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                officer_id: parseInt(officerId),
                quarter_id: parseInt(quarterId),
                allocation_date: allocationDate
            })
        });
        
        if (res.ok) {
            const data = await res.json();
            if (data.success) {
                alert('Quarter allocated successfully!');
                window.location.href = '{{ route("building.quarters") }}';
            }
        } else {
            const error = await res.json();
            alert(error.message || 'Failed to allocate quarter');
        }
    } catch (error) {
        console.error('Error allocating quarter:', error);
        alert('Error allocating quarter');
    }
}
</script>
@endpush
@endsection


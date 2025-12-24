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
                    <div class="relative">
                        <input type="text" id="quarter-search" 
                            placeholder="Search quarters by number or type..."
                            class="kt-input w-full" 
                            autocomplete="off" />
                        <div id="quarter-results" class="hidden absolute z-10 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="quarter-id" name="quarter_id" />
                    <div id="selected-quarter" class="hidden mt-2 p-3 bg-muted rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold" id="quarter-display"></div>
                                <div class="text-sm text-secondary-foreground" id="quarter-details"></div>
                            </div>
                            <button type="button" onclick="clearQuarter()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" onclick="toggleQuarterList()" class="kt-btn kt-btn-sm kt-btn-secondary">
                            <i class="ki-filled ki-list"></i> Show All Available Quarters
                        </button>
                    </div>
                    <div id="all-quarters-list" class="hidden mt-2 max-h-60 overflow-y-auto border border-input rounded-lg p-2"></div>
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
let allQuartersListVisible = false;

document.addEventListener('DOMContentLoaded', () => {
    loadQuarters();
    setupOfficerSearch();
    setupQuarterSearch();
    
    document.getElementById('allocate-form').addEventListener('submit', handleSubmit);
    
    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        const quarterSearch = document.getElementById('quarter-search');
        const quarterResults = document.getElementById('quarter-results');
        if (!quarterSearch.contains(e.target) && !quarterResults.contains(e.target)) {
            quarterResults.classList.add('hidden');
        }
    });
});

async function loadQuarters() {
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
            quartersCache = data.data || [];
            renderAllQuartersList();
        } else {
            const errorMsg = data.message || 'Failed to load quarters';
            console.error('API Error:', errorMsg);
            
            if (data.meta?.code === 'NO_COMMAND_ASSIGNED') {
                showError('You must be assigned to a command to view quarters. Please contact HRD.');
            } else {
                showError(errorMsg);
            }
            quartersCache = [];
            renderAllQuartersList();
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        quartersCache = [];
        renderAllQuartersList();
    }
}

function setupQuarterSearch() {
    const searchInput = document.getElementById('quarter-search');
    const resultsDiv = document.getElementById('quarter-results');
    let searchTimeout;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length === 0) {
            resultsDiv.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchQuarters(query);
        }, 300);
    });
}

function searchQuarters(query) {
    const resultsDiv = document.getElementById('quarter-results');
    const queryLower = query.toLowerCase();
    
    const filtered = quartersCache.filter(q => {
        const number = (q.quarter_number || '').toLowerCase();
        const type = (q.quarter_type || '').toLowerCase();
        return number.includes(queryLower) || type.includes(queryLower);
    });
    
    if (filtered.length === 0) {
        resultsDiv.innerHTML = '<div class="p-3 text-secondary-foreground">No quarters found</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    resultsDiv.innerHTML = filtered.map(q => `
        <div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-b-0" 
            onclick="selectQuarter(${q.id}, '${q.quarter_number || 'N/A'}', '${q.quarter_type || 'N/A'}')">
            <div class="font-semibold">${q.quarter_number || 'N/A'}</div>
            <div class="text-sm text-secondary-foreground">${q.quarter_type || 'N/A'}</div>
        </div>
    `).join('');
    
    resultsDiv.classList.remove('hidden');
}

function renderAllQuartersList() {
    const listDiv = document.getElementById('all-quarters-list');
    
    if (quartersCache.length === 0) {
        listDiv.innerHTML = '<div class="p-3 text-secondary-foreground text-center">No available quarters</div>';
        return;
    }
    
    listDiv.innerHTML = quartersCache.map(q => `
        <div class="p-3 hover:bg-muted cursor-pointer border-b border-input last:border-b-0 rounded mb-1" 
            onclick="selectQuarter(${q.id}, '${q.quarter_number || 'N/A'}', '${q.quarter_type || 'N/A'}')">
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

function toggleQuarterList() {
    const listDiv = document.getElementById('all-quarters-list');
    allQuartersListVisible = !allQuartersListVisible;
    
    if (allQuartersListVisible) {
        listDiv.classList.remove('hidden');
        renderAllQuartersList();
    } else {
        listDiv.classList.add('hidden');
    }
}

function selectQuarter(id, number, type) {
    document.getElementById('quarter-id').value = id;
    document.getElementById('quarter-display').textContent = number;
    document.getElementById('quarter-details').textContent = type;
    document.getElementById('selected-quarter').classList.remove('hidden');
    document.getElementById('quarter-search').value = '';
    document.getElementById('quarter-results').classList.add('hidden');
    document.getElementById('all-quarters-list').classList.add('hidden');
    allQuartersListVisible = false;
}

function clearQuarter() {
    document.getElementById('quarter-id').value = '';
    document.getElementById('selected-quarter').classList.add('hidden');
    document.getElementById('quarter-search').value = '';
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


async function handleSubmit(e) {
    e.preventDefault();
    
    const officerId = document.getElementById('officer-id').value;
    const quarterId = document.getElementById('quarter-id').value;
    const allocationDate = document.getElementById('allocation-date').value;
    
    if (!officerId) {
        showError('Please select an officer');
        return;
    }
    
    if (!quarterId) {
        showError('Please select a quarter');
        return;
    }
    
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            showError('Authentication required. Please refresh the page.');
            return;
        }

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
        
        const data = await res.json();
        
        if (res.ok && data.success) {
            // Show success message
            showSuccess('Quarter allocated successfully!');
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = '{{ route("building.quarters") }}';
            }, 1500);
        } else {
            const errorMsg = data.message || 'Failed to allocate quarter';
            console.error('API Error:', errorMsg);
            showError(errorMsg);
        }
    } catch (error) {
        console.error('Error allocating quarter:', error);
        showError('Error allocating quarter. Please try again.');
    }
}

function showSuccess(message) {
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
    
    const content = document.querySelector('.grid.gap-5');
    if (content) {
        content.insertBefore(notification, content.firstChild);
        setTimeout(() => notification.remove(), 5000);
    } else {
        alert(message);
    }
}

function showError(message) {
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
</script>
@endpush
@endsection


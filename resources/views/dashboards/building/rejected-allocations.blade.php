@extends('layouts.app')

@section('title', 'Rejected Allocations')
@section('page-title', 'Rejected Quarter Allocations')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('building.dashboard') }}">Building Unit</a>
    <span>/</span>
    <span class="text-primary">Rejected Allocations</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filter -->
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="flex flex-wrap gap-3 items-center">
                <label class="text-sm text-secondary-foreground">Filter by Date:</label>
                <input type="date" id="filter-from-date" class="kt-input" value="{{ request('from_date') }}" onchange="applyFilters()">
                <span class="text-secondary-foreground">to</span>
                <input type="date" id="filter-to-date" class="kt-input" value="{{ request('to_date') }}" onchange="applyFilters()">
                <button onclick="clearFilters()" class="kt-btn kt-btn-sm kt-btn-ghost">
                    <i class="ki-filled ki-cross"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Rejected Allocations List -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Rejected Quarter Allocations</h3>
        </div>
        <div class="kt-card-content">
            <div class="overflow-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>Rejected Date</th>
                            <th>Officer</th>
                            <th>Service Number</th>
                            <th>Quarter Number</th>
                            <th>Quarter Type</th>
                            <th>Allocated By</th>
                            <th>Rejection Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rejected-allocations-list">
                        @forelse($rejectedAllocations as $allocation)
                            <tr>
                                <td>{{ $allocation->rejected_at ? $allocation->rejected_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }}</td>
                                <td>{{ $allocation->officer->service_number ?? 'N/A' }}</td>
                                <td>{{ $allocation->quarter->quarter_number ?? 'N/A' }}</td>
                                <td>{{ $allocation->quarter->quarter_type ?? 'N/A' }}</td>
                                <td>
                                    @if($allocation->allocatedBy)
                                        @if($allocation->allocatedBy->officer)
                                            {{ ($allocation->allocatedBy->officer->initials ?? '') . ' ' . ($allocation->allocatedBy->officer->surname ?? '') }}
                                        @else
                                            {{ $allocation->allocatedBy->email ?? 'N/A' }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="max-w-xs">
                                    @if($allocation->rejection_reason)
                                        <span class="text-sm">{{ $allocation->rejection_reason }}</span>
                                    @else
                                        <span class="text-secondary-foreground text-sm">No reason provided</span>
                                    @endif
                                </td>
                                <td>
                                    <button 
                                        onclick="openReallocateModal({{ $allocation->id }}, {{ $allocation->officer_id }}, '{{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }} ({{ $allocation->officer->service_number ?? 'N/A' }})', '{{ ($allocation->quarter->quarter_number ?? 'N/A') . ' (' . ($allocation->quarter->quarter_type ?? 'N/A') . ')' }}')"
                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                        title="Re-allocate to this officer">
                                        <i class="ki-filled ki-check"></i> Re-allocate
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-secondary-foreground">
                                    No rejected allocations found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Re-allocate Modal -->
<div id="reallocate-modal" class="kt-modal" style="display: none;">
    <div class="kt-modal-content">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Re-allocate Quarter</h3>
            <button type="button" class="kt-modal-close" onclick="closeReallocateModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="reallocate-form">
            <input type="hidden" id="reallocate-officer-id">
            <input type="hidden" id="reallocate-allocation-id">
            
            <div class="kt-modal-body">
                <div class="kt-alert kt-alert-info mb-4">
                    <i class="ki-filled ki-information"></i>
                    <div>
                        <strong>Officer:</strong> <span id="reallocate-officer-name"></span><br>
                        <strong>Previous Quarter:</strong> <span id="reallocate-previous-quarter"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="kt-form-label">Select New Quarter <span class="text-danger">*</span></label>
                    <select id="reallocate-quarter-id" name="quarter_id" class="kt-select" required>
                        <option value="">Loading available quarters...</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="kt-form-label">Allocation Date</label>
                    <input type="date" id="reallocate-allocation-date" name="allocation_date" class="kt-input" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" onclick="closeReallocateModal()">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Re-allocate</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('reallocate-form').addEventListener('submit', handleReallocate);
});

function openReallocateModal(allocationId, officerId, officerName, previousQuarter) {
    document.getElementById('reallocate-allocation-id').value = allocationId;
    document.getElementById('reallocate-officer-id').value = officerId;
    document.getElementById('reallocate-officer-name').textContent = officerName;
    document.getElementById('reallocate-previous-quarter').textContent = previousQuarter;
    
    // Load available quarters
    loadAvailableQuarters();
    
    document.getElementById('reallocate-modal').style.display = 'flex';
}

function closeReallocateModal() {
    document.getElementById('reallocate-modal').style.display = 'none';
    document.getElementById('reallocate-form').reset();
}

async function loadAvailableQuarters() {
    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            throw new Error('Authentication token not found');
        }

        const response = await fetch('/api/v1/quarters?is_occupied=0', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to load quarters');
        }

        const data = await response.json();
        const quarters = data.data || [];
        
        const select = document.getElementById('reallocate-quarter-id');
        if (quarters.length === 0) {
            select.innerHTML = '<option value="">No available quarters</option>';
        } else {
            select.innerHTML = '<option value="">Select a quarter...</option>' +
                quarters.map(q => 
                    `<option value="${q.id}">${q.quarter_number} (${q.quarter_type})</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        document.getElementById('reallocate-quarter-id').innerHTML = `<option value="">Error: ${error.message}</option>`;
    }
}

async function handleReallocate(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ki-filled ki-loader"></i> Re-allocating...';
    
    const officerId = document.getElementById('reallocate-officer-id').value;
    const quarterId = document.getElementById('reallocate-quarter-id').value;
    const allocationDate = document.getElementById('reallocate-allocation-date').value;

    if (!quarterId) {
        alert('Please select a quarter');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    try {
        const token = window.API_CONFIG?.token;
        if (!token) {
            alert('Authentication token not found. Please refresh the page.');
            return;
        }

        const response = await fetch('/api/v1/quarters/allocate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                officer_id: officerId,
                quarter_id: quarterId,
                allocation_date: allocationDate || null
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Quarter re-allocated successfully! The officer will need to accept the allocation.');
            closeReallocateModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to re-allocate quarter');
        }
    } catch (error) {
        console.error('Error re-allocating quarter:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function applyFilters() {
    const fromDate = document.getElementById('filter-from-date').value;
    const toDate = document.getElementById('filter-to-date').value;
    
    // Build URL with filters
    let url = new URL(window.location.href);
    if (fromDate) {
        url.searchParams.set('from_date', fromDate);
    } else {
        url.searchParams.delete('from_date');
    }
    if (toDate) {
        url.searchParams.set('to_date', toDate);
    } else {
        url.searchParams.delete('to_date');
    }
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('filter-from-date').value = '';
    document.getElementById('filter-to-date').value = '';
    window.location.href = window.location.pathname;
}
</script>
@endpush
@endsection


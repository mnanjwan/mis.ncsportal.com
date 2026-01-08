@extends('layouts.app')

@section('title', 'Rejected Allocations')
@section('page-title', 'Rejected Quarter Allocations')

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filter Rejected Allocations</h3>
        </div>
        <div class="kt-card-content">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- From Date -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">From Date</label>
                        <input type="date" id="filter-from-date" class="kt-input w-full" value="{{ request('from_date') }}" onchange="applyFilters()">
                    </div>

                    <!-- To Date -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">To Date</label>
                        <input type="date" id="filter-to-date" class="kt-input w-full" value="{{ request('to_date') }}" onchange="applyFilters()">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" onclick="clearFilters()" class="kt-btn kt-btn-outline w-full md:w-auto">
                            <i class="ki-filled ki-cross"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejected Allocations List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Rejected Quarter Allocations</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Table with horizontal scroll wrapper -->
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 1000px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'rejected_at', 'sort_order' => request('sort_by') === 'rejected_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Rejected Date
                                    @if(request('sort_by') === 'rejected_at' || !request('sort_by'))
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' || !request('sort_order') ? 'down' : 'up' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer_name', 'sort_order' => request('sort_by') === 'officer_name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Officer
                                    @if(request('sort_by') === 'officer_name')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Service Number
                                    @if(request('sort_by') === 'service_number')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'quarter_number', 'sort_order' => request('sort_by') === 'quarter_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Quarter Number
                                    @if(request('sort_by') === 'quarter_number')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'quarter_type', 'sort_order' => request('sort_by') === 'quarter_type' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                   class="flex items-center gap-1 hover:text-primary transition-colors">
                                    Quarter Type
                                    @if(request('sort_by') === 'quarter_type')
                                        <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @else
                                        <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Allocated By
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Rejection Reason
                            </th>
                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="rejected-allocations-list">
                        @forelse($rejectedAllocations as $allocation)
                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->rejected_at ? $allocation->rejected_at->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-foreground" style="white-space: nowrap;">
                                    {{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }}
                                </td>
                                <td class="py-3 px-4" style="white-space: nowrap;">
                                    <span class="text-sm font-mono text-foreground">{{ $allocation->officer->service_number ?? 'N/A' }}</span>
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->quarter->quarter_number ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    {{ $allocation->quarter->quarter_type ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                <td class="py-3 px-4 text-sm text-secondary-foreground max-w-xs" style="white-space: nowrap;">
                                    @if($allocation->rejection_reason)
                                        <span>{{ $allocation->rejection_reason }}</span>
                                    @else
                                        <span class="text-secondary-foreground italic">No reason provided</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right" style="white-space: nowrap;">
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
                                <td colspan="8" class="py-12 text-center">
                                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                    <p class="text-secondary-foreground">No rejected allocations found</p>
                                </td>
                            </tr>
                        @endforelse
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

<!-- Re-allocate Modal -->
<div id="reallocate-modal" class="kt-modal" data-kt-modal="true">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-check text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Re-allocate Quarter</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="reallocate-form">
            <input type="hidden" id="reallocate-officer-id">
            <input type="hidden" id="reallocate-allocation-id">
            
            <div class="kt-modal-body py-5 px-5">
                <div class="kt-alert kt-alert-info mb-4">
                    <i class="ki-filled ki-information"></i>
                    <div>
                        <strong>Officer:</strong> <span id="reallocate-officer-name"></span><br>
                        <strong>Previous Quarter:</strong> <span id="reallocate-previous-quarter"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Select New Quarter <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="reallocate-quarter-search" 
                                   class="kt-input w-full" 
                                   placeholder="Search quarters by number or type..."
                                   autocomplete="off">
                            <input type="hidden" 
                                   id="reallocate-quarter-id" 
                                   name="quarter_id">
                            <div id="reallocate-quarter-dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                <!-- Options will be populated by JavaScript -->
                            </div>
                        </div>
                        <div id="selected-reallocate-quarter" class="hidden mt-2 p-2 bg-muted/50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium" id="selected-reallocate-quarter-name"></span>
                                    <span class="text-xs text-secondary-foreground" id="selected-reallocate-quarter-details"></span>
                                </div>
                                <button type="button" 
                                        id="clear-reallocate-quarter" 
                                        class="kt-btn kt-btn-sm kt-btn-ghost text-danger">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Allocation Date</label>
                        <input type="date" id="reallocate-allocation-date" name="allocation_date" class="kt-input w-full" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                <button type="submit" class="kt-btn kt-btn-primary">Re-allocate</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let quartersCache = [];

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('reallocate-form').addEventListener('submit', handleReallocate);
    setupReallocateQuarterSelect();
});

function openReallocateModal(allocationId, officerId, officerName, previousQuarter) {
    document.getElementById('reallocate-allocation-id').value = allocationId;
    document.getElementById('reallocate-officer-id').value = officerId;
    document.getElementById('reallocate-officer-name').textContent = officerName;
    document.getElementById('reallocate-previous-quarter').textContent = previousQuarter;
    
    // Clear previous selection
    document.getElementById('reallocate-quarter-id').value = '';
    document.getElementById('reallocate-quarter-search').value = '';
    document.getElementById('selected-reallocate-quarter').classList.add('hidden');
    
    // Load available quarters
    loadAvailableQuarters();
    
    // Show modal using KTModal system
    const modal = document.getElementById('reallocate-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal) || new KTModal(modal);
        modalInstance.show();
    } else {
        modal.style.display = 'flex';
    }
}

function closeReallocateModal() {
    const modal = document.getElementById('reallocate-modal');
    if (typeof KTModal !== 'undefined') {
        const modalInstance = KTModal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    } else {
        modal.style.display = 'none';
    }
    document.getElementById('reallocate-form').reset();
    document.getElementById('reallocate-quarter-id').value = '';
    document.getElementById('reallocate-quarter-search').value = '';
    document.getElementById('selected-reallocate-quarter').classList.add('hidden');
}

    // Create searchable select function (similar to Manning Request pattern)
    function createSearchableSelect(searchInput, hiddenInput, dropdown, selectedDiv, selectedName, selectedDetails, options, searchFields, displayNameField, displayDetailsField, onSelect) {
        let selectedOption = null;

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                dropdown.classList.add('hidden');
                return;
            }
            
            const filtered = options.filter(opt => {
                return searchFields.some(field => {
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
                const name = opt[displayNameField] || 'N/A';
                const details = opt[displayDetailsField] || '';
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
                const foundOption = options.find(o => o.id == option.dataset.id);
                if (foundOption) {
                    selectedOption = foundOption;
                    hiddenInput.value = selectedOption.id;
                    searchInput.value = selectedOption[displayNameField] || '';
                    if (selectedName) selectedName.textContent = selectedOption[displayNameField] || '';
                    if (selectedDetails) selectedDetails.textContent = selectedOption[displayDetailsField] || '';
                    if (selectedDiv) selectedDiv.classList.remove('hidden');
                    dropdown.classList.add('hidden');
                    if (onSelect) onSelect(selectedOption);
                }
            }
        });

        // Clear selection
        document.getElementById('clear-reallocate-quarter')?.addEventListener('click', function() {
            selectedOption = null;
            hiddenInput.value = '';
            searchInput.value = '';
            if (selectedDiv) selectedDiv.classList.add('hidden');
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function setupReallocateQuarterSelect() {
        const searchInput = document.getElementById('reallocate-quarter-search');
        const hiddenInput = document.getElementById('reallocate-quarter-id');
        const dropdown = document.getElementById('reallocate-quarter-dropdown');
        const selectedDiv = document.getElementById('selected-reallocate-quarter');
        const selectedName = document.getElementById('selected-reallocate-quarter-name');
        const selectedDetails = document.getElementById('selected-reallocate-quarter-details');

        // Create the select but it will be populated when modal opens
        createSearchableSelect(
            searchInput,
            hiddenInput,
            dropdown,
            selectedDiv,
            selectedName,
            selectedDetails,
            quartersCache,
            ['quarter_number', 'quarter_type'],
            'display_name',
            'quarter_type',
            null
        );
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
            quartersCache = (data.data || []).map(q => ({
                id: q.id,
                quarter_number: q.quarter_number || 'N/A',
                quarter_type: q.quarter_type || 'N/A',
                display_name: `${q.quarter_number || 'N/A'} (${q.quarter_type || 'N/A'})`
            }));
            
            // Re-setup the select with new data
            setupReallocateQuarterSelect();
        } catch (error) {
            console.error('Error loading quarters:', error);
            quartersCache = [];
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


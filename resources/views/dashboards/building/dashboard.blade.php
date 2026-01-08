@extends('layouts.app')

@section('title', 'Building Unit Dashboard')
@section('page-title', 'Building Unit Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-normal text-secondary-foreground">Total Quarters</span>
                            <span class="text-xl font-semibold text-mono" id="total-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                            <i class="ki-filled ki-home-2 text-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-normal text-secondary-foreground">Occupied</span>
                            <span class="text-xl font-semibold text-mono" id="occupied-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                            <i class="ki-filled ki-check text-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-normal text-secondary-foreground">Available</span>
                            <span class="text-xl font-semibold text-mono" id="available-quarters">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                            <i class="ki-filled ki-home text-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-normal text-secondary-foreground">Pending Requests</span>
                            <span class="text-xl font-semibold text-mono" id="pending-requests-count">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                            <i class="ki-filled ki-file-up text-lg text-warning"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 mt-1">
                        <a href="{{ route('building.requests') }}" class="kt-btn kt-btn-warning kt-btn-xs justify-center">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-2 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-normal text-secondary-foreground">Rejected</span>
                            <span class="text-xl font-semibold text-mono" id="rejected-count">Loading...</span>
                        </div>
                        <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                            <i class="ki-filled ki-cross-circle text-lg text-danger"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 mt-1">
                        <a href="{{ route('building.rejected-allocations') }}" class="kt-btn kt-btn-danger kt-btn-xs justify-center">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('building.quarters') }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-home-2"></i> Manage Quarters
                    </a>
                    <a href="{{ route('building.officers') }}" class="kt-btn kt-btn-success">
                        <i class="ki-filled ki-user"></i> Manage Officers Quartered Status
                    </a>
                    <a href="{{ route('building.requests') }}" class="kt-btn kt-btn-warning">
                        <i class="ki-filled ki-file-up"></i> Quarter Requests
                    </a>
                    <a href="{{ route('building.allocations') }}" class="kt-btn kt-btn-info">
                        <i class="ki-filled ki-list"></i> Quarter Allocations
                    </a>
                </div>
            </div>
        </div>

        <!-- Rejected Quarter Allocations - Quick Handling -->
        @if(isset($rejectedAllocations) && $rejectedAllocations->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Rejected Quarter Allocations - Quick Handling</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('building.rejected-allocations') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @foreach($rejectedAllocations as $allocation)
                        <div class="p-4 rounded-lg border border-danger/20 bg-danger/5">
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $allocation->officer->service_number ?? 'N/A' }} • {{ $allocation->officer->substantive_rank ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <span class="kt-badge kt-badge-danger kt-badge-sm">REJECTED</span>
                                </div>
                                
                                @if($allocation->quarter)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2 border-t border-border">
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Quarter:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">
                                            {{ $allocation->quarter->quarter_number }} ({{ $allocation->quarter->quarter_type }})
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Rejected On:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">
                                            {{ $allocation->rejected_at ? $allocation->rejected_at->format('d/m/Y H:i') : 'N/A' }}
                                        </span>
                                    </div>
                                    @if($allocation->rejection_reason)
                                    <div class="md:col-span-3">
                                        <span class="text-xs text-secondary-foreground">Reason:</span>
                                        <span class="text-sm text-foreground ml-2">{{ $allocation->rejection_reason }}</span>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <div class="flex gap-2 pt-2">
                                    <button 
                                        onclick="openReallocateModal({{ $allocation->id }}, {{ $allocation->officer_id }}, '{{ ($allocation->officer->initials ?? '') . ' ' . ($allocation->officer->surname ?? '') }} ({{ $allocation->officer->service_number ?? 'N/A' }})', '{{ ($allocation->quarter->quarter_number ?? 'N/A') . ' (' . ($allocation->quarter->quarter_type ?? 'N/A') . ')' }}')"
                                        class="kt-btn kt-btn-primary kt-btn-sm flex-1">
                                        <i class="ki-filled ki-check"></i> Re-allocate Quarter
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($rejectedAllocations->count() >= 10)
                <div class="mt-4 pt-4 border-t border-border text-center">
                    <a href="{{ route('building.rejected-allocations') }}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-eye"></i> View All Rejected Allocations
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Re-allocate Modal -->
    @if(isset($rejectedAllocations) && $rejectedAllocations->count() > 0)
    <div id="reallocate-modal" class="kt-modal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                        <i class="ki-filled ki-check text-primary text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Re-allocate Quarter</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeReallocateModal()">
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
                    <button type="button" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true" onclick="closeReallocateModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-primary">Re-allocate</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
        <script>
            let quartersCache = [];

            document.addEventListener('DOMContentLoaded', async () => {
                @if(isset($rejectedAllocations) && $rejectedAllocations->count() > 0)
                // Setup re-allocate modal
                const reallocateForm = document.getElementById('reallocate-form');
                if (reallocateForm) {
                    reallocateForm.addEventListener('submit', handleReallocate);
                    setupReallocateQuarterSelect();
                }
                @endif
                const token = window.API_CONFIG?.token;

                if (!token) {
                    console.error('API token not found');
                    showError('Authentication required. Please refresh the page.');
                    return;
                }

                try {
                    // Load statistics
                    const statsRes = await fetch('/api/v1/quarters/statistics', {
                        headers: { 
                            'Authorization': 'Bearer ' + token, 
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const statsData = await statsRes.json();

                    if (statsRes.ok && statsData.success) {
                        if (statsData.data) {
                            document.getElementById('total-quarters').textContent = statsData.data.total_quarters || 0;
                            document.getElementById('occupied-quarters').textContent = statsData.data.occupied || 0;
                            document.getElementById('available-quarters').textContent = statsData.data.available || 0;
                        } else {
                            // No data available
                            document.getElementById('total-quarters').textContent = '0';
                            document.getElementById('occupied-quarters').textContent = '0';
                            document.getElementById('available-quarters').textContent = '0';
                        }
                    } else {
                        // Handle error response
                        const errorMsg = statsData.message || 'Failed to load statistics';
                        console.error('API Error:', errorMsg);
                        
                        if (statsData.meta?.code === 'NO_COMMAND_ASSIGNED') {
                            document.getElementById('total-quarters').textContent = 'N/A';
                            document.getElementById('occupied-quarters').textContent = 'N/A';
                            document.getElementById('available-quarters').textContent = 'N/A';
                            showError('You must be assigned to a command to view statistics. Please contact HRD.');
                        } else {
                            document.getElementById('total-quarters').textContent = '0';
                            document.getElementById('occupied-quarters').textContent = '0';
                            document.getElementById('available-quarters').textContent = '0';
                            showError(errorMsg);
                        }
                    }

                    // Load pending quarter requests count
                    const pendingRequestsRes = await fetch('/api/v1/quarters/requests?status=PENDING&per_page=1', {
                        headers: { 
                            'Authorization': 'Bearer ' + token, 
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (pendingRequestsRes.ok) {
                        const pendingRequestsData = await pendingRequestsRes.json();
                        if (pendingRequestsData.success && pendingRequestsData.meta) {
                            document.getElementById('pending-requests-count').textContent = pendingRequestsData.meta.total || 0;
                        } else {
                            document.getElementById('pending-requests-count').textContent = '0';
                        }
                    } else {
                        document.getElementById('pending-requests-count').textContent = '0';
                    }

                    // Load rejected allocations count
                    const rejectedRes = await fetch('/api/v1/quarters/rejected-allocations', {
                        headers: { 
                            'Authorization': 'Bearer ' + token, 
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (rejectedRes.ok) {
                        const rejectedData = await rejectedRes.json();
                        if (rejectedData.success && rejectedData.data) {
                            document.getElementById('rejected-count').textContent = rejectedData.data.length || 0;
                        } else {
                            document.getElementById('rejected-count').textContent = '0';
                        }
                    } else {
                        document.getElementById('rejected-count').textContent = '0';
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                    document.getElementById('total-quarters').textContent = 'Error';
                    document.getElementById('occupied-quarters').textContent = 'Error';
                    document.getElementById('available-quarters').textContent = 'Error';
                    document.getElementById('pending-requests-count').textContent = 'Error';
                    document.getElementById('rejected-count').textContent = 'Error';
                    showError('Failed to load dashboard data. Please refresh the page.');
                }
            });

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

            @if(isset($rejectedAllocations) && $rejectedAllocations->count() > 0)
            // Re-allocate Modal Functions
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
                const form = document.getElementById('reallocate-form');
                if (form) form.reset();
                document.getElementById('reallocate-quarter-id').value = '';
                document.getElementById('reallocate-quarter-search').value = '';
                document.getElementById('selected-reallocate-quarter').classList.add('hidden');
            }

            function setupReallocateQuarterSelect() {
                const searchInput = document.getElementById('reallocate-quarter-search');
                const hiddenInput = document.getElementById('reallocate-quarter-id');
                const dropdown = document.getElementById('reallocate-quarter-dropdown');
                const selectedDiv = document.getElementById('selected-reallocate-quarter');
                const selectedName = document.getElementById('selected-reallocate-quarter-name');
                const selectedDetails = document.getElementById('selected-reallocate-quarter-details');

                if (!searchInput || !hiddenInput || !dropdown) return;

                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    
                    if (searchTerm.length === 0) {
                        dropdown.classList.add('hidden');
                        return;
                    }
                    
                    const filtered = quartersCache.filter(opt => {
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
                        const foundOption = quartersCache.find(o => o.id == option.dataset.id);
                        if (foundOption) {
                            hiddenInput.value = foundOption.id;
                            searchInput.value = foundOption.display_name || '';
                            if (selectedName) selectedName.textContent = foundOption.display_name || '';
                            if (selectedDetails) selectedDetails.textContent = foundOption.quarter_type || '';
                            if (selectedDiv) selectedDiv.classList.remove('hidden');
                            dropdown.classList.add('hidden');
                        }
                    }
                });

                // Clear selection
                const clearBtn = document.getElementById('clear-reallocate-quarter');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        hiddenInput.value = '';
                        searchInput.value = '';
                        if (selectedDiv) selectedDiv.classList.add('hidden');
                    });
                }

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
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
                    showError('Please select a quarter');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    return;
                }

                try {
                    const token = window.API_CONFIG?.token;
                    if (!token) {
                        showError('Authentication token not found. Please refresh the page.');
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
                        showSuccess('Quarter re-allocated successfully! The officer will need to accept the allocation.');
                        closeReallocateModal();
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError(data.message || 'Failed to re-allocate quarter');
                    }
                } catch (error) {
                    console.error('Error re-allocating quarter:', error);
                    showError('An error occurred. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
            @endif
        </script>
    @endpush
@endsection

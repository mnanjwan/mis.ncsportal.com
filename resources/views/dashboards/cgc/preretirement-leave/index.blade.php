@extends('layouts.app')

@section('title', 'Preretirement Leave Management')
@section('page-title', 'Preretirement Leave Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('cgc.dashboard') }}">CGC Office</a>
    <span>/</span>
    <span class="text-primary">Preretirement Leave</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters and Actions -->
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                <div class="flex flex-col md:flex-row gap-4 flex-1">
                    <form method="GET" action="{{ route('cgc.preretirement-leave.index') }}" class="flex gap-2 flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by service number or name..." 
                               class="kt-input flex-1">
                        <div class="relative">
                            <input type="hidden" name="status" id="status_id" value="{{ request('status') ?? '' }}">
                            <button type="button" 
                                    id="status_select_trigger" 
                                    class="kt-input text-left flex items-center justify-between cursor-pointer">
                                <span id="status_select_text">{{ request('status') ? (request('status') === 'AUTO_PLACED' ? 'Auto Placed' : (request('status') === 'CGC_APPROVED_IN_OFFICE' ? 'CGC Office Approved (In Office)' : 'All Status')) : 'All Status' }}</span>
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
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                        @if(request('search') || request('status'))
                            <a href="{{ route('cgc.preretirement-leave.index') }}" class="kt-btn kt-btn-secondary">
                                <i class="ki-filled ki-cross"></i> Clear
                            </a>
                        @endif
                    </form>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cgc.preretirement-leave.approaching') }}" class="kt-btn kt-btn-warning">
                        <i class="ki-filled ki-calendar"></i> Officers Approaching
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Preretirement Leave List -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers on Preretirement Leave</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                <table class="kt-table" style="min-width: 1000px; width: 100%;">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">SVC No</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Preretirement Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Retirement Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="border-b border-border hover:bg-muted/30 transition-colors">
                                <td class="py-3 px-4 text-sm">{{ $item->officer->service_number ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm font-medium">{{ $item->officer->full_name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->rank ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->date_of_pre_retirement_leave ? $item->date_of_pre_retirement_leave->format('d/m/Y') : 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">{{ $item->retirement_date ? $item->retirement_date->format('d/m/Y') : 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm">
                                    @if($item->preretirement_leave_status === 'AUTO_PLACED')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-info/10 text-info">
                                            <i class="ki-filled ki-calendar-2"></i> Auto Placed
                                        </span>
                                    @elseif($item->preretirement_leave_status === 'CGC_APPROVED_IN_OFFICE')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-success/10 text-success">
                                            <i class="ki-filled ki-check-circle"></i> Approved (In Office)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-muted text-secondary-foreground">
                                            {{ $item->preretirement_leave_status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('cgc.preretirement-leave.show', $item->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-eye"></i> View
                                        </a>
                                        @if($item->preretirement_leave_status === 'AUTO_PLACED')
                                            <button type="button" 
                                                    onclick="openApproveModal({{ $item->id }}, '{{ $item->officer->full_name ?? 'N/A' }}')"
                                                    class="kt-btn kt-btn-sm kt-btn-success">
                                                <i class="ki-filled ki-check"></i> Approve In Office
                                            </button>
                                        @elseif($item->preretirement_leave_status === 'CGC_APPROVED_IN_OFFICE')
                                            <button type="button" 
                                                    onclick="openCancelApprovalModal({{ $item->id }}, '{{ $item->officer->full_name ?? 'N/A' }}')"
                                                    class="kt-btn kt-btn-sm kt-btn-danger">
                                                <i class="ki-filled ki-cross"></i> Cancel Approval
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-secondary-foreground">
                                    <i class="ki-filled ki-information text-4xl mb-2"></i>
                                    <p>No officers found on preretirement leave.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($items->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve In Office Modal -->
<div class="kt-modal" data-kt-modal="true" id="approveModal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Approve Officer for Preretirement Leave In Office</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeApproveModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="approveForm" method="POST">
            @csrf
            <div class="kt-modal-body py-5 px-5">
                <div class="mb-4">
                    <p class="text-sm text-secondary-foreground mb-2">
                        Officer: <span id="officerName" class="font-medium"></span>
                    </p>
                    <p class="text-xs text-secondary-foreground">
                        This will allow the officer to continue working during the preretirement period (last 3 months before retirement).
                    </p>
                </div>
                <div class="mb-4">
                    <label for="approval_reason" class="block text-sm font-medium mb-2">Approval Reason (Optional)</label>
                    <textarea id="approval_reason" name="approval_reason" rows="3" 
                              class="kt-input w-full" 
                              placeholder="Enter reason for approval..."></textarea>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeApproveModal()" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Approve
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(itemId, officerName) {
    document.getElementById('officerName').textContent = officerName;
    document.getElementById('approveForm').action = `/cgc/preretirement-leave/${itemId}/approve-in-office`;
    
    // Use KT UI modal toggle approach - create temporary button and trigger click
    const toggleBtn = document.createElement('button');
    toggleBtn.setAttribute('data-kt-modal-toggle', '#approveModal');
    toggleBtn.style.display = 'none';
    document.body.appendChild(toggleBtn);
    
    // Trigger click event
    toggleBtn.click();
    
    // Clean up after a short delay to allow modal to open
    setTimeout(() => {
        if (document.body.contains(toggleBtn)) {
            document.body.removeChild(toggleBtn);
        }
    }, 100);
}

function closeApproveModal() {
    const modalElement = document.getElementById('approveModal');
    if (modalElement) {
        const dismissBtn = modalElement.querySelector('[data-kt-modal-dismiss]');
        if (dismissBtn) {
            dismissBtn.click();
        }
    }
    document.getElementById('approveForm').reset();
}

function openCancelApprovalModal(itemId, officerName) {
    document.getElementById('cancelOfficerName').textContent = officerName;
    document.getElementById('cancelApprovalForm').action = `/cgc/preretirement-leave/${itemId}/cancel-approval`;
    
    // Use KT UI modal toggle approach - create temporary button and trigger click
    const toggleBtn = document.createElement('button');
    toggleBtn.setAttribute('data-kt-modal-toggle', '#cancelApprovalModal');
    toggleBtn.style.display = 'none';
    document.body.appendChild(toggleBtn);
    
    // Trigger click event
    toggleBtn.click();
    
    // Clean up after a short delay to allow modal to open
    setTimeout(() => {
        if (document.body.contains(toggleBtn)) {
            document.body.removeChild(toggleBtn);
        }
    }, 100);
}

function closeCancelApprovalModal() {
    const modalElement = document.getElementById('cancelApprovalModal');
    if (modalElement) {
        const dismissBtn = modalElement.querySelector('[data-kt-modal-dismiss]');
        if (dismissBtn) {
            dismissBtn.click();
        }
    }
}
</script>

<!-- Cancel Approval Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="cancelApprovalModal">
    <div class="kt-modal-content max-w-md">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                    <i class="ki-filled ki-information text-warning text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Cancel CGC Office Approval</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true" onclick="closeCancelApprovalModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="cancelApprovalForm" method="POST" onsubmit="closeCancelApprovalModal()">
            @csrf
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground mb-2">
                    Officer: <span id="cancelOfficerName" class="font-medium"></span>
                </p>
                <p class="text-sm text-secondary-foreground">
                    Are you sure you want to cancel this approval? The officer will revert to automatic preretirement leave.
                </p>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCancelApprovalModal()" class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                    Cancel
                </button>
                <button type="submit" class="kt-btn kt-btn-danger">
                    <i class="ki-filled ki-cross"></i> Cancel Approval
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Status options
        const statusOptions = [
            {id: '', name: 'All Status'},
            {id: 'AUTO_PLACED', name: 'Auto Placed'},
            {id: 'CGC_APPROVED_IN_OFFICE', name: 'CGC Office Approved (In Office)'}
        ];

        // Initialize status select
        createSearchableSelect({
            triggerId: 'status_select_trigger',
            hiddenInputId: 'status_id',
            dropdownId: 'status_dropdown',
            searchInputId: 'status_search_input',
            optionsContainerId: 'status_options',
            displayTextId: 'status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...'
        });
    });
</script>
@endpush
@endsection


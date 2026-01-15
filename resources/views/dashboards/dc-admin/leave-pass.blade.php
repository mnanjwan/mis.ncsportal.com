@extends('layouts.app')

@section('title', 'Leave & Pass Management')
@section('page-title', 'Leave & Pass Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <span class="text-primary">Leave & Pass</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <!-- Tabs -->
    <div class="kt-card">
        <div class="kt-card-header">
            <ul class="flex gap-2 border-b border-input">
                <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'leave' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                    <a href="{{ route('dc-admin.leave-pass', ['type' => 'leave', 'status' => request('status')]) }}">
                        Leave Applications
                    </a>
                </li>
                <li class="cursor-pointer px-4 py-2 border-b-2 {{ $type === 'pass' ? 'border-primary text-primary font-semibold' : 'border-transparent text-secondary-foreground hover:text-primary' }}">
                    <a href="{{ route('dc-admin.leave-pass', ['type' => 'pass', 'status' => request('status')]) }}">
                        Pass Applications
                    </a>
                </li>
            </ul>
        </div>
        <div class="kt-card-content">
            @if($type === 'leave')
            <!-- Leave Applications Tab -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mono">Minuted Leave Applications</h3>
                <form method="GET" action="{{ route('dc-admin.leave-pass') }}" id="leave-status-filter-form" class="inline">
                    <input type="hidden" name="type" value="leave">
                    <div class="relative">
                        <input type="hidden" name="status" id="leave_status_id" value="{{ request('status') ?? '' }}">
                        <button type="button" 
                                id="leave_status_select_trigger" 
                                class="kt-input text-left flex items-center justify-between cursor-pointer">
                            <span id="leave_status_select_text">{{ request('status') ? (request('status') === 'PENDING' ? 'Pending' : (request('status') === 'APPROVED' ? 'Approved' : (request('status') === 'REJECTED' ? 'Rejected' : 'All Status'))) : 'All Status' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="leave_status_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="leave_status_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search status..."
                                       autocomplete="off">
                            </div>
                            <div id="leave_status_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex flex-col gap-4">
                @forelse($leaveApplications as $app)
                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-calendar text-warning text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">
                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->leaveType->name ?? 'N/A' }}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }} ({{ $app->number_of_days }} days)
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Minuted: {{ $app->minuted_at ? $app->minuted_at->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                            {{ $app->status }}
                        </span>
                        @if($app->status === 'PENDING')
                            <form action="{{ route('dc-admin.leave-applications.approve', $app->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-success" onclick="return confirm('Approve this leave application?')">
                                    <i class="ki-filled ki-check"></i> Approve
                                </button>
                            </form>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger" onclick="showRejectModal({{ $app->id }}, 'leave')">
                                <i class="ki-filled ki-cross"></i> Reject
                            </button>
                        @endif
                        <a href="{{ route('dc-admin.leave-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
                @empty
                    <p class="text-secondary-foreground text-center py-8">No minuted leave applications found</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $leaveApplications->links() }}
            </div>
            @else
            <!-- Pass Applications Tab -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-mono">Minuted Pass Applications</h3>
                <form method="GET" action="{{ route('dc-admin.leave-pass') }}" id="pass-status-filter-form" class="inline">
                    <input type="hidden" name="type" value="pass">
                    <div class="relative">
                        <input type="hidden" name="status" id="pass_status_id" value="{{ request('status') ?? '' }}">
                        <button type="button" 
                                id="pass_status_select_trigger" 
                                class="kt-input text-left flex items-center justify-between cursor-pointer">
                            <span id="pass_status_select_text">{{ request('status') ? (request('status') === 'PENDING' ? 'Pending' : (request('status') === 'APPROVED' ? 'Approved' : (request('status') === 'REJECTED' ? 'Rejected' : 'All Status'))) : 'All Status' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="pass_status_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="pass_status_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search status..."
                                       autocomplete="off">
                            </div>
                            <div id="pass_status_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex flex-col gap-4">
                @forelse($passApplications as $app)
                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-calendar-tick text-info text-xl"></i>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-foreground">
                                {{ $app->officer->initials ?? '' }} {{ $app->officer->surname ?? '' }} - {{ $app->number_of_days }} days
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                {{ $app->start_date->format('M d, Y') }} to {{ $app->end_date->format('M d, Y') }}
                            </span>
                            <span class="text-xs text-secondary-foreground">
                                Minuted: {{ $app->minuted_at ? $app->minuted_at->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="kt-badge kt-badge-{{ strtolower($app->status) === 'approved' ? 'success' : (strtolower($app->status) === 'pending' ? 'warning' : 'danger') }} kt-badge-sm">
                            {{ $app->status }}
                        </span>
                        @if($app->status === 'PENDING')
                            <form action="{{ route('dc-admin.pass-applications.approve', $app->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-success" onclick="return confirm('Approve this pass application?')">
                                    <i class="ki-filled ki-check"></i> Approve
                                </button>
                            </form>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-danger" onclick="showRejectModal({{ $app->id }}, 'pass')">
                                <i class="ki-filled ki-cross"></i> Reject
                            </button>
                        @endif
                        <a href="{{ route('dc-admin.pass-applications.show', $app->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-eye"></i> View
                        </a>
                    </div>
                </div>
                @empty
                    <p class="text-secondary-foreground text-center py-8">No minuted pass applications found</p>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $passApplications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="kt-card max-w-md w-full mx-4">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Reject Application</h3>
        </div>
        <form id="reject-form" method="POST" class="kt-card-content">
            @csrf
            <div class="flex flex-col gap-4">
                <div>
                    <label class="kt-form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="kt-input" rows="4" placeholder="Enter reason for rejection" required></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger">Reject</button>
                </div>
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
            {id: 'PENDING', name: 'Pending'},
            {id: 'APPROVED', name: 'Approved'},
            {id: 'REJECTED', name: 'Rejected'}
        ];

        // Initialize leave status select
        createSearchableSelect({
            triggerId: 'leave_status_select_trigger',
            hiddenInputId: 'leave_status_id',
            dropdownId: 'leave_status_dropdown',
            searchInputId: 'leave_status_search_input',
            optionsContainerId: 'leave_status_options',
            displayTextId: 'leave_status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                document.getElementById('leave-status-filter-form').submit();
            }
        });

        // Initialize pass status select
        createSearchableSelect({
            triggerId: 'pass_status_select_trigger',
            hiddenInputId: 'pass_status_id',
            dropdownId: 'pass_status_dropdown',
            searchInputId: 'pass_status_search_input',
            optionsContainerId: 'pass_status_options',
            displayTextId: 'pass_status_select_text',
            options: statusOptions,
            placeholder: 'All Status',
            searchPlaceholder: 'Search status...',
            onSelect: function() {
                document.getElementById('pass-status-filter-form').submit();
            }
        });
    });

function showRejectModal(id, type) {
    const modal = document.getElementById('reject-modal');
    const form = document.getElementById('reject-form');
    const route = type === 'leave' 
        ? '{{ route("dc-admin.leave-applications.reject", ":id") }}'
        : '{{ route("dc-admin.pass-applications.reject", ":id") }}';
    form.action = route.replace(':id', id);
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.getElementById('reject-form').reset();
}

// Close modal on outside click
document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection

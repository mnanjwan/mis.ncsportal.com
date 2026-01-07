@extends('layouts.app')

@section('title', 'View Draft Items')
@section('page-title', 'View Draft Items for Manning Request')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests') }}">Manning Requests</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}">Request Details</a>
    <span>/</span>
    <span class="text-primary">View Draft</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Request Details
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('hrd.manning-deployments.draft') }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                <i class="ki-filled ki-file-add"></i> Go to Draft Deployment
            </a>
            @if($activeDraft)
                <form action="{{ route('hrd.manning-deployments.publish', $activeDraft->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to publish this deployment? This will create movement orders and post officers to their new commands.');">
                    @csrf
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Publish Deployment
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Request Header -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-sm text-info font-medium">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Draft Items:</strong> This page shows all officers from Manning Request #{{ $manningRequest->id }} that are currently in the draft deployment. You can manage these officers from the main draft deployment page.
                </p>
            </div>
            <h3 class="text-lg font-semibold mb-4">Manning Request Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-secondary-foreground">Request ID:</span>
                    <span class="font-semibold text-mono ml-2">#{{ $manningRequest->id }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Command:</span>
                    <span class="font-semibold text-mono ml-2">{{ $manningRequest->command->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Total Items in Draft:</span>
                    <span class="font-semibold text-mono ml-2">{{ $assignments->count() }} officer(s)</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Ranks in Draft:</span>
                    <span class="font-semibold text-mono ml-2">{{ $assignmentsByItem->count() }} rank(s)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Officers in Draft -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">
                Officers in Draft Deployment
                <span class="text-sm font-normal text-secondary-foreground">
                    ({{ $assignments->count() }} officer(s))
                </span>
            </h3>
        </div>
        <div class="kt-card-content">
            @if($assignments->count() > 0)
                @foreach($assignmentsByItem as $itemId => $itemAssignments)
                    @php
                        $item = $itemAssignments->first()->manningRequestItem;
                    @endphp
                    <div class="mb-6 last:mb-0">
                        <div class="mb-3 pb-2 border-b border-primary/20">
                            <h4 class="text-md font-semibold text-primary">
                                {{ $item->rank ?? 'Unknown Rank' }} 
                                <span class="text-sm font-normal text-secondary-foreground">
                                    ({{ $itemAssignments->count() }} of {{ $item->quantity_needed ?? 0 }} needed)
                                </span>
                            </h4>
                        </div>
                        
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <table class="kt-table w-full">
                                    <thead>
                                        <tr class="border-b border-border">
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Zone</th>
                                            <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemAssignments as $assignment)
                                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                                <td class="py-3 px-4">
                                                    <span class="text-sm font-medium text-foreground">
                                                        {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                                    {{ $assignment->officer->service_number ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->officer->substantive_rank ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->fromCommand->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->toCommand->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->officer->presentStation->zone->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" 
                                                                class="kt-btn kt-btn-sm kt-btn-secondary"
                                                                data-kt-modal-toggle="#swap-officer-modal-{{ $assignment->id }}"
                                                                onclick="prepareSwapModal({{ $assignment->id }}, '{{ addslashes(($assignment->officer->initials ?? '') . ' ' . ($assignment->officer->surname ?? '')) }}', {{ $assignment->officer->id }})">
                                                            <i class="ki-filled ki-arrows-circle"></i> Swap
                                                        </button>
                                                        <button type="button" 
                                                                class="kt-btn kt-btn-sm kt-btn-danger"
                                                                data-kt-modal-toggle="#remove-officer-modal-{{ $assignment->id }}">
                                                            <i class="ki-filled ki-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden">
                            <div class="flex flex-col gap-3">
                                @foreach($itemAssignments as $assignment)
                                    <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground font-mono">
                                                {{ $assignment->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground mb-3">
                                            <div>Rank: <span class="font-semibold">{{ $assignment->officer->substantive_rank ?? 'N/A' }}</span></div>
                                            <div>Zone: <span class="font-semibold">{{ $assignment->officer->presentStation->zone->name ?? 'N/A' }}</span></div>
                                            <div>From: <span class="font-semibold">{{ $assignment->fromCommand->name ?? 'N/A' }}</span></div>
                                            <div>To: <span class="font-semibold">{{ $assignment->toCommand->name ?? 'N/A' }}</span></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" 
                                                    class="kt-btn kt-btn-sm kt-btn-secondary flex-1"
                                                    data-kt-modal-toggle="#swap-officer-modal-{{ $assignment->id }}"
                                                    onclick="prepareSwapModal({{ $assignment->id }}, '{{ addslashes(($assignment->officer->initials ?? '') . ' ' . ($assignment->officer->surname ?? '')) }}', {{ $assignment->officer->id }})">
                                                <i class="ki-filled ki-arrows-circle"></i> Swap
                                            </button>
                                            <button type="button" 
                                                    class="kt-btn kt-btn-sm kt-btn-danger flex-1"
                                                    data-kt-modal-toggle="#remove-officer-modal-{{ $assignment->id }}">
                                                <i class="ki-filled ki-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers from this request are currently in the draft deployment.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Swap Officer Modals (one per assignment) -->
@if($activeDraft && $assignments->count() > 0)
    @foreach($assignments as $assignment)
        <div class="kt-modal" data-kt-modal="true" id="swap-officer-modal-{{ $assignment->id }}">
            <div class="kt-modal-content max-w-[600px]">
                <div class="kt-modal-header py-4 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                            <i class="ki-filled ki-arrows-circle text-primary text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Swap Officer</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <form id="swap-form-{{ $assignment->id }}" method="POST" action="{{ route('hrd.manning-deployments.draft.swap-officer', ['deploymentId' => $activeDraft->id, 'assignmentId' => $assignment->id]) }}">
                        @csrf
                        <p class="text-sm text-secondary-foreground mb-4">
                            Select a new officer to replace <span class="font-semibold">{{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}</span>:
                        </p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Search Officer</label>
                            <input type="text" 
                                   id="officer-search-{{ $assignment->id }}" 
                                   class="kt-input w-full" 
                                   placeholder="Search by name, service number, or rank..."
                                   autocomplete="off">
                            <div id="officer-search-results-{{ $assignment->id }}" class="mt-2 max-h-60 overflow-y-auto border border-input rounded-lg hidden"></div>
                        </div>
                        <input type="hidden" id="new-officer-id-{{ $assignment->id }}" name="new_officer_id" required>
                    </form>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                    <button type="button" class="kt-btn kt-btn-primary" id="confirm-swap-btn-{{ $assignment->id }}" disabled onclick="submitSwapForm({{ $assignment->id }})">
                        <i class="ki-filled ki-arrows-circle"></i> Swap Officer
                    </button>
                </div>
            </div>
        </div>

        <!-- Remove Officer Confirmation Modal -->
        <div class="kt-modal" data-kt-modal="true" id="remove-officer-modal-{{ $assignment->id }}">
            <div class="kt-modal-content max-w-[500px]">
                <div class="kt-modal-header py-4 px-5">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-danger/10">
                            <i class="ki-filled ki-trash text-danger text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Remove Officer</h3>
                    </div>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <form id="remove-form-{{ $assignment->id }}" method="POST" action="{{ route('hrd.manning-deployments.draft.remove-officer', ['deploymentId' => $activeDraft->id, 'assignmentId' => $assignment->id]) }}">
                        @csrf
                        @method('DELETE')
                        <p class="text-sm text-secondary-foreground mb-4">
                            Are you sure you want to remove <span class="font-semibold">{{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}</span> ({{ $assignment->officer->service_number ?? 'N/A' }}) from this deployment?
                        </p>
                        <div class="p-3 bg-muted/50 rounded-lg">
                            <div class="text-sm text-secondary-foreground">
                                <div><strong>Rank:</strong> {{ $assignment->officer->substantive_rank ?? 'N/A' }}</div>
                                <div><strong>From:</strong> {{ $assignment->fromCommand->name ?? 'N/A' }}</div>
                                <div><strong>To:</strong> {{ $assignment->toCommand->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                    <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                    <button type="button" class="kt-btn kt-btn-danger" onclick="submitRemoveForm({{ $assignment->id }})">
                        <i class="ki-filled ki-trash"></i> Remove Officer
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@endif

<script>
// Prepare swap modal when opened
function prepareSwapModal(assignmentId, officerName, currentOfficerId) {
    const searchInput = document.getElementById(`officer-search-${assignmentId}`);
    const resultsDiv = document.getElementById(`officer-search-results-${assignmentId}`);
    const newOfficerIdInput = document.getElementById(`new-officer-id-${assignmentId}`);
    const confirmBtn = document.getElementById(`confirm-swap-btn-${assignmentId}`);
    
    // Reset form
    if (searchInput) searchInput.value = '';
    if (newOfficerIdInput) newOfficerIdInput.value = '';
    if (confirmBtn) confirmBtn.disabled = true;
    if (resultsDiv) resultsDiv.classList.add('hidden');
}

// Submit swap form
function submitSwapForm(assignmentId) {
    const form = document.getElementById(`swap-form-${assignmentId}`);
    const newOfficerId = document.getElementById(`new-officer-id-${assignmentId}`)?.value;
    if (form && newOfficerId) {
        form.submit();
    }
}

// Submit remove form
function submitRemoveForm(assignmentId) {
    const form = document.getElementById(`remove-form-${assignmentId}`);
    if (form) {
        form.submit();
    }
}

// Setup officer search for swap modals
@if($activeDraft && $assignments->count() > 0)
    @foreach($assignments as $assignment)
        (function() {
            const assignmentId = {{ $assignment->id }};
            const searchInput = document.getElementById(`officer-search-${assignmentId}`);
            const resultsDiv = document.getElementById(`officer-search-results-${assignmentId}`);
            let searchTimeout;
            
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const query = e.target.value.trim();
                    
                    if (query.length < 2) {
                        if (resultsDiv) resultsDiv.classList.add('hidden');
                        return;
                    }
                    
                    searchTimeout = setTimeout(() => {
                        fetch(`{{ route('hrd.officers.search') }}?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (!resultsDiv) return;
                                resultsDiv.innerHTML = '';
                                if (data.length === 0) {
                                    resultsDiv.innerHTML = '<div class="p-4 text-sm text-secondary-foreground">No officers found</div>';
                                } else {
                                    data.forEach(officer => {
                                        const div = document.createElement('div');
                                        div.className = 'p-3 hover:bg-muted cursor-pointer border-b border-input last:border-0';
                                        div.innerHTML = `
                                            <div class="font-semibold">${officer.initials} ${officer.surname}</div>
                                            <div class="text-xs text-secondary-foreground">${officer.service_number} - ${officer.substantive_rank} - ${officer.present_station_name || 'N/A'}</div>
                                        `;
                                        div.addEventListener('click', () => {
                                            const newOfficerIdInput = document.getElementById(`new-officer-id-${assignmentId}`);
                                            const confirmBtn = document.getElementById(`confirm-swap-btn-${assignmentId}`);
                                            if (newOfficerIdInput) newOfficerIdInput.value = officer.id;
                                            if (searchInput) searchInput.value = `${officer.initials} ${officer.surname} (${officer.service_number})`;
                                            resultsDiv.classList.add('hidden');
                                            if (confirmBtn) confirmBtn.disabled = false;
                                        });
                                        resultsDiv.appendChild(div);
                                    });
                                }
                                resultsDiv.classList.remove('hidden');
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                            });
                    }, 300);
                });
            }
        })();
    @endforeach
@endif
</script>
@endsection


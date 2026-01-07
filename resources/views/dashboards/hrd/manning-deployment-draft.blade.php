@extends('layouts.app')

@section('title', 'Draft Deployment Management')
@section('page-title', 'Draft Deployment Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    @if(isset($manningRequest))
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests') }}">Manning Requests</a>
        <span>/</span>
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}">Request #{{ $manningRequest->id }}</a>
        <span>/</span>
        <span class="text-primary">Draft Deployment</span>
    @else
    <span class="text-primary">Draft Deployment</span>
    @endif
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-mono">Draft Deployment Management</h2>
            <p class="text-sm text-secondary-foreground mt-1">Review and adjust officer assignments before publishing</p>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($manningRequest))
                <a href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                    <i class="ki-filled ki-arrow-left"></i> Back to Request Details
                </a>
            @else
            <a href="{{ route('hrd.manning-requests') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Requests
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="kt-alert kt-alert-success">
            <div class="kt-alert-content">
                <i class="ki-filled ki-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-alert kt-alert-error">
            <div class="kt-alert-content">
                <i class="ki-filled ki-cross-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($activeDraft)
        @if(isset($manningRequest))
            <!-- Manning Request Info -->
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                        <p class="text-sm text-info font-medium">
                            <i class="ki-filled ki-information"></i> 
                            <strong>Filtered View:</strong> Showing draft deployment items for Manning Request #{{ $manningRequest->id }} ({{ $manningRequest->command->name ?? 'N/A' }}).
                        </p>
                    </div>
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
                            <span class="text-secondary-foreground">Items in Draft:</span>
                            <span class="font-semibold text-mono ml-2">{{ $assignmentsByCommand->sum(fn($group) => $group->count()) }} officer(s)</span>
                        </div>
                        <div>
                            <span class="text-secondary-foreground">Total in Draft:</span>
                            <span class="font-semibold text-mono ml-2">{{ $activeDraft->assignments->count() }} officer(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Draft Info -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Deployment: {{ $activeDraft->deployment_number }}</h3>
                        <div class="flex items-center gap-4 text-sm text-secondary-foreground">
                            <span>Status: <span class="kt-badge kt-badge-warning kt-badge-sm">DRAFT</span></span>
                            <span>Created: {{ $activeDraft->created_at->format('d/m/Y H:i') }}</span>
                            <span>Total Officers: {{ $activeDraft->assignments->count() }}</span>
                            @if(isset($manningRequest))
                                <span>Showing: {{ $assignmentsByCommand->sum(fn($group) => $group->count()) }} from Request #{{ $manningRequest->id }}</span>
                            @endif
                        </div>
                    </div>
                    <form id="publish-deployment-form" action="{{ route('hrd.manning-deployments.publish', $activeDraft->id) }}" method="POST">
                        @csrf
                        @if(isset($manningRequest))
                            <input type="hidden" name="manning_request_id" value="{{ $manningRequest->id }}">
                        @endif
                        <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#publish-deployment-modal">
                            <i class="ki-filled ki-check"></i> Publish Deployment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assignments by Command -->
        <div id="assignments-container">
        @if($assignmentsByCommand->count() > 0)
            @foreach($assignmentsByCommand as $commandId => $assignments)
                @php
                    $command = $assignments->first()->toCommand;
                @endphp
                <div class="kt-card command-section" data-command-id="{{ $commandId }}">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">{{ $command->name ?? 'Unknown Command' }}</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="overflow-x-auto">
                            <table class="kt-table w-full">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                        <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group assignments by rank
                                        $assignmentsByRank = $assignments->groupBy(function($assignment) {
                                            return $assignment->officer->substantive_rank ?? 'Unknown';
                                        });
                                    @endphp
                                    @foreach($assignmentsByRank as $rank => $rankAssignments)
                                        @if($loop->first)
                                            {{-- First rank header --}}
                                            <tr>
                                                <td colspan="5" class="py-2 px-4 bg-primary/5 border-b border-primary/20">
                                                    <span class="text-xs font-semibold text-primary">{{ $rank }}</span>
                                                </td>
                                            </tr>
                                        @else
                                            {{-- Rank separator for subsequent ranks --}}
                                            <tr class="border-t-2 border-primary/30">
                                                <td colspan="5" class="py-2 px-4 bg-primary/5">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-1 border-t border-primary/20"></div>
                                                        <span class="text-xs font-semibold text-primary px-2">{{ $rank }}</span>
                                                        <div class="flex-1 border-t border-primary/20"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @foreach($rankAssignments as $assignment)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors officer-row" 
                                            data-officer-name="{{ strtolower(($assignment->officer->initials ?? '') . ' ' . ($assignment->officer->surname ?? '')) }}"
                                            data-service-number="{{ strtolower($assignment->officer->service_number ?? '') }}"
                                            data-rank="{{ strtolower($assignment->officer->substantive_rank ?? '') }}"
                                            data-command-id="{{ $commandId }}"
                                            data-from-command="{{ strtolower($assignment->fromCommand->name ?? '') }}">
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
                                            <td class="py-3 px-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" 
                                                            class="kt-btn kt-btn-sm kt-btn-secondary"
                                                                data-kt-modal-toggle="#swap-officer-modal-{{ $assignment->id }}"
                                                                onclick="prepareSwapModal({{ $assignment->id }}, '{{ addslashes(($assignment->officer->initials ?? '') . ' ' . ($assignment->officer->surname ?? '')) }}', {{ $assignment->officer->id }}, '{{ addslashes($assignment->officer->substantive_rank ?? '') }}')">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="kt-card">
                <div class="kt-card-content p-12 text-center">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers in draft deployment yet. Add officers from manning requests.</p>
                </div>
            </div>
        @endif
        </div>
    @else
        <div class="kt-card">
            <div class="kt-card-content p-12 text-center">
                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground mb-4">No active draft deployment. Add officers from manning requests to create one.</p>
                <a href="{{ route('hrd.manning-requests') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-arrow-right"></i> Go to Manning Requests
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Swap Officer Modals (one per assignment) -->
@if($activeDraft)
    @foreach($activeDraft->assignments as $assignment)
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
                            <label class="block text-sm font-medium mb-2">Search Officer (Same Rank: {{ $assignment->officer->substantive_rank ?? 'N/A' }})</label>
                            <input type="text" 
                                   id="officer-search-{{ $assignment->id }}" 
                                   class="kt-input w-full" 
                                   placeholder="Search by name or service number (filtered to {{ $assignment->officer->substantive_rank ?? 'same rank' }} only)..."
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

<!-- Publish Deployment Confirmation Modal -->
@if($activeDraft)
    <div class="kt-modal" data-kt-modal="true" id="publish-deployment-modal">
        <div class="kt-modal-content max-w-[500px]">
            <div class="kt-modal-header py-4 px-5">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                        <i class="ki-filled ki-check text-primary text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Publish Deployment</h3>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body py-5 px-5">
                <p class="text-sm text-secondary-foreground mb-4">
                    Are you sure you want to publish this deployment? This will create movement orders and post officers to their new commands.
                </p>
                <div class="p-3 bg-muted/50 rounded-lg">
                    <div class="text-sm text-secondary-foreground">
                        <div><strong>Deployment:</strong> {{ $activeDraft->deployment_number }}</div>
                        @if(isset($manningRequest))
                            @php
                                $filteredCount = $assignmentsByCommand->sum(fn($group) => $group->count());
                            @endphp
                            <div><strong>Officers to Publish:</strong> {{ $filteredCount }} (from Request #{{ $manningRequest->id }})</div>
                            <div><strong>Total in Draft:</strong> {{ $activeDraft->assignments->count() }}</div>
                            <div><strong>Manning Request:</strong> #{{ $manningRequest->id }} - {{ $manningRequest->command->name ?? 'N/A' }}</div>
                        @else
                            <div><strong>Total Officers:</strong> {{ $activeDraft->assignments->count() }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                <button type="button" class="kt-btn kt-btn-primary" onclick="submitPublishForm()">
                    <i class="ki-filled ki-check"></i> Publish Deployment
                </button>
            </div>
        </div>
    </div>
@endif

<script>
// Prepare swap modal when opened
function prepareSwapModal(assignmentId, officerName, currentOfficerId, officerRank) {
    const searchInput = document.getElementById(`officer-search-${assignmentId}`);
    const resultsDiv = document.getElementById(`officer-search-results-${assignmentId}`);
    const newOfficerIdInput = document.getElementById(`new-officer-id-${assignmentId}`);
    const confirmBtn = document.getElementById(`confirm-swap-btn-${assignmentId}`);
    
    // Store the rank for this modal
    if (searchInput) {
        searchInput.dataset.officerRank = officerRank || '';
    }
    
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

// Submit publish form
function submitPublishForm() {
    const form = document.getElementById('publish-deployment-form');
    if (form) {
        form.submit();
    }
}

// Setup officer search for swap modals
@if($activeDraft)
    @foreach($activeDraft->assignments as $assignment)
        (function() {
            const assignmentId = {{ $assignment->id }};
            const searchInput = document.getElementById(`officer-search-${assignmentId}`);
            const resultsDiv = document.getElementById(`officer-search-results-${assignmentId}`);
            const officerRank = '{{ addslashes($assignment->officer->substantive_rank ?? '') }}';
let searchTimeout;
            
            if (searchInput) {
                // Store the rank in the input's dataset
                searchInput.dataset.officerRank = officerRank;
                
                searchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
                    const rank = searchInput.dataset.officerRank || '';
    
                    // If no query and no rank, hide results
                    if (query.length < 1 && !rank) {
                        if (resultsDiv) resultsDiv.classList.add('hidden');
        return;
    }
                    
                    // If we have a rank but no query yet, wait a bit before showing all officers of that rank
                    // This prevents showing too many results immediately
                    const delay = (query.length < 2 && rank) ? 500 : 300;
    
    searchTimeout = setTimeout(() => {
                        // Build search URL with rank filter
                        let searchUrl = `{{ route('hrd.officers.search') }}?`;
                        if (query.length >= 1) {
                            searchUrl += `q=${encodeURIComponent(query)}`;
                        }
                        if (rank) {
                            if (query.length >= 1) searchUrl += '&';
                            searchUrl += `rank=${encodeURIComponent(rank)}`;
                        }
                        
                        fetch(searchUrl)
            .then(response => response.json())
            .then(data => {
                                if (!resultsDiv) return;
                resultsDiv.innerHTML = '';
                if (data.length === 0) {
                                    const message = rank 
                                        ? `No officers found with rank ${rank}${query ? ' matching "' + query + '"' : ''}`
                                        : 'No officers found';
                                    resultsDiv.innerHTML = '<div class="p-4 text-sm text-secondary-foreground">' + message + '</div>';
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
                    }, delay);
                });
            }
        })();
    @endforeach
@endif
</script>
@endsection


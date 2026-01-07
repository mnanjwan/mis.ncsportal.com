@extends('layouts.app')

@section('title', 'Draft Deployment Management')
@section('page-title', 'Draft Deployment Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Draft Deployment</span>
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
            <a href="{{ route('hrd.manning-requests') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Requests
            </a>
            @if($activeDraft)
                <a href="{{ route('hrd.manning-deployments.print', $activeDraft->id) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary">
                    <i class="ki-filled ki-printer"></i> Print Preview
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
                        </div>
                    </div>
                    <form action="{{ route('hrd.manning-deployments.publish', $activeDraft->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to publish this deployment? This will create movement orders and post officers to their new commands.');">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check"></i> Publish Deployment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Manning Levels Summary -->
        @if(count($manningLevels) > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Manning Levels by Command</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($manningLevels as $commandId => $level)
                        <div class="p-4 rounded-lg bg-muted/50 border border-input">
                            <h4 class="font-semibold mb-2">{{ $level['command_name'] }}</h4>
                            <div class="text-sm text-secondary-foreground space-y-1">
                                <div>Total Officers: <span class="font-semibold">{{ count($level['officers']) }}</span></div>
                                @foreach($level['by_rank'] as $rank => $count)
                                    <div>{{ $rank }}: <span class="font-semibold">{{ $count }}</span></div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Search and Filter Bar -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-2">Search Officers</label>
                        <input type="text" 
                               id="officer-search-input" 
                               class="kt-input w-full" 
                               placeholder="Search by name, service number, rank, or command..."
                               autocomplete="off">
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium mb-2">Filter by Command</label>
                        <select id="command-filter" class="kt-input w-full">
                            <option value="">All Commands</option>
                            @foreach($assignmentsByCommand as $commandId => $assignments)
                                @php
                                    $command = $assignments->first()->toCommand;
                                @endphp
                                <option value="{{ $commandId }}">{{ $command->name ?? 'Unknown' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium mb-2">Filter by Rank</label>
                        <select id="rank-filter" class="kt-input w-full">
                            <option value="">All Ranks</option>
                            @php
                                $allRanks = $activeDraft->assignments->pluck('officer.substantive_rank')->filter()->unique()->sort()->values();
                            @endphp
                            @foreach($allRanks as $rank)
                                <option value="{{ $rank }}">{{ $rank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="button" 
                                onclick="clearFilters()" 
                                class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-cross"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Officer Button -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <button type="button" 
                        data-kt-modal-toggle="#add-officer-modal" 
                        class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Add Officer to Draft
                </button>
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

<!-- Add Officer Modal -->
<div class="kt-modal" data-kt-modal="true" id="add-officer-modal">
    <div class="kt-modal-content max-w-[700px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-plus text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Add Officer to Draft</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <form id="add-officer-form" method="POST" action="{{ route('hrd.manning-deployments.draft.add-officer') }}">
                @csrf
                <input type="hidden" name="deployment_id" value="{{ $activeDraft->id ?? '' }}">
                <p class="text-sm text-secondary-foreground mb-4">
                    Search for an officer and select their destination command to add them to the draft deployment.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Search Officer</label>
                    <input type="text" 
                           id="add-officer-search" 
                           class="kt-input w-full" 
                           placeholder="Search by name, service number, or rank..."
                           autocomplete="off">
                    <div id="add-officer-search-results" class="mt-2 max-h-60 overflow-y-auto border border-input rounded-lg hidden"></div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Destination Command</label>
                    <select name="to_command_id" id="to-command-select" class="kt-input w-full" required>
                        <option value="">Select command...</option>
                        @foreach(\App\Models\Command::where('is_active', true)->orderBy('name')->get() as $cmd)
                            <option value="{{ $cmd->id }}">{{ $cmd->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="add-officer-id" name="officer_id" required>
            </form>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" id="confirm-add-btn" disabled onclick="submitAddOfficer()">
                <i class="ki-filled ki-plus"></i> Add Officer
            </button>
        </div>
    </div>
</div>

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
@if($activeDraft)
    @foreach($activeDraft->assignments as $assignment)
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

// Dynamic Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('officer-search-input');
    const commandFilter = document.getElementById('command-filter');
    const rankFilter = document.getElementById('rank-filter');
    
    function filterOfficers() {
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        const selectedCommand = commandFilter?.value || '';
        const selectedRank = rankFilter?.value || '';
        
        const rows = document.querySelectorAll('.officer-row');
        const commandSections = document.querySelectorAll('.command-section');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const officerName = row.getAttribute('data-officer-name') || '';
            const serviceNumber = row.getAttribute('data-service-number') || '';
            const rank = row.getAttribute('data-rank') || '';
            const commandId = row.getAttribute('data-command-id') || '';
            const fromCommand = row.getAttribute('data-from-command') || '';
            
            // Search filter
            const matchesSearch = !searchTerm || 
                officerName.includes(searchTerm) ||
                serviceNumber.includes(searchTerm) ||
                rank.includes(searchTerm) ||
                fromCommand.includes(searchTerm);
            
            // Command filter
            const matchesCommand = !selectedCommand || commandId === selectedCommand;
            
            // Rank filter
            const matchesRank = !selectedRank || rank === selectedRank.toLowerCase();
            
            if (matchesSearch && matchesCommand && matchesRank) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Hide/show command sections based on visible rows
        commandSections.forEach(section => {
            const sectionCommandId = section.getAttribute('data-command-id');
            const sectionRows = section.querySelectorAll('.officer-row');
            const visibleRows = Array.from(sectionRows).filter(row => row.style.display !== 'none');
            
            if (visibleRows.length === 0 && selectedCommand && sectionCommandId !== selectedCommand) {
                section.style.display = 'none';
            } else {
                section.style.display = '';
            }
        });
        
        // Show/hide "no results" message
        const container = document.getElementById('assignments-container');
        if (container && visibleCount === 0 && rows.length > 0) {
            let noResultsMsg = container.querySelector('.no-results-message');
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'kt-card no-results-message';
                noResultsMsg.innerHTML = `
                    <div class="kt-card-content p-12 text-center">
                        <i class="ki-filled ki-search text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No officers match your search criteria.</p>
                    </div>
                `;
                container.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = '';
        } else {
            const noResultsMsg = container?.querySelector('.no-results-message');
            if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    }
    
    // Add event listeners
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterOfficers, 300); // Debounce for 300ms
        });
    }
    
    if (commandFilter) {
        commandFilter.addEventListener('change', filterOfficers);
    }
    
    if (rankFilter) {
        rankFilter.addEventListener('change', filterOfficers);
    }
    
    // Clear filters function
    window.clearFilters = function() {
        if (searchInput) searchInput.value = '';
        if (commandFilter) commandFilter.value = '';
        if (rankFilter) rankFilter.value = '';
        filterOfficers();
    };
    
    // Add Officer Modal - Setup search functionality
    const addOfficerSearchInput = document.getElementById('add-officer-search');
    const addOfficerSearchResults = document.getElementById('add-officer-search-results');
    let addOfficerSearchTimeout;
    
    if (addOfficerSearchInput) {
        addOfficerSearchInput.addEventListener('input', function(e) {
            clearTimeout(addOfficerSearchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                if (addOfficerSearchResults) addOfficerSearchResults.classList.add('hidden');
                return;
            }
            
            addOfficerSearchTimeout = setTimeout(() => {
                fetch(`{{ route('hrd.officers.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!addOfficerSearchResults) return;
                        addOfficerSearchResults.innerHTML = '';
                        if (data.length === 0) {
                            addOfficerSearchResults.innerHTML = '<div class="p-4 text-sm text-secondary-foreground">No officers found</div>';
                        } else {
                            data.forEach(officer => {
                                const div = document.createElement('div');
                                div.className = 'p-3 hover:bg-muted cursor-pointer border-b border-input last:border-0';
                                div.innerHTML = `
                                    <div class="font-semibold">${officer.initials} ${officer.surname}</div>
                                    <div class="text-xs text-secondary-foreground">${officer.service_number} - ${officer.substantive_rank} - ${officer.present_station_name || 'N/A'}</div>
                                `;
                                div.addEventListener('click', () => {
                                    const addOfficerIdInput = document.getElementById('add-officer-id');
                                    const confirmAddBtn = document.getElementById('confirm-add-btn');
                                    if (addOfficerIdInput) addOfficerIdInput.value = officer.id;
                                    if (addOfficerSearchInput) addOfficerSearchInput.value = `${officer.initials} ${officer.surname} (${officer.service_number})`;
                                    if (addOfficerSearchResults) addOfficerSearchResults.classList.add('hidden');
                                    if (confirmAddBtn) confirmAddBtn.disabled = false;
                                });
                                addOfficerSearchResults.appendChild(div);
                            });
                        }
                        addOfficerSearchResults.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                    });
            }, 300);
        });
    }
    
    // Reset add officer modal when closed
    const addOfficerModal = document.getElementById('add-officer-modal');
    if (addOfficerModal) {
        addOfficerModal.addEventListener('hidden', function() {
            if (addOfficerSearchInput) addOfficerSearchInput.value = '';
            const addOfficerIdInput = document.getElementById('add-officer-id');
            const confirmAddBtn = document.getElementById('confirm-add-btn');
            const toCommandSelect = document.getElementById('to-command-select');
            if (addOfficerIdInput) addOfficerIdInput.value = '';
            if (confirmAddBtn) confirmAddBtn.disabled = true;
            if (toCommandSelect) toCommandSelect.value = '';
            if (addOfficerSearchResults) addOfficerSearchResults.classList.add('hidden');
        });
    }
    
    // Submit add officer form
    window.submitAddOfficer = function() {
        const form = document.getElementById('add-officer-form');
        const officerId = document.getElementById('add-officer-id')?.value;
        const commandId = document.getElementById('to-command-select')?.value;
        if (form && officerId && commandId) {
            form.submit();
        }
    };
});
</script>
@endsection


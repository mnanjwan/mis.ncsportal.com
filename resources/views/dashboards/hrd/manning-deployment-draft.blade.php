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
                        onclick="openAddOfficerModal()" 
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
                                    @foreach($assignments as $assignment)
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
                                                            onclick="openSwapModal({{ $assignment->id }}, '{{ addslashes(($assignment->officer->initials ?? '') . ' ' . ($assignment->officer->surname ?? '')) }}', {{ $assignment->officer->id }})">
                                                        <i class="ki-filled ki-arrows-circle"></i> Swap
                                                    </button>
                                                    <form action="{{ route('hrd.manning-deployments.draft.remove-officer', ['deploymentId' => $activeDraft->id, 'assignmentId' => $assignment->id]) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Remove this officer from the deployment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-danger">
                                                            <i class="ki-filled ki-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
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

<!-- Swap Officer Modal -->
<div class="kt-modal" data-kt-modal="true" id="swap-officer-modal">
    <div class="kt-modal-content max-w-[600px]">
        <div class="kt-modal-header py-4 px-5">
            <h3 class="text-lg font-semibold text-foreground">Swap Officer</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <form id="swap-form" method="POST">
                @csrf
                <input type="hidden" id="swap-assignment-id" name="assignment_id">
                <p class="text-sm text-secondary-foreground mb-4">
                    Select a new officer to replace <span id="current-officer-name" class="font-semibold"></span>:
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Search Officer</label>
                    <input type="text" 
                           id="officer-search" 
                           class="kt-input w-full" 
                           placeholder="Search by name, service number, or rank..."
                           autocomplete="off">
                    <div id="officer-search-results" class="mt-2 max-h-60 overflow-y-auto border border-input rounded-lg hidden"></div>
                </div>
                <input type="hidden" id="new-officer-id" name="new_officer_id" required>
            </form>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" id="confirm-swap-btn" disabled>
                <i class="ki-filled ki-arrows-circle"></i> Swap Officer
            </button>
        </div>
    </div>
</div>

<script>
function openSwapModal(assignmentId, officerName, currentOfficerId) {
    document.getElementById('swap-assignment-id').value = assignmentId;
    document.getElementById('current-officer-name').textContent = officerName;
    document.getElementById('new-officer-id').value = '';
    document.getElementById('confirm-swap-btn').disabled = true;
    document.getElementById('officer-search').value = '';
    document.getElementById('officer-search-results').classList.add('hidden');
    
    const form = document.getElementById('swap-form');
    const deploymentId = {{ $activeDraft->id ?? 0 }};
    form.action = `/hrd/manning-deployments/${deploymentId}/swap-officer/${assignmentId}`;
    
    // Show modal
    document.getElementById('swap-officer-modal').classList.add('show');
}

// Officer search functionality
let searchTimeout;
document.getElementById('officer-search')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    const resultsDiv = document.getElementById('officer-search-results');
    
    if (query.length < 2) {
        resultsDiv.classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`{{ route('hrd.officers.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
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
                            document.getElementById('new-officer-id').value = officer.id;
                            document.getElementById('officer-search').value = `${officer.initials} ${officer.surname} (${officer.service_number})`;
                            resultsDiv.classList.add('hidden');
                            document.getElementById('confirm-swap-btn').disabled = false;
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

// Confirm swap
document.getElementById('confirm-swap-btn')?.addEventListener('click', function() {
    const form = document.getElementById('swap-form');
    if (document.getElementById('new-officer-id').value) {
        form.submit();
    }
});

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
    
    // Add Officer Modal function
    window.openAddOfficerModal = function() {
        // Create a modal for adding officers
        const modal = document.createElement('div');
        modal.className = 'kt-modal show';
        modal.id = 'add-officer-modal';
        modal.innerHTML = `
            <div class="kt-modal-content max-w-[700px]">
                <div class="kt-modal-header py-4 px-5">
                    <h3 class="text-lg font-semibold text-foreground">Add Officer to Draft</h3>
                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" onclick="closeAddOfficerModal()">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body py-5 px-5">
                    <form id="add-officer-form" method="POST" action="{{ route('hrd.manning-deployments.draft.add-officer') }}">
                        @csrf
                        <input type="hidden" name="deployment_id" value="{{ $activeDraft->id ?? '' }}">
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
                    <button class="kt-btn kt-btn-secondary" onclick="closeAddOfficerModal()">Cancel</button>
                    <button type="button" class="kt-btn kt-btn-primary" id="confirm-add-btn" disabled onclick="submitAddOfficer()">
                        <i class="ki-filled ki-plus"></i> Add Officer
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Setup search functionality
        let addSearchTimeout;
        const addSearchInput = document.getElementById('add-officer-search');
        const addSearchResults = document.getElementById('add-officer-search-results');
        
        addSearchInput.addEventListener('input', function(e) {
            clearTimeout(addSearchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                addSearchResults.classList.add('hidden');
                return;
            }
            
            addSearchTimeout = setTimeout(() => {
                fetch(`{{ route('hrd.officers.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        addSearchResults.innerHTML = '';
                        if (data.length === 0) {
                            addSearchResults.innerHTML = '<div class="p-4 text-sm text-secondary-foreground">No officers found</div>';
                        } else {
                            data.forEach(officer => {
                                const div = document.createElement('div');
                                div.className = 'p-3 hover:bg-muted cursor-pointer border-b border-input last:border-0';
                                div.innerHTML = `
                                    <div class="font-semibold">${officer.initials} ${officer.surname}</div>
                                    <div class="text-xs text-secondary-foreground">${officer.service_number} - ${officer.substantive_rank} - ${officer.present_station_name || 'N/A'}</div>
                                `;
                                div.addEventListener('click', () => {
                                    document.getElementById('add-officer-id').value = officer.id;
                                    addSearchInput.value = `${officer.initials} ${officer.surname} (${officer.service_number})`;
                                    addSearchResults.classList.add('hidden');
                                    document.getElementById('confirm-add-btn').disabled = false;
                                });
                                addSearchResults.appendChild(div);
                            });
                        }
                        addSearchResults.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                    });
            }, 300);
        });
    };
    
    window.closeAddOfficerModal = function() {
        const modal = document.getElementById('add-officer-modal');
        if (modal) {
            modal.remove();
        }
    };
    
    window.submitAddOfficer = function() {
        const form = document.getElementById('add-officer-form');
        if (form && document.getElementById('add-officer-id').value && document.getElementById('to-command-select').value) {
            form.submit();
        }
    };
});
</script>
@endsection


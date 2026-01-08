@extends('layouts.app')

@section('title', 'Command Duration Draft')
@section('page-title', 'Command Duration Draft')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.command-duration.index') }}">Command Duration</a>
    <span>/</span>
    <span class="text-primary">Draft Deployment</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-mono">Command Duration Draft Deployment</h2>
            <p class="text-sm text-secondary-foreground mt-1">Review and assign destination commands before publishing</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('hrd.command-duration.index') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Search
            </a>
        </div>
    </div>

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

    @if($activeDraft)
        <!-- Draft Info Card -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">Deployment Number:</span>
                            <span class="text-sm text-secondary-foreground">{{ $activeDraft->deployment_number }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">Status:</span>
                            <span class="kt-badge kt-badge-info kt-badge-sm">DRAFT</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">Total Officers:</span>
                            <span class="text-sm text-secondary-foreground">{{ $activeDraft->assignments()->whereNull('manning_request_id')->count() }}</span>
                        </div>
                    </div>
                    <form id="publish-deployment-form" action="{{ route('hrd.command-duration.publish', $activeDraft->id) }}" method="POST">
                        @csrf
                        @csrf
                        <button type="button" class="kt-btn kt-btn-primary" onclick="submitPublishForm()">
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
                    $command = $assignments->first()->toCommand ?? null;
                    $commandName = $command ? $command->name : 'Unassigned';
                @endphp
                <div class="kt-card command-section" data-command-id="{{ $commandId ?? 'unassigned' }}">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">{{ $commandName }}</h3>
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
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
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
                                            <tr>
                                                <td colspan="6" class="py-2 px-4 bg-primary/5 border-b border-primary/20">
                                                    <span class="text-xs font-semibold text-primary">{{ $rank }}</span>
                                                </td>
                                            </tr>
                                        @else
                                            <tr class="border-t-2 border-primary/30">
                                                <td colspan="6" class="py-2 px-4 bg-primary/5">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-1 border-t border-primary/20"></div>
                                                        <span class="text-xs font-semibold text-primary px-2">{{ $rank }}</span>
                                                        <div class="flex-1 border-t border-primary/20"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @foreach($rankAssignments as $assignment)
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
                                            <td class="py-3 px-4">
                                                <div class="command-select-wrapper" data-assignment-id="{{ $assignment->id }}">
                                                    <div class="relative" style="position: relative;">
                                                        <input type="text" 
                                                               class="kt-input kt-input-sm command-search-input {{ $assignment->to_command_id == $assignment->from_command_id ? 'border-warning' : '' }}" 
                                                               placeholder="Type to search command..."
                                                               autocomplete="off"
                                                               data-assignment-id="{{ $assignment->id }}"
                                                               value="{{ $assignment->toCommand ? ($assignment->toCommand->name . ($assignment->toCommand->code ? ' (' . $assignment->toCommand->code . ')' : '')) : '' }}">
                                                        <input type="hidden" 
                                                               class="command-hidden-input" 
                                                               name="to_command_id"
                                                               value="{{ $assignment->to_command_id ?? '' }}"
                                                               data-assignment-id="{{ $assignment->id }}">
                                                        <div class="command-dropdown" 
                                                             style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); max-height: 240px; overflow-y: auto; display: none;" 
                                                             data-assignment-id="{{ $assignment->id }}"></div>
                                                    </div>
                                                    @if($assignment->to_command_id == $assignment->from_command_id)
                                                        <span class="kt-badge kt-badge-warning kt-badge-sm mt-1 inline-block" title="Please select destination command (currently same as from command)">
                                                            <i class="ki-filled ki-information"></i> Select Destination
                                                        </span>
                                                    @endif
                                                </div>
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
                    <p class="text-secondary-foreground">No officers in Command Duration draft deployment yet.</p>
                    <a href="{{ route('hrd.command-duration.index') }}" class="kt-btn kt-btn-primary mt-4">
                        <i class="ki-filled ki-search"></i> Search Officers
                    </a>
                </div>
            </div>
        @endif
        </div>
    @else
        <div class="kt-card">
            <div class="kt-card-content p-12 text-center">
                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground mb-4">No active Command Duration draft deployment. Add officers from Command Duration search to create one.</p>
                <a href="{{ route('hrd.command-duration.index') }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-search"></i> Search Officers
                </a>
            </div>
        </div>
    @endif
</div>

<script>
// Commands data (same format as Assign Role page)
@php
    $commandsData = $commands->map(function($command) {
        return [
            'id' => $command->id,
            'name' => $command->name,
            'code' => $command->code ?? ''
        ];
    })->values();
@endphp
const commandsCache = @json($commandsData);

// Searchable Select Helper Function (same as Assign Role page)
function createSearchableSelect(searchInput, hiddenInput, dropdown, selectedDiv, selectedName, options, onSelect, displayFn) {
    let selectedOption = null;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            dropdown.style.display = 'none';
            return;
        }
        
        const filtered = options.filter(opt => {
            if (displayFn) {
                return displayFn(opt).toLowerCase().includes(searchTerm);
            }
            const nameMatch = opt.name && opt.name.toLowerCase().includes(searchTerm);
            const codeMatch = opt.code && opt.code.toLowerCase().includes(searchTerm);
            return nameMatch || codeMatch;
        });

        if (filtered.length > 0) {
            dropdown.innerHTML = filtered.map(opt => {
                const display = displayFn ? displayFn(opt) : (opt.name + (opt.code ? ' (' + opt.code + ')' : ''));
                const escapedName = (opt.name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                return '<div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-0" ' +
                            'data-id="' + opt.id + '" ' +
                            'data-name="' + escapedName + '">' +
                            '<div class="font-medium text-sm">' + display + '</div>' +
                        '</div>';
            }).join('');
            dropdown.style.display = 'block';
        } else {
            dropdown.innerHTML = '<div class="p-3 text-gray-500 text-sm">No results found</div>';
            dropdown.style.display = 'block';
        }
    });
    
    // Show dropdown on focus if there's a search term
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    dropdown.addEventListener('click', function(e) {
        const option = e.target.closest('[data-id]');
        if (option) {
            selectedOption = options.find(o => o.id == option.dataset.id);
            if (selectedOption) {
                hiddenInput.value = selectedOption.id;
                const display = displayFn ? displayFn(selectedOption) : (selectedOption.name + (selectedOption.code ? ' (' + selectedOption.code + ')' : ''));
                searchInput.value = display;
                if (selectedName && selectedDiv) {
                    selectedDiv.querySelector(selectedName).textContent = display;
                }
                if (selectedDiv) {
                    selectedDiv.classList.remove('hidden');
                }
                dropdown.style.display = 'none';
                if (onSelect) onSelect(selectedOption);
            }
        }
    });

    // Clear selection
    const clearBtn = selectedDiv ? selectedDiv.querySelector('button') : null;
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            hiddenInput.value = '';
            searchInput.value = '';
            if (selectedDiv) selectedDiv.classList.add('hidden');
            selectedOption = null;
            if (onSelect) onSelect(null);
        });
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Initialize searchable selects for all command dropdowns
function initializeCommandSelects() {
    document.querySelectorAll('.command-select-wrapper').forEach(wrapper => {
        const assignmentId = wrapper.dataset.assignmentId;
        const searchInput = wrapper.querySelector('.command-search-input[data-assignment-id="' + assignmentId + '"]');
        const hiddenInput = wrapper.querySelector('.command-hidden-input[data-assignment-id="' + assignmentId + '"]');
        const dropdown = wrapper.querySelector('.command-dropdown[data-assignment-id="' + assignmentId + '"]');
        const selectedDiv = wrapper.querySelector('.selected-command-display[data-assignment-id="' + assignmentId + '"]');
        const selectedName = selectedDiv ? selectedDiv.querySelector('.selected-command-name') : null;
        
        if (!searchInput || !hiddenInput || !dropdown) return;
        
        // Initialize with current value if exists
        const currentValue = hiddenInput.value;
        if (currentValue) {
            const currentCommand = commandsCache.find(c => c.id == currentValue);
            if (currentCommand) {
                const display = currentCommand.name + (currentCommand.code ? ' (' + currentCommand.code + ')' : '');
                searchInput.value = display;
                if (selectedDiv && selectedName) {
                    selectedName.textContent = display;
                    selectedDiv.classList.remove('hidden');
                }
            }
        }
        
        createSearchableSelect(
            searchInput,
            hiddenInput,
            dropdown,
            selectedDiv,
            '.selected-command-name',
            commandsCache,
            function(selectedCommand) {
                if (selectedCommand) {
                    updateDestinationCommand(assignmentId, selectedCommand.id);
                } else {
                    updateDestinationCommand(assignmentId, '');
                }
            },
            function(cmd) {
                return cmd.name + (cmd.code ? ' (' + cmd.code + ')' : '');
            }
        );
    });
}

function updateDestinationCommand(assignmentId, commandId) {
    // Don't update if commandId is empty (clearing selection)
    if (!commandId) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('hrd.command-duration.draft.update-destination', ['deploymentId' => $activeDraft->id ?? 0, 'assignmentId' => 'ASSIGNMENT_ID']) }}`.replace('ASSIGNMENT_ID', assignmentId);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';
    form.appendChild(methodInput);
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    const commandInput = document.createElement('input');
    commandInput.type = 'hidden';
    commandInput.name = 'to_command_id';
    commandInput.value = commandId;
    form.appendChild(commandInput);
    
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json().catch(() => ({ success: true }));
        }
        throw new Error('Update failed');
    })
    .then(data => {
        if (data.success !== false) {
            console.log('Destination command updated successfully');
            // Reload page to show updated grouping by command
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update destination command. Please try again.');
        // Reload to reset state
        window.location.reload();
    });
}

function submitPublishForm() {
    document.getElementById('publish-deployment-form').submit();
}

function removeOfficer(assignmentId) {
    const form = document.getElementById(`remove-form-${assignmentId}`);
    if (form) {
        form.submit();
    }
}

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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCommandSelects();
    
    // Setup officer search for swap modals
    @if($activeDraft)
        @foreach($activeDraft->assignments()->whereNull('manning_request_id')->get() as $assignment)
            (function() {
                const assignmentId = {{ $assignment->id }};
                const searchInput = document.getElementById(`officer-search-${assignmentId}`);
                const resultsDiv = document.getElementById(`officer-search-results-${assignmentId}`);
                const officerRank = '{{ addslashes($assignment->officer->substantive_rank ?? '') }}';
                let searchTimeout;
                
                if (searchInput) {
                    searchInput.dataset.officerRank = officerRank;
                    
                    searchInput.addEventListener('input', function(e) {
                        clearTimeout(searchTimeout);
                        const query = e.target.value.trim();
                        const rank = searchInput.dataset.officerRank || '';
                        
                        if (query.length < 1 && !rank) {
                            if (resultsDiv) resultsDiv.classList.add('hidden');
                            return;
                        }
                        
                        const delay = (query.length < 2 && rank) ? 500 : 300;
                        
                        searchTimeout = setTimeout(() => {
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
});
</script>

<!-- Swap Officer Modals -->
@if($activeDraft)
    @foreach($activeDraft->assignments()->whereNull('manning_request_id')->get() as $assignment)
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
                    <form id="swap-form-{{ $assignment->id }}" method="POST" action="{{ route('hrd.command-duration.draft.swap-officer', ['deploymentId' => $activeDraft->id, 'assignmentId' => $assignment->id]) }}">
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
                    <form id="remove-form-{{ $assignment->id }}" method="POST" action="{{ route('hrd.command-duration.draft.remove-officer', ['deploymentId' => $activeDraft->id, 'assignmentId' => $assignment->id]) }}">
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
@endsection


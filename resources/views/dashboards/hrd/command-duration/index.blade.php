@extends('layouts.app')

@section('title', 'Command Duration')
@section('page-title', 'Command Duration')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Command Duration</span>
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

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <div class="text-sm text-danger font-medium">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Search Filters Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Search Officers by Command Duration</h3>
        </div>
        <div class="kt-card-content">
            <form method="POST" action="{{ route('hrd.command-duration.search') }}" id="search-form" class="flex flex-col gap-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Zone (Required) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Zone <span class="text-danger">*</span>
                        </label>
                        <select name="zone_id" id="zone_id" class="kt-input w-full" required onchange="loadCommands()">
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ (isset($selected_zone_id) && $selected_zone_id == $zone->id) ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Command (Required) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">
                            Command <span class="text-danger">*</span>
                        </label>
                        <select name="command_id" id="command_id" class="kt-input w-full" required {{ !isset($selected_zone_id) ? 'disabled' : '' }}>
                            <option value="">Select Command</option>
                            @foreach($commands as $command)
                                <option value="{{ $command->id }}" {{ (isset($selected_command_id) && $selected_command_id == $command->id) ? 'selected' : '' }}>
                                    {{ $command->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rank (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Rank</label>
                        <select name="rank" class="kt-input w-full">
                            <option value="">All Ranks</option>
                            @foreach($ranks as $rank)
                                <option value="{{ $rank }}" {{ (isset($selected_rank) && $selected_rank == $rank) ? 'selected' : '' }}>
                                    {{ $rank }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sex (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Sex</label>
                        <select name="sex" class="kt-input w-full">
                            <option value="Any" {{ (!isset($selected_sex) || $selected_sex == 'Any') ? 'selected' : '' }}>Any</option>
                            <option value="Male" {{ (isset($selected_sex) && $selected_sex == 'Male') ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ (isset($selected_sex) && $selected_sex == 'Female') ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <!-- Command Duration (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Duration in Command</label>
                        <select name="duration_years" class="kt-input w-full">
                            <option value="">All Durations</option>
                            <option value="0" {{ (isset($selected_duration) && $selected_duration == '0') ? 'selected' : '' }}>0 Years</option>
                            <option value="1" {{ (isset($selected_duration) && $selected_duration == '1') ? 'selected' : '' }}>1 Year</option>
                            <option value="2" {{ (isset($selected_duration) && $selected_duration == '2') ? 'selected' : '' }}>2 Years</option>
                            <option value="3" {{ (isset($selected_duration) && $selected_duration == '3') ? 'selected' : '' }}>3 Years</option>
                            <option value="4" {{ (isset($selected_duration) && $selected_duration == '4') ? 'selected' : '' }}>4 Years</option>
                            <option value="5" {{ (isset($selected_duration) && $selected_duration == '5') ? 'selected' : '' }}>5 Years</option>
                            <option value="6" {{ (isset($selected_duration) && $selected_duration == '6') ? 'selected' : '' }}>6 Years</option>
                            <option value="7" {{ (isset($selected_duration) && $selected_duration == '7') ? 'selected' : '' }}>7 Years</option>
                            <option value="8" {{ (isset($selected_duration) && $selected_duration == '8') ? 'selected' : '' }}>8 Years</option>
                            <option value="9" {{ (isset($selected_duration) && $selected_duration == '9') ? 'selected' : '' }}>9 Years</option>
                            <option value="10" {{ (isset($selected_duration) && $selected_duration == '10') ? 'selected' : '' }}>10+ Years</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="resetForm()" class="kt-btn kt-btn-secondary">
                        <i class="ki-filled ki-cross"></i> Reset
                    </button>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if(isset($officers))
        <div class="kt-card">
            <div class="kt-card-header">
                <div class="flex items-center justify-between">
                    <h3 class="kt-card-title">Search Results ({{ $officers->count() }} officer(s))</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="print-btn" onclick="printResults()" class="kt-btn kt-btn-sm kt-btn-secondary">
                            <i class="ki-filled ki-printer"></i> Print Results
                        </button>
                        <button type="button" id="add-to-draft-btn" data-kt-modal-toggle="#add-to-draft-modal" class="kt-btn kt-btn-sm kt-btn-primary hidden" onclick="prepareAddToDraftModal()">
                            <i class="ki-filled ki-file-add"></i> Add Selected to Draft
                        </button>
                    </div>
                </div>
            </div>
            <div class="kt-card-content">
                @if($officers->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground w-12">
                                        <input type="checkbox" id="select-all" onchange="toggleAll(this)">
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Full Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Date Posted to Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Duration in Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($officers as $officer)
                                    @php
                                        $isDisabled = !$officer->is_eligible_for_movement;
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors {{ $isDisabled ? 'opacity-60' : '' }}">
                                        <td class="py-3 px-4">
                                            <input type="checkbox" 
                                                   class="officer-checkbox" 
                                                   value="{{ $officer->id }}"
                                                   {{ $isDisabled ? 'disabled' : '' }}
                                                   onchange="updateAddButton()">
                                        </td>
                                        <td class="py-3 px-4 text-sm text-foreground">{{ $officer->service_number }}</td>
                                        <td class="py-3 px-4 text-sm text-foreground">{{ $officer->full_name }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->substantive_rank }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->presentStation->name ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->date_posted_to_command ? $officer->date_posted_to_command->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $officer->duration_display }}</td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                @if($officer->current_status === 'Active')
                                                    <span class="kt-badge kt-badge-success kt-badge-sm">{{ $officer->current_status }}</span>
                                                @elseif($officer->current_status === 'Under Investigation')
                                                    <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $officer->current_status }}</span>
                                                @else
                                                    <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $officer->current_status }}</span>
                                                @endif
                                                @if($officer->is_in_draft)
                                                    <span class="kt-badge kt-badge-info kt-badge-sm" title="Officer already in draft deployment">
                                                        <i class="ki-filled ki-file-add"></i> In Draft
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4">
                            @foreach($officers as $officer)
                                @php
                                    $isDisabled = !$officer->is_eligible_for_movement;
                                @endphp
                                <div class="p-4 rounded-lg bg-muted/50 border border-input {{ $isDisabled ? 'opacity-60' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" 
                                               class="officer-checkbox mt-1" 
                                               value="{{ $officer->id }}"
                                               {{ $isDisabled ? 'disabled' : '' }}
                                               onchange="updateAddButton()">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-semibold text-foreground">{{ $officer->full_name }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if($officer->current_status === 'Active')
                                                        <span class="kt-badge kt-badge-success kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @elseif($officer->current_status === 'Under Investigation')
                                                        <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @else
                                                        <span class="kt-badge kt-badge-danger kt-badge-sm">{{ $officer->current_status }}</span>
                                                    @endif
                                                    @if($officer->is_in_draft)
                                                        <span class="kt-badge kt-badge-info kt-badge-sm">
                                                            <i class="ki-filled ki-file-add"></i> In Draft
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground">
                                                <div>Service #: <span class="font-semibold">{{ $officer->service_number }}</span></div>
                                                <div>Rank: <span class="font-semibold">{{ $officer->substantive_rank }}</span></div>
                                                <div>Command: <span class="font-semibold">{{ $officer->presentStation->name ?? 'N/A' }}</span></div>
                                                <div>Duration: <span class="font-semibold">{{ $officer->duration_display }}</span></div>
                                                <div class="col-span-2">Posted: <span class="font-semibold">{{ $officer->date_posted_to_command ? $officer->date_posted_to_command->format('d/m/Y') : 'N/A' }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-search text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-2">No officers found matching your criteria</p>
                        <p class="text-xs text-secondary-foreground">Try adjusting your filters</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Add to Draft Form (Hidden) -->
<form id="add-to-draft-form" method="POST" action="{{ route('hrd.command-duration.add-to-draft') }}" style="display: none;">
    @csrf
    <input type="hidden" name="command_id" id="draft-command-id" value="{{ $selected_command_id ?? (request('command_id') ?? '') }}">
    <input type="hidden" name="officer_ids" id="draft-officer-ids">
</form>

<!-- Add to Draft Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="add-to-draft-modal">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-file-add text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Add Officers to Draft</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground mb-4">
                Add <strong id="selected-officers-count">0</strong> officer(s) to draft deployment?
            </p>
            <div class="p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-xs text-info">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Note:</strong> Officers will be added to the draft deployment. You can review, remove, or swap officers in the draft before publishing.
                </p>
            </div>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" onclick="submitAddToDraft()">
                <i class="ki-filled ki-file-add"></i> Add to Draft
            </button>
        </div>
    </div>
</div>

<script>
function loadCommands() {
    const zoneId = document.getElementById('zone_id').value;
    const commandSelect = document.getElementById('command_id');
    
    if (!zoneId) {
        commandSelect.disabled = true;
        commandSelect.innerHTML = '<option value="">Select Command</option>';
        return;
    }
    
    // Load commands via AJAX
    fetch(`{{ route('hrd.command-duration.index') }}?zone_id=${zoneId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        commandSelect.innerHTML = '<option value="">Select Command</option>';
        if (data.commands && data.commands.length > 0) {
            data.commands.forEach(cmd => {
                const option = document.createElement('option');
                option.value = cmd.id;
                option.textContent = cmd.name;
                commandSelect.appendChild(option);
            });
        }
        commandSelect.disabled = false;
    })
    .catch(error => {
        console.error('Error loading commands:', error);
        // Fallback: reload page
        window.location.href = `{{ route('hrd.command-duration.index') }}?zone_id=${zoneId}`;
    });
}

function resetForm() {
    document.getElementById('search-form').reset();
    document.getElementById('command_id').disabled = true;
    document.getElementById('command_id').innerHTML = '<option value="">Select Command</option>';
}

function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.officer-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateAddButton();
}

function updateAddButton() {
    const checkboxes = document.querySelectorAll('.officer-checkbox:checked:not(:disabled)');
    const addBtn = document.getElementById('add-to-draft-btn');
    
    if (checkboxes.length > 0) {
        addBtn.classList.remove('hidden');
    } else {
        addBtn.classList.add('hidden');
    }
}

function prepareAddToDraftModal() {
    const checkboxes = document.querySelectorAll('.officer-checkbox:checked:not(:disabled)');
    const officerIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (officerIds.length === 0) {
        alert('Please select at least one eligible officer to add to draft.');
        // Hide modal if no officers selected
        const modal = document.getElementById('add-to-draft-modal');
        if (modal) {
            modal.style.display = 'none';
        }
        return;
    }
    
    // Ensure command_id is set
    const commandId = document.getElementById('command_id').value;
    if (!commandId) {
        alert('Please select a command first.');
        return;
    }
    
    // Update modal content with count
    const countElement = document.getElementById('selected-officers-count');
    if (countElement) {
        countElement.textContent = officerIds.length;
    }
    
    // Store officer IDs and command ID for form submission
    document.getElementById('draft-command-id').value = commandId;
    document.getElementById('draft-officer-ids').value = JSON.stringify(officerIds);
}

function submitAddToDraft() {
    document.getElementById('add-to-draft-form').submit();
}

function printResults() {
    // Get all form values
    const form = document.getElementById('search-form');
    const formData = new FormData(form);
    
    // Build query string
    const params = new URLSearchParams();
    params.append('zone_id', formData.get('zone_id'));
    params.append('command_id', formData.get('command_id'));
    if (formData.get('rank')) params.append('rank', formData.get('rank'));
    if (formData.get('sex') && formData.get('sex') !== 'Any') params.append('sex', formData.get('sex'));
    if (formData.get('duration_years')) params.append('duration_years', formData.get('duration_years'));
    
    // Open print page
    window.open(`{{ route('hrd.command-duration.print') }}?${params.toString()}`, '_blank');
}
</script>
@endsection


@extends('layouts.app')

@section('title', 'Find Matches')
@section('page-title', 'Find Matches for Manning Request')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests') }}">Manning Requests</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}">Request Details</a>
    <span>/</span>
    <span class="text-primary">Find Matches</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Request Details
        </a>
    </div>

    <!-- Requirement Details -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="mb-4 p-3 bg-primary/10 border border-primary/20 rounded-lg">
                <p class="text-sm text-primary font-medium">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Global Matching:</strong> Officers are searched from ALL commands EXCEPT the requesting command ({{ $manningRequest->command->name ?? 'N/A' }}). Officers from the requesting command are excluded from results. The requesting command only states their needs - HRD matches from other commands across the commission.
                </p>
            </div>
            <h3 class="text-lg font-semibold mb-4">Matching Criteria</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-secondary-foreground">Rank:</span>
                    <span class="font-semibold text-mono ml-2">{{ $item->rank }}</span>
                    <span class="text-xs text-secondary-foreground ml-2">(Exact match required)</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Quantity Needed:</span>
                    <span class="font-semibold text-mono ml-2">{{ $item->quantity_needed }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Sex Requirement:</span>
                    <span class="font-semibold text-mono ml-2">{{ $item->sex_requirement }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Qualification Preference:</span>
                    <span class="font-semibold text-mono ml-2">{{ $item->qualification_requirement ?? 'Any' }}</span>
                    @if($item->qualification_requirement)
                        <span class="text-xs text-secondary-foreground ml-2">(shown but not filtered - all officers with rank are displayed)</span>
                    @endif
                </div>
                <div class="col-span-2">
                    <span class="text-secondary-foreground">Search Scope:</span>
                    <span class="font-semibold text-mono ml-2">All Commands (Excluding Requesting Command)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Matched Officers -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">
                Matched Officers 
                <span class="text-sm font-normal text-secondary-foreground">
                    ({{ $matchedOfficers->count() }} shown of {{ $totalCount ?? $matchedOfficers->count() }} total from all commands)
                </span>
            </h3>
        </div>
        <div class="kt-card-content">
            @if($matchedOfficers->count() > 0)
                <form action="{{ route('hrd.manning-requests.add-to-draft', $manningRequest->id) }}" method="POST" id="matchForm">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    
                    <p class="text-sm text-secondary-foreground mb-4">
                        Select {{ $item->quantity_needed }} officer(s) to add to draft deployment. Officers will be added to the draft where you can review, adjust, and publish when ready.
                    </p>

                    <!-- Desktop Table View -->
                    <div class="hidden lg:block">
                        <div class="overflow-x-auto">
                            <table class="kt-table w-full">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                            <input type="checkbox" id="selectAll" class="kt-checkbox">
                                        </th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Command</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Zone</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Sex</th>
                                        @if(isset($qualificationRequirement) && $qualificationRequirement)
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Qualification Match</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($matchedOfficers as $officer)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" 
                                                       name="selected_officers[]" 
                                                       value="{{ $officer->id }}"
                                                       class="kt-checkbox officer-checkbox"
                                                       data-max="{{ $item->quantity_needed }}">
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-sm font-medium text-foreground">
                                                    {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->substantive_rank ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->presentStation->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->presentStation->zone->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $officer->sex ?? 'N/A' }}
                                            </td>
                                            @if(isset($qualificationRequirement) && $qualificationRequirement)
                                            <td class="py-3 px-4 text-sm">
                                                @if(isset($officer->qualification_matches) && $officer->qualification_matches)
                                                    <span class="kt-badge kt-badge-success kt-badge-sm">Matches</span>
                                                @else
                                                    <span class="text-secondary-foreground">—</span>
                                                @endif
                                            </td>
                                            @endif
                                            @if(isset($qualificationRequirement) && $qualificationRequirement)
                                            <td class="py-3 px-4 text-sm">
                                                @if(isset($officer->qualification_matches) && $officer->qualification_matches)
                                                    <span class="kt-badge kt-badge-success kt-badge-sm">Matches</span>
                                                @else
                                                    <span class="text-secondary-foreground">—</span>
                                                @endif
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4">
                            @foreach($matchedOfficers as $officer)
                                <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" 
                                               name="selected_officers[]" 
                                               value="{{ $officer->id }}"
                                               class="kt-checkbox officer-checkbox mt-1"
                                               data-max="{{ $item->quantity_needed }}">
                                        <div class="flex-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $officer->initials ?? '' }} {{ $officer->surname ?? '' }}
                                            </span>
                                            <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground mt-2">
                                                <div>SVC: <span class="font-mono">{{ $officer->service_number ?? 'N/A' }}</span></div>
                                                <div>Rank: <span class="font-semibold">{{ $officer->substantive_rank ?? 'N/A' }}</span></div>
                                                <div>Command: <span class="font-semibold">{{ $officer->presentStation->name ?? 'N/A' }}</span></div>
                                                <div>Zone: <span class="font-semibold">{{ $officer->presentStation->zone->name ?? 'N/A' }}</span></div>
                                                <div>Sex: <span class="font-semibold">{{ $officer->sex ?? 'N/A' }}</span></div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 mt-6 border-t border-border">
                        <a href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}" class="kt-btn kt-btn-ghost">
                            Cancel
                        </a>
                        <button type="button" class="kt-btn kt-btn-primary" id="generateBtn" disabled data-kt-modal-toggle="#add-to-draft-confirm-modal">
                            <i class="ki-filled ki-file-add"></i> Add to Draft
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-search text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground mb-2 font-semibold">No matching officers found</p>
                    <p class="text-xs text-secondary-foreground mb-4">
                        No officers match the specified criteria:
                    </p>
                    <div class="text-left max-w-md mx-auto bg-muted/50 p-4 rounded-lg">
                        <p class="text-xs text-secondary-foreground mb-2"><strong>Requirements:</strong></p>
                        <ul class="text-xs text-secondary-foreground space-y-1 list-disc list-inside">
                            <li>Rank: <strong>{{ $item->rank }}</strong></li>
                            <li>Sex: <strong>{{ $item->sex_requirement }}</strong></li>
                            @if($item->qualification_requirement)
                            <li>Qualification: <strong>{{ $item->qualification_requirement }}</strong></li>
                            @endif
                            <li>Active and not interdicted/suspended/dismissed/deceased</li>
                            <li>Must have a current command (present_station)</li>
                        </ul>
                    </div>
                    <p class="text-xs text-secondary-foreground mt-4">
                        <strong>Tip:</strong> Check if there are officers with the rank "{{ $item->rank }}" in the system, or if the qualification requirement is too restrictive.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($matchedOfficers->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maxSelections = {{ $item->quantity_needed }};
    const checkboxes = document.querySelectorAll('.officer-checkbox');
    const selectAll = document.getElementById('selectAll');
    const generateBtn = document.getElementById('generateBtn');
    const form = document.getElementById('matchForm');

    // Handle individual checkbox selection
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selected = document.querySelectorAll('.officer-checkbox:checked').length;
            console.log('Selected officers:', selected, 'Max:', maxSelections);
            
            // Enable/disable generate button
            if (selected > 0 && selected <= maxSelections) {
                generateBtn.disabled = false;
                generateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                generateBtn.disabled = true;
                generateBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            
            // Update select all checkbox
            if (selectAll) {
                selectAll.checked = selected === checkboxes.length;
                selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
            }
            
            // Disable remaining checkboxes if max reached
            if (selected >= maxSelections) {
                checkboxes.forEach(cb => {
                    if (!cb.checked) {
                        cb.disabled = true;
                    }
                });
            } else {
                checkboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }
        });
    });

    // Handle select all
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const limit = Math.min(maxSelections, checkboxes.length);
            checkboxes.forEach((cb, index) => {
                if (index < limit) {
                    cb.checked = this.checked;
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    // Form will be submitted from modal confirmation button
    // No need for submit validation here
});
</script>
@endif

@if(session('success'))
    <div class="kt-alert kt-alert-success mb-4">
        <div class="kt-alert-content">
            <i class="ki-filled ki-check-circle"></i>
            <div>
                <strong>Success!</strong>
                <p class="mt-1">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '{{ route('hrd.manning-requests.show', $manningRequest->id) }}';
        }, 3000);
    </script>
@endif

@if(session('error'))
    <div class="kt-alert kt-alert-error mb-4">
        <div class="kt-alert-content">
            <i class="ki-filled ki-cross-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="kt-alert kt-alert-error mb-4">
        <div class="kt-alert-content">
            <i class="ki-filled ki-cross-circle"></i>
            <div>
                <strong>Validation Errors:</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Add to Draft Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="add-to-draft-confirm-modal">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-file-add text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Add to Draft Deployment</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground mb-4">
                Are you sure you want to add the selected officer(s) to the draft deployment? You can review and adjust before publishing.
            </p>
            <div class="p-3 bg-muted/50 rounded-lg">
                <p class="text-sm font-semibold mb-2 text-foreground">Selected Officers:</p>
                <ul class="text-sm text-secondary-foreground space-y-1" id="selected-officers-list">
                    <!-- Will be populated by JavaScript -->
                </ul>
            </div>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <button class="kt-btn kt-btn-primary" id="confirm-add-btn">
                <i class="ki-filled ki-file-add"></i>
                <span>Add to Draft</span>
            </button>
        </div>
    </div>
</div>
<!-- End of Generate Order Confirmation Modal -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generateBtn');
    const confirmBtn = document.getElementById('confirm-add-btn');
    const form = document.getElementById('matchForm');
    const selectedOfficersList = document.getElementById('selected-officers-list');
    
    // Update modal with selected officers when button is clicked
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const selected = document.querySelectorAll('.officer-checkbox:checked');
            selectedOfficersList.innerHTML = '';
            
            if (selected.length === 0) {
                selectedOfficersList.innerHTML = '<li class="text-muted-foreground">No officers selected</li>';
            } else {
                selected.forEach(function(checkbox) {
                    // Try to find officer info from table row or card
                    const row = checkbox.closest('tr');
                    const card = checkbox.closest('label')?.parentElement;
                    
                    let name = 'N/A';
                    let serviceNo = 'N/A';
                    let rank = 'N/A';
                    
                    if (row) {
                        // Desktop table view
                        const cells = row.querySelectorAll('td');
                        name = cells[1]?.querySelector('span')?.textContent.trim() || 'N/A';
                        serviceNo = cells[2]?.textContent.trim() || 'N/A';
                        rank = cells[3]?.textContent.trim() || 'N/A';
                    } else if (card) {
                        // Mobile card view
                        const spans = card.querySelectorAll('span');
                        name = spans[0]?.textContent.trim() || 'N/A';
                        const divs = card.querySelectorAll('div');
                        serviceNo = Array.from(divs).find(d => d.textContent.includes('SVC:'))?.textContent.replace('SVC:', '').trim() || 'N/A';
                        rank = Array.from(divs).find(d => d.textContent.includes('Rank:'))?.textContent.replace('Rank:', '').trim() || 'N/A';
                    }
                    
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${name}</strong> (${serviceNo}) - ${rank}`;
                    selectedOfficersList.appendChild(li);
                });
            }
        });
    }
    
    // Handle confirmation
    if (confirmBtn && form) {
        confirmBtn.addEventListener('click', function() {
            // Close modal
            const modal = document.getElementById('add-to-draft-confirm-modal');
            if (modal) {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
            }
            
            // Show loading state on button
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Adding...';
            
            // Submit form
            form.submit();
        });
    }
});
</script>
@endsection


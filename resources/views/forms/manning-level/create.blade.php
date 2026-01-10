@extends('layouts.app')

@section('title', 'Create Manning Level Request')
@section('page-title', 'Create Manning Level Request')

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

@if($errors->any())
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-semibold text-danger">Please fix the following errors:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-danger ml-8">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-9 mb-5 lg:mb-10">
    <div class="xl:col-span-2 space-y-5">
        <!-- Info Card -->
        <div class="kt-card bg-info/10 border border-info/20">
            <div class="kt-card-content p-5">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-2xl text-info"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-semibold text-mono">Manning Level Request</span>
                        <span class="text-xs text-secondary-foreground">
                            Submit a request for additional officers at your command. The request will be reviewed by Area Controller.
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Info Card -->
        
        <!-- Form -->
        <form class="kt-card" id="create-manning-request-form" method="POST" action="{{ route('staff-officer.manning-level.store') }}">
            @csrf
            <div class="kt-card-header">
                <h3 class="kt-card-title">Manning Level Request Form</h3>
            </div>
            <div class="kt-card-content space-y-5">
                @if(!$command)
                    <div class="kt-card bg-warning/10 border border-warning/20">
                        <div class="kt-card-content p-4">
                            <p class="text-sm text-warning">
                                <i class="ki-filled ki-information"></i> You are not assigned to a command. Please contact HRD for command assignment.
                            </p>
                        </div>
                    </div>
                @else
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">Command <span class="text-danger">*</span></label>
                        <input type="text" class="kt-input" value="{{ $command->name }}" disabled/>
                        <input type="hidden" name="command_id" value="{{ $command->id }}"/>
                        <p class="text-xs text-secondary-foreground mt-1">This request is for your assigned command</p>
                </div>
                
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">Request Type <span class="text-danger">*</span></label>
                    <select name="type" id="request_type" class="kt-input" required>
                        <option value="">Select Request Type</option>
                        <option value="GENERAL">General Manning Level (HRD) - All Ranks</option>
                        <option value="ZONE">Zone Manning Level (Zone Coordinator) - GL 7 and Below Only</option>
                    </select>
                    <p class="text-xs text-secondary-foreground mt-1">
                        <strong>General:</strong> For all ranks, processed by HRD<br>
                        <strong>Zone:</strong> For GL 7 and below only, processed by Zone Coordinator
                    </p>
                </div>
                @endif
                
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <label class="kt-form-label font-normal text-mono">Request Items <span class="text-danger">*</span></label>
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" id="add-item-btn">
                            <i class="ki-filled ki-plus"></i> Add Item
                        </button>
                    </div>
                    <div id="items-container" class="space-y-4">
                        <!-- Items will be added here dynamically -->
                    </div>
                </div>
                
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">Notes</label>
                    <textarea class="kt-input" placeholder="Additional notes or remarks" name="notes" rows="4"></textarea>
                </div>
            </div>
            <div class="kt-card-footer flex justify-end items-center flex-wrap gap-3">
                <a class="kt-btn kt-btn-outline" href="{{ route('staff-officer.manning-level') }}">Cancel</a>
                <button class="kt-btn kt-btn-primary" type="submit" id="submit-btn">
                    Create Request
                    <i class="ki-filled ki-check text-base"></i>
                </button>
            </div>
        </form>
        <!-- End of Form -->
    </div>
    <div class="xl:col-span-1">
        <!-- Instructions Card -->
        <div class="kt-card bg-accent/50">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Instructions</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-3 text-sm">
                    <div class="kt-card shadow-none bg-info/10 border border-info/20">
                        <div class="kt-card-content p-4">
                            <p class="text-xs text-secondary-foreground mb-2">
                                <strong class="text-mono">Request Process:</strong>
                            </p>
                            <ul class="text-xs text-secondary-foreground mt-2 list-disc list-inside space-y-1">
                                <li>Select the command for the request</li>
                                <li>Add one or more items specifying rank and quantity</li>
                                <li>Optionally specify gender and qualification requirements</li>
                                <li>Submit the request for review</li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-xs text-secondary-foreground">
                        Your request will be created as a draft and can be submitted for approval later.
                    </p>
                    <p class="text-xs text-secondary-foreground">
                        Once submitted, the request will be reviewed by Area Controller for approval.
                    </p>
                </div>
            </div>
        </div>
        <!-- End of Instructions Card -->
    </div>
</div>

@push('scripts')
<script>
let itemCount = 0;
const ranks = @json($ranks ?? []);
const qualifications = @json($qualifications ?? []);
const zoneRanks = ['IC', 'AIC', 'CA I', 'CA II', 'CA III']; // GL 7 and below ranks

// Get available ranks based on request type
function getAvailableRanks(type) {
    if (type === 'ZONE') {
        return ranks.filter(rank => zoneRanks.includes(rank));
    }
    return ranks; // GENERAL - all ranks
}

// Update rank selects based on request type
function updateRankSelects(type) {
    const availableRanks = getAvailableRanks(type);
    const rankSelects = document.querySelectorAll('select[name*="[rank]"]');
    
    rankSelects.forEach(select => {
        const currentValue = select.value;
        // Clear existing options except the default
        select.innerHTML = '<option value="">Select Rank</option>';
        
        // Add available ranks (reversed for LIFO)
        const reversedRanks = [...availableRanks].reverse();
        reversedRanks.forEach(rank => {
            const option = document.createElement('option');
            option.value = rank;
            option.textContent = rank;
            if (currentValue === rank && availableRanks.includes(rank)) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        
        // If current value is not available, clear it
        if (currentValue && !availableRanks.includes(currentValue)) {
            select.value = '';
        }
    });
}

// Item template
function createItemTemplate(index) {
    // Get request type to determine available ranks
    const requestType = document.getElementById('request_type')?.value || 'GENERAL';
    const availableRanks = getAvailableRanks(requestType);
    // Reverse ranks array to show latest rank on top (LIFO - Last In First Out)
    const reversedRanks = [...availableRanks].reverse();
    const ranksHtml = reversedRanks.map(rank => `<option value="${rank}">${rank}</option>`).join('');
    const qualsHtml = qualifications.map(qual => `<option value="${qual}">${qual}</option>`).join('');
    
    return `
        <div class="kt-card shadow-none bg-muted/30 border border-input" data-item-index="${index}">
            <div class="kt-card-content p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-mono">Item #${index + 1}</span>
                    <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost text-danger remove-item-btn" data-index="${index}">
                        <i class="ki-filled ki-trash"></i> Remove
                    </button>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Rank <span class="text-danger">*</span></label>
                        <select class="kt-input" name="items[${index}][rank]" required>
                            <option value="">Select Rank</option>
                            ${ranksHtml}
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Quantity Needed <span class="text-danger">*</span></label>
                        <input class="kt-input" type="number" name="items[${index}][quantity_needed]" min="1" placeholder="Number of officers" required/>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Gender Requirement</label>
                        <select class="kt-input" name="items[${index}][sex_requirement]">
                            <option value="ANY">Any</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Qualification Requirement</label>
                        <select class="kt-input" name="items[${index}][qualification_requirement]" id="qual-select-${index}">
                            <option value="">Any Qualification</option>
                            ${qualsHtml}
                        </select>
                        <input type="text" class="kt-input mt-1" name="items[${index}][qualification_custom]" placeholder="Enter custom qualification" style="display: none;" id="qual-custom-${index}"/>
                        <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost mt-1" onclick="toggleCustomQual(${index})" id="qual-toggle-${index}">
                            <i class="ki-filled ki-plus"></i> Custom
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Add item
function addItem() {
    const container = document.getElementById('items-container');
    // Insert new item at the top - it will become Item #1, pushing all others down
    const itemHtml = createItemTemplate(itemCount);
    container.insertAdjacentHTML('afterbegin', itemHtml);
    itemCount++;
    
    // Renumber all items: new item becomes #1, existing items get pushed down (#1->#2, #2->#3, etc.)
    updateItemNumbers();
    
    // No scrolling - items will push down naturally
}

// Remove item
function removeItem(index) {
    const item = document.querySelector(`[data-item-index="${index}"]`);
    if (item) {
        item.remove();
        updateItemNumbers();
    }
}

// Update item numbers
function updateItemNumbers() {
    const items = document.querySelectorAll('[data-item-index]');
    items.forEach((item, index) => {
        const indexAttr = item.getAttribute('data-item-index');
        const title = item.querySelector('.text-mono');
        if (title) {
            title.textContent = `Item #${index + 1}`;
        }
        // Update all inputs and selects
        item.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                input.setAttribute('name', newName);
            }
            // Update IDs that contain the index (e.g., qual-select-0 -> qual-select-1)
            const id = input.getAttribute('id');
            if (id && id.match(/-\d+$/)) {
                const newId = id.replace(/-\d+$/, `-${index}`);
                input.setAttribute('id', newId);
            } else if (id && id.match(/\d+$/)) {
                // Fallback for IDs without hyphen
                const newId = id.replace(/\d+$/, index);
                input.setAttribute('id', newId);
            }
        });
        // Update button IDs and onclick handlers
        item.querySelectorAll('button').forEach(button => {
            const id = button.getAttribute('id');
            if (id && id.match(/-\d+$/)) {
                // IDs with hyphen pattern (e.g., qual-toggle-0 -> qual-toggle-1)
                const newId = id.replace(/-\d+$/, `-${index}`);
                button.setAttribute('id', newId);
            } else if (id && id.match(/\d+$/)) {
                // Fallback for IDs without hyphen
                const newId = id.replace(/\d+$/, index);
                button.setAttribute('id', newId);
            }
            const onclick = button.getAttribute('onclick');
            if (onclick && onclick.includes('toggleCustomQual')) {
                button.setAttribute('onclick', `toggleCustomQual(${index})`);
            }
            // Update remove button data-index
            if (button.classList.contains('remove-item-btn')) {
                button.setAttribute('data-index', index);
            }
        });
        item.setAttribute('data-item-index', index);
    });
    itemCount = items.length;
    updateRemoveButtons();
}

// Update remove buttons
function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    removeButtons.forEach(btn => {
        // Remove existing listeners first
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        // Add new listener
        newBtn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            removeItem(index);
        });
    });
}

// Toggle custom qualification
function toggleCustomQual(index) {
    const select = document.getElementById(`qual-select-${index}`);
    const custom = document.getElementById(`qual-custom-${index}`);
    const toggle = document.getElementById(`qual-toggle-${index}`);
    
    if (select && custom && toggle) {
        if (select.style.display === 'none') {
            select.style.display = 'block';
            custom.style.display = 'none';
            custom.value = '';
            toggle.innerHTML = '<i class="ki-filled ki-plus"></i> Custom';
        } else {
            select.style.display = 'none';
            custom.style.display = 'block';
            select.value = '';
            toggle.innerHTML = '<i class="ki-filled ki-cross"></i> Use List';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Add first item
    addItem();
    
    // Add item button
    document.getElementById('add-item-btn').addEventListener('click', addItem);
    
    // Handle request type change
    const requestTypeSelect = document.getElementById('request_type');
    if (requestTypeSelect) {
        requestTypeSelect.addEventListener('change', function() {
            const type = this.value;
            if (type === 'ZONE') {
                // Filter ranks to only show GL 7 and below
                updateRankSelects('ZONE');
            } else if (type === 'GENERAL') {
                // Show all ranks
                updateRankSelects('GENERAL');
            }
        });
    }
    
    // Form submission - validate before submit
    document.getElementById('create-manning-request-form').addEventListener('submit', function(e) {
        // Collect and validate items
        const itemContainers = document.querySelectorAll('[data-item-index]');
        let validCount = 0;
        const validIndices = [];
        
        // First pass: identify valid items
        itemContainers.forEach((container) => {
            const rankSelect = container.querySelector('select[name*="[rank]"]');
            const quantityInput = container.querySelector('input[name*="[quantity_needed]"]');
            
            if (rankSelect && rankSelect.value && quantityInput && quantityInput.value && parseInt(quantityInput.value) > 0) {
                validIndices.push(container.getAttribute('data-item-index'));
                validCount++;
            }
        });
        
        if (validCount === 0) {
            e.preventDefault();
            alert('Please add at least one request item with rank and quantity');
            return false;
        }
        
        // Second pass: re-index valid items and remove invalid ones
        let newIndex = 0;
        itemContainers.forEach((container) => {
            const oldIndex = container.getAttribute('data-item-index');
            const rankSelect = container.querySelector('select[name*="[rank]"]');
            const quantityInput = container.querySelector('input[name*="[quantity_needed]"]');
            
            if (rankSelect && rankSelect.value && quantityInput && quantityInput.value && parseInt(quantityInput.value) > 0) {
                // Valid item - re-index it
                container.setAttribute('data-item-index', newIndex);
                
                // Update all inputs in this container
                container.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.includes('items[')) {
                        const match = name.match(/items\[(\d+)\]\[(.+)\]/);
                        if (match) {
                            const field = match[2];
                            const newName = `items[${newIndex}][${field}]`;
                            input.setAttribute('name', newName);
                        }
                    }
                });
                
                newIndex++;
            } else {
                // Invalid item - remove it
                container.remove();
            }
        });
        
        // Form will submit normally with properly indexed items
        return true;
    });
});
</script>
@endpush
@endsection



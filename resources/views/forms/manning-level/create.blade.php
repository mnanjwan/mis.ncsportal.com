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
                    <div class="relative">
                        <input type="hidden" name="type" id="request_type" value="{{ old('type') ?? '' }}" required>
                        <button type="button" 
                                id="request_type_select_trigger" 
                                class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                            <span id="request_type_select_text">{{ old('type') ? (old('type') === 'GENERAL' ? 'General Manning Level (HRD) - All Ranks' : 'Zone Manning Level (Zone Coordinator) - GL 7 and Below Only') : 'Select Request Type' }}</span>
                            <i class="ki-filled ki-down text-gray-400"></i>
                        </button>
                        <div id="request_type_dropdown" 
                             class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                            <div class="p-3 border-b border-input">
                                <input type="text" 
                                       id="request_type_search_input" 
                                       class="kt-input w-full pl-10" 
                                       placeholder="Search request type..."
                                       autocomplete="off">
                            </div>
                            <div id="request_type_options" class="max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
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
    const rankSelects = document.querySelectorAll('[id^="item_rank_"][id$="_id"]');
    
    rankSelects.forEach(hiddenInput => {
        const match = hiddenInput.id.match(/item_rank_(\d+)_id/);
        if (!match) return;
        const index = match[1];
        const displayText = document.getElementById(`item_rank_${index}_select_text`);
        const optionsContainer = document.getElementById(`item_rank_${index}_options`);
        
        if (!displayText || !optionsContainer) return;
        
        const currentValue = hiddenInput.value;
        
        // Rebuild options (reversed for LIFO)
        const reversedRanks = [...availableRanks].reverse();
        const rankOptions = [
            {id: '', name: 'Select Rank'},
            ...reversedRanks.map(rank => ({id: rank, name: rank}))
        ];
        
        // Update options container
        optionsContainer.innerHTML = rankOptions.map(opt => {
            return `
                <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                     data-id="${opt.id}" 
                     data-name="${opt.name}">
                    <div class="text-sm text-foreground">${opt.name}</div>
                </div>
            `;
        }).join('');
        
        // Add click handlers
        optionsContainer.querySelectorAll('.select-option').forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                hiddenInput.value = id;
                displayText.textContent = name;
                document.getElementById(`item_rank_${index}_dropdown`).classList.add('hidden');
                document.getElementById(`item_rank_${index}_search_input`).value = '';
            });
        });
        
        // Update search functionality
        const searchInput = document.getElementById(`item_rank_${index}_search_input`);
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filtered = rankOptions.filter(opt => {
                    return String(opt.name).toLowerCase().includes(searchTerm);
                });
                optionsContainer.innerHTML = filtered.map(opt => {
                    return `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" 
                             data-id="${opt.id}" 
                             data-name="${opt.name}">
                            <div class="text-sm text-foreground">${opt.name}</div>
                        </div>
                    `;
                }).join('');
                
                // Re-add click handlers
                optionsContainer.querySelectorAll('.select-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        hiddenInput.value = id;
                        displayText.textContent = name;
                        document.getElementById(`item_rank_${index}_dropdown`).classList.add('hidden');
                        searchInput.value = '';
                    });
                });
            });
        }
        
        // If current value is not available, clear it
        if (currentValue && !availableRanks.includes(currentValue)) {
            hiddenInput.value = '';
            displayText.textContent = 'Select Rank';
        } else if (currentValue && availableRanks.includes(currentValue)) {
            displayText.textContent = currentValue;
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
                        <div class="relative">
                            <input type="hidden" name="items[${index}][rank]" id="item_rank_${index}_id" value="" required>
                            <button type="button" 
                                    id="item_rank_${index}_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="item_rank_${index}_select_text">Select Rank</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="item_rank_${index}_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="item_rank_${index}_search_input" 
                                           class="kt-input w-full pl-10 text-sm" 
                                           placeholder="Search rank..."
                                           autocomplete="off">
                                </div>
                                <div id="item_rank_${index}_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Quantity Needed <span class="text-danger">*</span></label>
                        <input class="kt-input" type="number" name="items[${index}][quantity_needed]" min="1" placeholder="Number of officers" required/>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Gender Requirement</label>
                        <div class="relative">
                            <input type="hidden" name="items[${index}][sex_requirement]" id="item_sex_${index}_id" value="ANY">
                            <button type="button" 
                                    id="item_sex_${index}_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="item_sex_${index}_select_text">Any</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="item_sex_${index}_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="item_sex_${index}_search_input" 
                                           class="kt-input w-full pl-10 text-sm" 
                                           placeholder="Search..."
                                           autocomplete="off">
                                </div>
                                <div id="item_sex_${index}_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono text-xs">Qualification Requirement</label>
                        <div class="relative">
                            <input type="hidden" name="items[${index}][qualification_requirement]" id="qual-select-${index}" value="">
                            <button type="button" 
                                    id="item_qual_${index}_select_trigger" 
                                    class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                <span id="item_qual_${index}_select_text">Any Qualification</span>
                                <i class="ki-filled ki-down text-gray-400"></i>
                            </button>
                            <div id="item_qual_${index}_dropdown" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                <div class="p-3 border-b border-input">
                                    <input type="text" 
                                           id="item_qual_${index}_search_input" 
                                           class="kt-input w-full pl-10 text-sm" 
                                           placeholder="Search qualification..."
                                           autocomplete="off">
                                </div>
                                <div id="item_qual_${index}_options" class="max-h-60 overflow-y-auto"></div>
                            </div>
                        </div>
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
    
    // Initialize searchable selects for the new item
    initializeItemSelects(itemCount);
    
    itemCount++;
    
    // Renumber all items: new item becomes #1, existing items get pushed down (#1->#2, #2->#3, etc.)
    updateItemNumbers();
    
    // No scrolling - items will push down naturally
}

// Initialize searchable selects for an item
function initializeItemSelects(index) {
    // Get request type to determine available ranks
    const requestType = document.getElementById('request_type')?.value || 'GENERAL';
    const availableRanks = getAvailableRanks(requestType);
    const reversedRanks = [...availableRanks].reverse();
    
    // Rank options
    const rankOptions = [
        {id: '', name: 'Select Rank'},
        ...reversedRanks.map(rank => ({id: rank, name: rank}))
    ];
    
    // Sex options
    const sexOptions = [
        {id: 'ANY', name: 'Any'},
        {id: 'M', name: 'Male'},
        {id: 'F', name: 'Female'}
    ];
    
    // Qualification options
    const qualOptions = [
        {id: '', name: 'Any Qualification'},
        ...qualifications.map(qual => ({id: qual, name: qual}))
    ];
    
    // Initialize rank select
    createSearchableSelect({
        triggerId: `item_rank_${index}_select_trigger`,
        hiddenInputId: `item_rank_${index}_id`,
        dropdownId: `item_rank_${index}_dropdown`,
        searchInputId: `item_rank_${index}_search_input`,
        optionsContainerId: `item_rank_${index}_options`,
        displayTextId: `item_rank_${index}_select_text`,
        options: rankOptions,
        placeholder: 'Select Rank',
        searchPlaceholder: 'Search rank...'
    });
    
    // Initialize sex select
    createSearchableSelect({
        triggerId: `item_sex_${index}_select_trigger`,
        hiddenInputId: `item_sex_${index}_id`,
        dropdownId: `item_sex_${index}_dropdown`,
        searchInputId: `item_sex_${index}_search_input`,
        optionsContainerId: `item_sex_${index}_options`,
        displayTextId: `item_sex_${index}_select_text`,
        options: sexOptions,
        placeholder: 'Any',
        searchPlaceholder: 'Search...'
    });
    
    // Initialize qualification select
    createSearchableSelect({
        triggerId: `item_qual_${index}_select_trigger`,
        hiddenInputId: `qual-select-${index}`,
        dropdownId: `item_qual_${index}_dropdown`,
        searchInputId: `item_qual_${index}_search_input`,
        optionsContainerId: `item_qual_${index}_options`,
        displayTextId: `item_qual_${index}_select_text`,
        options: qualOptions,
        placeholder: 'Any Qualification',
        searchPlaceholder: 'Search qualification...'
    });
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
    items.forEach((item, newIndex) => {
        const oldIndex = item.getAttribute('data-item-index');
        const title = item.querySelector('.text-mono');
        if (title) {
            title.textContent = `Item #${newIndex + 1}`;
        }
        
        // Update all inputs and hidden inputs (searchable selects use hidden inputs)
        item.querySelectorAll('input[type="hidden"], input[type="text"], input[type="number"]').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('items[')) {
                const newName = name.replace(/items\[\d+\]/, `items[${newIndex}]`);
                input.setAttribute('name', newName);
            }
            // Update IDs that contain the index
            const id = input.getAttribute('id');
            if (id) {
                // Handle patterns like item_rank_0_id, qual-select-0, qual-custom-0
                if (id.match(/item_(rank|sex)_\d+_id/)) {
                    const newId = id.replace(/item_(rank|sex)_\d+_id/, `item_$1_${newIndex}_id`);
                    input.setAttribute('id', newId);
                } else if (id.match(/qual-(select|custom)-\d+/)) {
                    const newId = id.replace(/qual-(select|custom)-\d+/, `qual-$1-${newIndex}`);
                    input.setAttribute('id', newId);
                }
            }
        });
        
        // Update searchable select elements (triggers, dropdowns, etc.)
        const selectPatterns = [
            {prefix: 'item_rank_', suffix: '_id'},
            {prefix: 'item_rank_', suffix: '_select_trigger'},
            {prefix: 'item_rank_', suffix: '_select_text'},
            {prefix: 'item_rank_', suffix: '_dropdown'},
            {prefix: 'item_rank_', suffix: '_search_input'},
            {prefix: 'item_rank_', suffix: '_options'},
            {prefix: 'item_sex_', suffix: '_id'},
            {prefix: 'item_sex_', suffix: '_select_trigger'},
            {prefix: 'item_sex_', suffix: '_select_text'},
            {prefix: 'item_sex_', suffix: '_dropdown'},
            {prefix: 'item_sex_', suffix: '_search_input'},
            {prefix: 'item_sex_', suffix: '_options'},
            {prefix: 'item_qual_', suffix: '_select_trigger'},
            {prefix: 'item_qual_', suffix: '_select_text'},
            {prefix: 'item_qual_', suffix: '_dropdown'},
            {prefix: 'item_qual_', suffix: '_search_input'},
            {prefix: 'item_qual_', suffix: '_options'}
        ];
        
        selectPatterns.forEach(pattern => {
            const oldId = `${pattern.prefix}${oldIndex}${pattern.suffix}`;
            const newId = `${pattern.prefix}${newIndex}${pattern.suffix}`;
            const element = document.getElementById(oldId);
            if (element) {
                element.id = newId;
            }
        });
        
        // Update button IDs
        item.querySelectorAll('button').forEach(button => {
            const id = button.getAttribute('id');
            if (id) {
                if (id.match(/qual-toggle-\d+/)) {
                    button.setAttribute('id', `qual-toggle-${newIndex}`);
                } else if (id.match(/qual-toggle-\d+/)) {
                    button.setAttribute('id', id.replace(/-\d+$/, `-${newIndex}`));
                }
            }
            const onclick = button.getAttribute('onclick');
            if (onclick && onclick.includes('toggleCustomQual')) {
                button.setAttribute('onclick', `toggleCustomQual(${newIndex})`);
            }
            // Update remove button data-index
            if (button.classList.contains('remove-item-btn')) {
                button.setAttribute('data-index', newIndex);
            }
        });
        
        item.setAttribute('data-item-index', newIndex);
        
        // Reinitialize the searchable selects with new IDs
        initializeItemSelects(newIndex);
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
    const qualSelectContainer = document.getElementById(`item_qual_${index}_select_trigger`)?.parentElement;
    const custom = document.getElementById(`qual-custom-${index}`);
    const toggle = document.getElementById(`qual-toggle-${index}`);
    
    if (qualSelectContainer && custom && toggle) {
        if (qualSelectContainer.style.display === 'none') {
            qualSelectContainer.style.display = 'block';
            custom.style.display = 'none';
            custom.value = '';
            toggle.innerHTML = '<i class="ki-filled ki-plus"></i> Custom';
        } else {
            qualSelectContainer.style.display = 'none';
            custom.style.display = 'block';
            document.getElementById(`qual-select-${index}`).value = '';
            document.getElementById(`item_qual_${index}_select_text`).textContent = 'Any Qualification';
            toggle.innerHTML = '<i class="ki-filled ki-cross"></i> Use List';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Add first item
    addItem();
    
    // Add item button
    document.getElementById('add-item-btn').addEventListener('click', addItem);
    
    // Initialize request type select
    const requestTypeOptions = [
        {id: '', name: 'Select Request Type'},
        {id: 'GENERAL', name: 'General Manning Level (HRD) - All Ranks'},
        {id: 'ZONE', name: 'Zone Manning Level (Zone Coordinator) - GL 7 and Below Only'}
    ];
    
    if (document.getElementById('request_type_select_trigger')) {
        createSearchableSelect({
            triggerId: 'request_type_select_trigger',
            hiddenInputId: 'request_type',
            dropdownId: 'request_type_dropdown',
            searchInputId: 'request_type_search_input',
            optionsContainerId: 'request_type_options',
            displayTextId: 'request_type_select_text',
            options: requestTypeOptions,
            placeholder: 'Select Request Type',
            searchPlaceholder: 'Search request type...',
            onSelect: function(option) {
                const type = option.id;
                if (type === 'ZONE') {
                    // Filter ranks to only show GL 7 and below
                    updateRankSelects('ZONE');
                } else if (type === 'GENERAL') {
                    // Show all ranks
                    updateRankSelects('GENERAL');
                }
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
            const rankHiddenInput = container.querySelector('input[type="hidden"][name*="[rank]"]');
            const quantityInput = container.querySelector('input[name*="[quantity_needed]"]');
            
            if (rankHiddenInput && rankHiddenInput.value && quantityInput && quantityInput.value && parseInt(quantityInput.value) > 0) {
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
            const rankHiddenInput = container.querySelector('input[type="hidden"][name*="[rank]"]');
            const quantityInput = container.querySelector('input[name*="[quantity_needed]"]');
            
            if (rankHiddenInput && rankHiddenInput.value && quantityInput && quantityInput.value && parseInt(quantityInput.value) > 0) {
                // Valid item - re-index it
                container.setAttribute('data-item-index', newIndex);
                
                // Update all inputs in this container
                container.querySelectorAll('input[type="hidden"], input[type="text"], input[type="number"]').forEach(input => {
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



@extends('layouts.app')

@section('title', 'Add Drug / Item')
@section('page-title', 'Add Drug / Item to Catalog')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.drugs.index') }}" class="text-secondary-foreground hover:text-primary">Drugs / Items</a>
    <span>/</span>
    <span class="text-secondary-foreground">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5 max-w-2xl">
        @if($errors->any())
            <div class="kt-alert kt-alert-danger">
                <i class="ki-filled ki-information"></i>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pharmacy.drugs.store') }}">
            @csrf

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Drug / Item Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Drug / Item Name *</label>
                            <div class="relative">
                                <input type="hidden" name="name" id="drug_name_id" value="{{ old('name') }}" required>
                                <button type="button"
                                        id="drug_name_select_trigger"
                                        class="kt-input w-full text-left flex items-center justify-between cursor-pointer">
                                    <span id="drug_name_select_text">{{ old('name') ? old('name') : '-- Select or search drug / item --' }}</span>
                                    <i class="ki-filled ki-down text-gray-400"></i>
                                </button>
                                <div id="drug_name_dropdown"
                                     class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden">
                                    <div class="p-3 border-b border-input">
                                        <input type="text"
                                               id="drug_name_search_input"
                                               class="kt-input w-full pl-10"
                                               placeholder="Search drug / item..."
                                               autocomplete="off">
                                    </div>
                                    <div id="drug_name_options" class="max-h-60 overflow-y-auto"></div>
                                </div>
                                <input type="text"
                                       id="drug_name_custom"
                                       class="kt-input mt-2 hidden"
                                       placeholder="Type new drug / item name (e.g., Paracetamol 500mg)..."
                                       autocomplete="off">
                            </div>
                            <p class="text-xs text-secondary-foreground mt-1">Search to select an existing drug/item, or add new to avoid duplicates.</p>
                        </div>

                        <div>
                            <label class="kt-label">Description (Optional)</label>
                            <textarea name="description" class="kt-input" rows="3" placeholder="Enter drug / item description...">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="kt-label">Unit of Measure *</label>
                                <select name="unit_of_measure" class="kt-input" required>
                                    <option value="others" {{ old('unit_of_measure', 'others') === 'others' ? 'selected' : '' }}>Others</option>
                                    <option value="tablets" {{ old('unit_of_measure') === 'tablets' ? 'selected' : '' }}>Tablets</option>
                                    <option value="capsules" {{ old('unit_of_measure') === 'capsules' ? 'selected' : '' }}>Capsules</option>
                                    <option value="bottles" {{ old('unit_of_measure') === 'bottles' ? 'selected' : '' }}>Bottles</option>
                                    <option value="vials" {{ old('unit_of_measure') === 'vials' ? 'selected' : '' }}>Vials</option>
                                    <option value="ampoules" {{ old('unit_of_measure') === 'ampoules' ? 'selected' : '' }}>Ampoules</option>
                                    <option value="sachets" {{ old('unit_of_measure') === 'sachets' ? 'selected' : '' }}>Sachets</option>
                                    <option value="tubes" {{ old('unit_of_measure') === 'tubes' ? 'selected' : '' }}>Tubes</option>
                                    <option value="units" {{ old('unit_of_measure') === 'units' ? 'selected' : '' }}>Units</option>
                                    <option value="packs" {{ old('unit_of_measure') === 'packs' ? 'selected' : '' }}>Packs</option>
                                </select>
                            </div>

                            <div>
                                <label class="kt-label">Category (Optional)</label>
                                <input type="text" name="category" class="kt-input" value="{{ old('category') }}" 
                                       placeholder="e.g., Analgesics, Antibiotics" list="categories">
                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end gap-3">
                    <a href="{{ route('pharmacy.drugs.index') }}" class="kt-btn kt-btn-light">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Add Drug / Item</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ADD_NEW_VALUE = '__ADD_NEW__';
    const existingNames = @json($existingDrugNames ?? []);
    const drugNameOptions = [
        { id: '', name: '-- Select or search drug / item --' },
        { id: ADD_NEW_VALUE, name: '-- Add new drug / item (type below) --' },
        ...existingNames.map(n => ({ id: n, name: n }))
    ];

    // Inline fallback when Vite bundle (app.js) doesn't load on production
    function createSearchableSelectFallback(config) {
        var c = config, trigger = document.getElementById(c.triggerId), hidden = document.getElementById(c.hiddenInputId),
            dropdown = document.getElementById(c.dropdownId), searchInput = document.getElementById(c.searchInputId),
            optionsContainer = document.getElementById(c.optionsContainerId), displayText = document.getElementById(c.displayTextId);
        if (!trigger || !hidden || !dropdown || !searchInput || !optionsContainer || !displayText) return;
        var options = c.options || [], filtered = options.slice(0);
        function render(opts) {
            optionsContainer.innerHTML = opts.length === 0 ? '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>' : opts.map(function(opt) {
                var d = c.displayFn ? c.displayFn(opt) : (opt.name != null ? opt.name : opt.id != null ? opt.id : opt);
                var v = opt.id !== undefined ? opt.id : (opt.value !== undefined ? opt.value : opt);
                return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-input last:border-0 select-option" data-id="' + v + '" data-name="' + d + '"><div class="text-sm text-foreground">' + d + '</div></div>';
            }).join('');
            optionsContainer.querySelectorAll('.select-option').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var id = this.dataset.id, name = this.dataset.name;
                    hidden.value = id ?? ''; displayText.textContent = name ?? '';
                    dropdown.classList.add('hidden'); dropdown.style.cssText = ''; searchInput.value = '';
                    filtered = options.slice(0); render(filtered);
                    if (c.onSelect) c.onSelect({ id: id, name: name });
                });
            });
        }
        function openDrop() {
            dropdown.classList.remove('hidden');
            var r = trigger.getBoundingClientRect();
            dropdown.style.cssText = 'position:fixed;z-index:99999;top:' + (r.bottom + 4) + 'px;left:' + r.left + 'px;width:' + r.width + 'px;min-width:' + r.width + 'px;';
            setTimeout(function() { searchInput.focus(); }, 100);
        }
        function closeDrop() { dropdown.classList.add('hidden'); dropdown.style.cssText = ''; }
        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase();
            filtered = options.filter(function(opt) {
                var d = c.displayFn ? c.displayFn(opt) : (opt.name != null ? opt.name : opt.id != null ? opt.id : opt);
                return String(d).toLowerCase().includes(term);
            });
            render(filtered);
        });
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.contains('hidden') ? openDrop() : closeDrop();
        });
        document.addEventListener('click', function(e) {
            setTimeout(function() {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) closeDrop();
            }, 0);
        });
        render(filtered);
    }

    var createSearchableSelect = typeof window.createSearchableSelect === 'function' ? window.createSearchableSelect : createSearchableSelectFallback;

    createSearchableSelect({
        triggerId: 'drug_name_select_trigger',
        hiddenInputId: 'drug_name_id',
        dropdownId: 'drug_name_dropdown',
        searchInputId: 'drug_name_search_input',
        optionsContainerId: 'drug_name_options',
        displayTextId: 'drug_name_select_text',
        options: drugNameOptions,
        placeholder: '-- Select or search drug / item --',
        searchPlaceholder: 'Search drug / item...',
        onSelect: function(option) {
            const hidden = document.getElementById('drug_name_id');
            const customInput = document.getElementById('drug_name_custom');
            const displayText = document.getElementById('drug_name_select_text');
            if (!hidden || !customInput || !displayText) return;

            if (option && option.id === ADD_NEW_VALUE) {
                customInput.classList.remove('hidden');
                customInput.value = (hidden.value && hidden.value !== ADD_NEW_VALUE) ? hidden.value : '';
                hidden.value = customInput.value.trim();
                displayText.textContent = '-- Add new drug / item (type below) --';
                setTimeout(() => customInput.focus(), 0);
            } else {
                customInput.classList.add('hidden');
                customInput.value = '';
                if (option && option.id) hidden.value = option.id;
            }
        }
    });

    const customInput = document.getElementById('drug_name_custom');
    const hiddenInput = document.getElementById('drug_name_id');
    if (customInput && hiddenInput) {
        const initialVal = hiddenInput.value;
        if (initialVal && initialVal !== ADD_NEW_VALUE && !existingNames.includes(initialVal)) {
            customInput.classList.remove('hidden');
            customInput.value = initialVal;
        }
        customInput.addEventListener('input', function() {
            hiddenInput.value = this.value.trim();
        });
    }
});
</script>
@endpush

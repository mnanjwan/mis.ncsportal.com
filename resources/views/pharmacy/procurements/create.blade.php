@extends('layouts.app')

@section('title', 'Create Procurement')
@section('page-title', 'Create Procurement')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.procurements.index') }}" class="text-secondary-foreground hover:text-primary">Procurements</a>
    <span>/</span>
    <span class="text-secondary-foreground">Create</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
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

        <form method="POST" action="{{ route('pharmacy.procurements.store') }}" id="procurementForm">
            @csrf

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">New Procurement Draft</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Notes (Optional)</label>
                            <textarea name="notes" class="kt-input" rows="3" placeholder="Add any notes for this procurement...">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label class="kt-label">Procurement Items</label>
                            <div id="itemsContainer" class="flex flex-col gap-3">
                                <!-- Items will be added here dynamically -->
                            </div>
                            <button type="button" id="addItemBtn" class="kt-btn kt-btn-light mt-3">
                                <i class="ki-filled ki-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end gap-3">
                    <a href="{{ route('pharmacy.procurements.index') }}" class="kt-btn kt-btn-light">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Create Draft</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Item Row Template -->
    <template id="itemRowTemplate">
        <div class="item-row flex flex-wrap items-start gap-3 p-3 bg-muted/50 rounded-lg border border-input">
            <div class="flex-grow min-w-[200px] relative">
                <label class="kt-label text-xs">Drug / Item Name *</label>
                <input type="hidden" name="items[INDEX][drug_name]" id="proc_drug_name_INDEX_id" value="" required>
                <button type="button" id="proc_drug_name_INDEX_trigger" class="kt-input kt-input-sm w-full text-left flex items-center justify-between cursor-pointer">
                    <span id="proc_drug_name_INDEX_text">-- Search or add drug / item --</span>
                    <i class="ki-filled ki-down text-gray-400"></i>
                </button>
                <div id="proc_drug_name_INDEX_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden" style="min-width: 200px;">
                    <div class="p-2 border-b border-input">
                        <input type="text" id="proc_drug_name_INDEX_search" class="kt-input kt-input-sm w-full" placeholder="Search..." autocomplete="off">
                    </div>
                    <div id="proc_drug_name_INDEX_options" class="max-h-48 overflow-y-auto"></div>
                </div>
            </div>
            <div class="w-32">
                <label class="kt-label text-xs">Quantity *</label>
                <input type="number" name="items[INDEX][quantity]" class="kt-input kt-input-sm" placeholder="Qty" min="1" required>
            </div>
            <div class="w-48 relative">
                <label class="kt-label text-xs">Unit *</label>
                <input type="hidden" name="items[INDEX][unit]" id="proc_unit_INDEX_id" value="others" required>
                <button type="button" id="proc_unit_INDEX_trigger" class="kt-input kt-input-sm w-full text-left flex items-center justify-between cursor-pointer">
                    <span id="proc_unit_INDEX_text">Others</span>
                    <i class="ki-filled ki-down text-gray-400"></i>
                </button>
                <div id="proc_unit_INDEX_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-input rounded-lg shadow-lg hidden" style="min-width: 150px;">
                    <div class="p-2 border-b border-input">
                        <input type="text" id="proc_unit_INDEX_search" class="kt-input kt-input-sm w-full" placeholder="Search..." autocomplete="off">
                    </div>
                    <div id="proc_unit_INDEX_options" class="max-h-48 overflow-y-auto"></div>
                </div>
            </div>
            <div class="flex items-end">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-light kt-btn-icon remove-item-btn mt-5">
                    <i class="ki-filled ki-trash text-danger"></i>
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingNames = @json($existingDrugNames ?? []);
    const drugOptions = [
        { id: '', name: '-- Search drug / item --' },
        ...existingNames.map(n => ({ id: n, name: n }))
    ];

    const unitOptionsList = @json($unitOptions ?? []);
    const unitOptions = [
        { id: '', name: '-- Select unit --' },
        ...unitOptionsList.map(u => ({ id: u, name: u }))
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

    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemRowTemplate');
    const addBtn = document.getElementById('addItemBtn');
    let itemIndex = 0;

    function initDrugSelect(row, idx) {
        const prefix = 'proc_drug_name_' + idx;
        createSearchableSelect({
            triggerId: prefix + '_trigger',
            hiddenInputId: prefix + '_id',
            dropdownId: prefix + '_dropdown',
            searchInputId: prefix + '_search',
            optionsContainerId: prefix + '_options',
            displayTextId: prefix + '_text',
            options: drugOptions.slice(),
            placeholder: '-- Search drug / item --',
            searchPlaceholder: 'Search...',
            onSelect: function(option) {
                const hidden = document.getElementById(prefix + '_id');
                const displayText = document.getElementById(prefix + '_text');
                if (!hidden || !displayText) return;
                if (option && option.id) {
                    hidden.value = option.id;
                }
            }
        });
    }

    function initUnitSelect(row, idx) {
        const prefix = 'proc_unit_' + idx;
        createSearchableSelect({
            triggerId: prefix + '_trigger',
            hiddenInputId: prefix + '_id',
            dropdownId: prefix + '_dropdown',
            searchInputId: prefix + '_search',
            optionsContainerId: prefix + '_options',
            displayTextId: prefix + '_text',
            options: unitOptions.slice(),
            placeholder: '-- Select unit --',
            searchPlaceholder: 'Search...',
            onSelect: function(option) {
                const hidden = document.getElementById(prefix + '_id');
                const displayText = document.getElementById(prefix + '_text');
                if (!hidden || !displayText) return;
                if (option && option.id) {
                    hidden.value = option.id;
                }
            }
        });
    }

    function addItem() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.item-row');
        const idx = itemIndex;

        row.querySelectorAll('[name]').forEach(el => { el.name = el.name.replace('INDEX', idx); });
        row.querySelectorAll('[id]').forEach(el => { el.id = el.id.replace('INDEX', idx); });

        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(clone);
        initDrugSelect(row, idx);
        initUnitSelect(row, idx);
        itemIndex++;
    }

    addBtn.addEventListener('click', addItem);
    addItem();
});
</script>
@endpush

@extends('layouts.app')

@section('page-title', 'Edit Stock Return Draft')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('pharmacy.returns.index') }}">Pharmacy</a>
    <span class="text-muted-foreground">/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('pharmacy.returns.index') }}">Stock Returns</a>
    <span class="text-muted-foreground">/</span>
    <span class="text-muted-foreground">Edit Draft</span>
@endsection

@section('content')
    <div class="kt-container-fixed">
        <div class="kt-card max-w-4xl mx-auto overflow-hidden">
            <div class="kt-card-header py-4">
                <h3 class="kt-card-title text-xl font-bold">Edit Stock Return Draft</h3>
                <p class="text-sm text-secondary-foreground mt-1">Modify items and quantities for your return request.</p>
            </div>
            <div class="kt-card-content p-6">
                <form action="{{ route('pharmacy.returns.update', $return->id) }}" method="POST" id="return-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-8">
                        <label for="notes" class="block text-sm font-semibold text-foreground mb-2">Notes / Reason for Return</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="w-full rounded-lg border border-border bg-background px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                                  placeholder="e.g., Short-dated stock, Surplus, Recalled items...">{{ $return->notes }}</textarea>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-md font-bold text-foreground mb-4">Return Items</h4>
                        <div id="items-container" class="space-y-4">
                            @foreach($return->items as $idx => $rItem)
                                <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-4 p-4 rounded-xl border border-border bg-muted/20 relative group transition-all hover:bg-muted/30">
                                    <div class="md:col-span-7 relative">
                                        <label class="block text-xs font-bold text-secondary-foreground uppercase tracking-wider mb-2">Select Drug / Item</label>
                                        
                                        <input type="hidden" name="items[{{ $idx }}][drug_id]" id="drug_{{ $idx }}_id" value="{{ $rItem->pharmacy_drug_id }}" required>
                                        <button type="button" id="drug_{{ $idx }}_trigger" class="w-full rounded-lg border border-border bg-background px-4 py-2 text-sm text-left flex items-center justify-between cursor-pointer focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                            <span id="drug_{{ $idx }}_text" class="truncate">{{ $rItem->drug->name }} (Available: {{ $rItem->drug->getStockForCommand(auth()->user()->command_id) }} {{ $rItem->drug->unit_of_measure }})</span>
                                            <i class="ki-filled ki-down text-gray-400"></i>
                                        </button>
                                        
                                        <div id="drug_{{ $idx }}_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-border rounded-lg shadow-lg hidden" style="min-width: 250px;">
                                            <div class="p-2 border-b border-border">
                                                <input type="text" id="drug_{{ $idx }}_search" class="w-full rounded-md border border-border bg-background px-3 py-1.5 text-sm outline-none focus:border-primary" placeholder="Search drug..." autocomplete="off">
                                            </div>
                                            <div id="drug_{{ $idx }}_options" class="max-h-60 overflow-y-auto scrollbar-thin"></div>
                                        </div>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-bold text-secondary-foreground uppercase tracking-wider mb-2">Return Quantity</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $rItem->quantity }}" min="1" class="qty-input w-full rounded-lg border border-border bg-background px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                                            <span class="unit-label text-xs font-medium text-secondary-foreground min-w-[40px]">{{ $rItem->drug->unit_of_measure }}</span>
                                        </div>
                                        <p class="qty-warning text-[10px] text-danger mt-1 hidden font-semibold">Cannot exceed available stock</p>
                                    </div>
                                    <div class="md:col-span-1 flex items-end justify-center pb-1">
                                        <button type="button" class="remove-item-btn text-muted-foreground hover:text-danger transition-colors opacity-0 group-hover:opacity-100 p-2" title="Remove Item">
                                            <i class="ki-filled ki-trash text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" id="add-item-btn" 
                                class="mt-4 flex items-center gap-2 text-primary hover:text-primary/80 transition-colors text-sm font-semibold">
                            <i class="ki-filled ki-plus-circle text-lg"></i>
                            Add Another Item
                        </button>
                    </div>

                    <div class="mt-10 pt-6 border-t border-border flex items-center justify-end gap-3">
                        <a href="{{ route('pharmacy.returns.show', $return->id) }}" class="kt-btn kt-btn-ghost">Cancel</a>
                        <button type="submit" class="kt-btn kt-btn-primary px-8">Update Return Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Template for Dynamic Rows -->
    <template id="item-row-template">
        <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-4 p-4 rounded-xl border border-border bg-muted/20 relative group transition-all hover:bg-muted/30">
            <div class="md:col-span-7 relative">
                <label class="block text-xs font-bold text-secondary-foreground uppercase tracking-wider mb-2">Select Drug / Item</label>
                
                <input type="hidden" name="items[INDEX][drug_id]" id="drug_INDEX_id" required>
                <button type="button" id="drug_INDEX_trigger" class="w-full rounded-lg border border-border bg-background px-4 py-2 text-sm text-left flex items-center justify-between cursor-pointer focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <span id="drug_INDEX_text" class="truncate">-- Search and Select Drug --</span>
                    <i class="ki-filled ki-down text-gray-400"></i>
                </button>
                
                <div id="drug_INDEX_dropdown" class="absolute z-50 w-full mt-1 bg-background border border-border rounded-lg shadow-lg hidden" style="min-width: 250px;">
                    <div class="p-2 border-b border-border">
                        <input type="text" id="drug_INDEX_search" class="w-full rounded-md border border-border bg-background px-3 py-1.5 text-sm outline-none focus:border-primary" placeholder="Search drug..." autocomplete="off">
                    </div>
                    <div id="drug_INDEX_options" class="max-h-60 overflow-y-auto scrollbar-thin"></div>
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-secondary-foreground uppercase tracking-wider mb-2">Return Quantity</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="items[INDEX][quantity]" min="1" class="qty-input w-full rounded-lg border border-border bg-background px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                    <span class="unit-label text-xs font-medium text-secondary-foreground min-w-[40px]">Unit</span>
                </div>
                <p class="qty-warning text-[10px] text-danger mt-1 hidden font-semibold">Cannot exceed available stock</p>
            </div>
            <div class="md:col-span-1 flex items-end justify-center pb-1">
                <button type="button" class="remove-item-btn text-muted-foreground hover:text-danger transition-colors opacity-0 group-hover:opacity-100 p-2" title="Remove Item">
                    <i class="ki-filled ki-trash text-lg"></i>
                </button>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('items-container');
            const template = document.getElementById('item-row-template');
            const addBtn = document.getElementById('add-item-btn');
            let index = {{ $return->items->count() }};

            const drugsData = [
                @foreach($drugs as $drug)
                    { 
                        id: '{{ $drug['id'] }}', 
                        name: '{{ addslashes($drug['name']) }} (Available: {{ $drug['available_quantity'] }} {{ $drug['unit'] }})',
                        qty: {{ $drug['available_quantity'] }},
                        unit: '{{ $drug['unit'] }}'
                    },
                @endforeach
            ];

            const drugOptions = [
                { id: '', name: '-- Search and Select Drug --', qty: 0, unit: 'Unit' },
                ...drugsData
            ];

            function createSearchableSelectFallback(config) {
                var c = config, trigger = document.getElementById(c.triggerId), hidden = document.getElementById(c.hiddenInputId),
                    dropdown = document.getElementById(c.dropdownId), searchInput = document.getElementById(c.searchInputId),
                    optionsContainer = document.getElementById(c.optionsContainerId), displayText = document.getElementById(c.displayTextId);
                
                if (!trigger || !hidden || !dropdown || !searchInput || !optionsContainer || !displayText) {
                    console.error('SearchableSelect: Required elements not found for ID suffix', c.triggerId);
                    return;
                }

                var options = c.options || [], filtered = options.slice(0);
                function render(opts) {
                    optionsContainer.innerHTML = opts.length === 0 ? '<div class="p-3 text-sm text-secondary-foreground text-center">No options found</div>' : opts.map(function(opt) {
                        return '<div class="p-3 hover:bg-muted/50 cursor-pointer border-b border-border last:border-0 select-option" data-id="' + opt.id + '" data-name="' + (opt.name).replace(/"/g, '&quot;') + '"><div class="text-sm text-foreground">' + opt.name + '</div></div>';
                    }).join('');
                    optionsContainer.querySelectorAll('.select-option').forEach(function(el) {
                        el.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var id = this.dataset.id, name = this.dataset.name;
                            hidden.value = id ?? ''; displayText.textContent = name ?? '';
                            dropdown.classList.add('hidden'); dropdown.style.cssText = ''; searchInput.value = '';
                            filtered = options.slice(0); render(filtered);
                            const selectedOpt = options.find(o => o.id == id);
                            if (c.onSelect) c.onSelect(selectedOpt);
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
                        return String(opt.name).toLowerCase().includes(term);
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

            function setupRow(row, idx) {
                const qtyInput = row.querySelector('.qty-input');
                const unitLabel = row.querySelector('.unit-label');
                const qtyWarning = row.querySelector('.qty-warning');
                const removeBtn = row.querySelector('.remove-item-btn');

                createSearchableSelectFallback({
                    triggerId: 'drug_' + idx + '_trigger',
                    hiddenInputId: 'drug_' + idx + '_id',
                    dropdownId: 'drug_' + idx + '_dropdown',
                    searchInputId: 'drug_' + idx + '_search',
                    optionsContainerId: 'drug_' + idx + '_options',
                    displayTextId: 'drug_' + idx + '_text',
                    options: drugOptions,
                    onSelect: function(opt) {
                        if (opt && opt.id) {
                            qtyInput.max = opt.qty;
                            unitLabel.textContent = opt.unit;
                            validateQty(qtyInput, qtyWarning, opt.qty);
                        } else {
                            unitLabel.textContent = 'Unit';
                        }
                    }
                });

                qtyInput.addEventListener('input', function() {
                    const hiddenId = document.getElementById('drug_' + idx + '_id');
                    const selected = drugOptions.find(o => o.id == hiddenId.value);
                    if (selected) {
                        validateQty(this, qtyWarning, selected.qty);
                    }
                });

                removeBtn.addEventListener('click', function() {
                    if (container.querySelectorAll('.item-row').length > 1) {
                        row.classList.add('scale-95', 'opacity-0');
                        setTimeout(() => row.remove(), 200);
                    }
                });
            }

            function addItem() {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.item-row');
                const idx = index;
                
                row.querySelectorAll('[id]').forEach(el => { el.id = el.id.replace('INDEX', idx); });
                row.querySelectorAll('[name]').forEach(el => { el.name = el.name.replace('INDEX', idx); });

                // CRITICAL: Append to document BEFORE initializing searchable select
                container.appendChild(row);
                const actualRow = container.lastElementChild;
                
                setupRow(actualRow, idx);
                index++;
            }

            function validateQty(input, warning, max) {
                const val = parseInt(input.value);
                if (val > max) {
                    warning.classList.remove('hidden');
                    input.classList.add('border-danger', 'text-danger');
                } else {
                    warning.classList.add('hidden');
                    input.classList.remove('border-danger', 'text-danger');
                }
            }

            // Setup existing rows
            container.querySelectorAll('.item-row').forEach((row, idx) => setupRow(row, idx));

            addBtn.addEventListener('click', addItem);
        });
    </script>
@endsection

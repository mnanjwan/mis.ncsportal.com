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

    if (typeof window.createSearchableSelect !== 'function') return;

    window.createSearchableSelect({
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

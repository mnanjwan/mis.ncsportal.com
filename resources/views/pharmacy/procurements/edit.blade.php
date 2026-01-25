@extends('layouts.app')

@section('title', 'Edit Procurement')
@section('page-title', 'Edit Procurement')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.procurements.index') }}" class="text-secondary-foreground hover:text-primary">Procurements</a>
    <span>/</span>
    <span class="text-secondary-foreground">Edit</span>
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

        <form method="POST" action="{{ route('pharmacy.procurements.update', $procurement->id) }}" id="procurementForm">
            @csrf
            @method('PUT')

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Edit Procurement Draft</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Notes (Optional)</label>
                            <textarea name="notes" class="kt-input" rows="3" placeholder="Add any notes for this procurement...">{{ old('notes', $procurement->notes) }}</textarea>
                        </div>

                        <div>
                            <label class="kt-label">Procurement Items</label>
                            <div id="itemsContainer" class="flex flex-col gap-3">
                                <!-- Existing items will be loaded here -->
                            </div>
                            <button type="button" id="addItemBtn" class="kt-btn kt-btn-light mt-3">
                                <i class="ki-filled ki-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end gap-3">
                    <a href="{{ route('pharmacy.procurements.show', $procurement->id) }}" class="kt-btn kt-btn-light">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Update Draft</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Item Row Template -->
    <template id="itemRowTemplate">
        <div class="item-row flex flex-wrap items-start gap-3 p-3 bg-muted/50 rounded-lg border border-input">
            <div class="flex-grow min-w-[200px]">
                <label class="kt-label text-xs">Drug Name *</label>
                <input type="text" name="items[INDEX][drug_name]" class="kt-input kt-input-sm drug-name-input" placeholder="e.g., Paracetamol 500mg" required>
            </div>
            <div class="w-32">
                <label class="kt-label text-xs">Quantity *</label>
                <input type="number" name="items[INDEX][quantity]" class="kt-input kt-input-sm quantity-input" placeholder="Qty" min="1" required>
            </div>
            <div class="w-32">
                <label class="kt-label text-xs">Unit</label>
                <select name="items[INDEX][unit]" class="kt-input kt-input-sm unit-select">
                    <option value="tablets">Tablets</option>
                    <option value="capsules">Capsules</option>
                    <option value="bottles">Bottles</option>
                    <option value="vials">Vials</option>
                    <option value="ampoules">Ampoules</option>
                    <option value="sachets">Sachets</option>
                    <option value="tubes">Tubes</option>
                    <option value="packs">Packs</option>
                    <option value="units">Units</option>
                </select>
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
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemRowTemplate');
    const addBtn = document.getElementById('addItemBtn');
    let itemIndex = 0;

    // Existing items data
    const existingItems = @json($procurement->items->map(fn($i) => [
        'drug_name' => $i->drug_name ?? ($i->drug ? $i->drug->name : ''),
        'quantity' => $i->quantity_requested,
        'unit' => $i->unit_of_measure ?? ($i->drug ? $i->drug->unit_of_measure : 'units')
    ]));

    function addItem(drugName = '', quantity = '', unit = 'tablets') {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.item-row');
        
        // Update name attributes with current index
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace('INDEX', itemIndex);
        });

        // Set values if provided
        if (drugName) {
            row.querySelector('.drug-name-input').value = drugName;
        }
        if (quantity) {
            row.querySelector('.quantity-input').value = quantity;
        }
        if (unit) {
            row.querySelector('.unit-select').value = unit;
        }

        // Add remove handler
        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(clone);
        itemIndex++;
    }

    addBtn.addEventListener('click', () => addItem());

    // Load existing items
    if (existingItems.length > 0) {
        existingItems.forEach(item => {
            addItem(item.drug_name, item.quantity, item.unit);
        });
    } else {
        addItem();
    }
});
</script>
@endpush

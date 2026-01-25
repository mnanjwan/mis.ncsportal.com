@extends('layouts.app')

@section('title', 'Create Requisition')
@section('page-title', 'Create Requisition')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.requisitions.index') }}" class="text-secondary-foreground hover:text-primary">Requisitions</a>
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

        <form method="POST" action="{{ route('pharmacy.requisitions.store') }}" id="requisitionForm">
            @csrf

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">New Requisition Draft</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Notes (Optional)</label>
                            <textarea name="notes" class="kt-input" rows="3" placeholder="Add any notes for this requisition...">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label class="kt-label">Requisition Items</label>
                            @if($drugs->count() > 0)
                                <p class="text-xs text-secondary-foreground mb-3">Available stock at Central Store is shown for reference.</p>
                                <div id="itemsContainer" class="flex flex-col gap-3">
                                    <!-- Items will be added here dynamically -->
                                </div>
                                <button type="button" id="addItemBtn" class="kt-btn kt-btn-light mt-3">
                                    <i class="ki-filled ki-plus"></i> Add Item
                                </button>
                            @else
                                <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg text-center">
                                    <i class="ki-filled ki-information text-2xl text-warning mb-2"></i>
                                    <p class="text-sm text-secondary-foreground">No drugs available in the catalog yet.</p>
                                    <p class="text-xs text-muted-foreground mt-1">Drugs are added when procurements are received at the Central Medical Store.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end gap-3">
                    <a href="{{ route('pharmacy.requisitions.index') }}" class="kt-btn kt-btn-light">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Create Draft</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Item Row Template -->
    <template id="itemRowTemplate">
        <div class="item-row flex items-start gap-3 p-3 bg-muted/50 rounded-lg border border-input">
            <div class="flex-grow">
                <select name="items[INDEX][drug_id]" class="kt-input kt-input-sm drug-select" required>
                    <option value="">Select Drug</option>
                    @foreach($drugs as $drug)
                        @php
                            $centralQty = isset($centralStock[$drug->id]) ? $centralStock[$drug->id]->sum('quantity') : 0;
                        @endphp
                        <option value="{{ $drug->id }}" data-stock="{{ $centralQty }}">
                            {{ $drug->name }} ({{ $drug->unit_of_measure }}) - Central: {{ number_format($centralQty) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <input type="number" name="items[INDEX][quantity]" class="kt-input kt-input-sm" placeholder="Qty" min="1" required>
            </div>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-light kt-btn-icon remove-item-btn">
                <i class="ki-filled ki-trash text-danger"></i>
            </button>
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

    function addItem() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.item-row');
        
        // Update name attributes with current index
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace('INDEX', itemIndex);
        });

        // Add remove handler
        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(clone);
        itemIndex++;
    }

    addBtn.addEventListener('click', addItem);

    // Add first item by default
    addItem();
});
</script>
@endpush

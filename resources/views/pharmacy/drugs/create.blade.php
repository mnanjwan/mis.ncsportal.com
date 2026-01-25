@extends('layouts.app')

@section('title', 'Add Drug')
@section('page-title', 'Add Drug to Catalog')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.drugs.index') }}" class="text-secondary-foreground hover:text-primary">Drugs</a>
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
                    <h3 class="kt-card-title">Drug Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Drug Name *</label>
                            <input type="text" name="name" class="kt-input" value="{{ old('name') }}" placeholder="e.g., Paracetamol 500mg" required>
                        </div>

                        <div>
                            <label class="kt-label">Description (Optional)</label>
                            <textarea name="description" class="kt-input" rows="3" placeholder="Enter drug description...">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="kt-label">Unit of Measure *</label>
                                <select name="unit_of_measure" class="kt-input" required>
                                    <option value="">Select Unit</option>
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
                    <button type="submit" class="kt-btn kt-btn-primary">Add Drug</button>
                </div>
            </div>
        </form>
    </div>
@endsection

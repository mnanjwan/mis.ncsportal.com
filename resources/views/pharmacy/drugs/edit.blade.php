@extends('layouts.app')

@section('title', 'Edit Drug')
@section('page-title', 'Edit Drug')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.drugs.index') }}" class="text-secondary-foreground hover:text-primary">Drugs</a>
    <span>/</span>
    <span class="text-secondary-foreground">Edit</span>
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

        <form method="POST" action="{{ route('pharmacy.drugs.update', $drug->id) }}">
            @csrf
            @method('PUT')

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Drug Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-5">
                        <div>
                            <label class="kt-label">Drug Name *</label>
                            <input type="text" name="name" class="kt-input" value="{{ old('name', $drug->name) }}" required>
                        </div>

                        <div>
                            <label class="kt-label">Description (Optional)</label>
                            <textarea name="description" class="kt-input" rows="3">{{ old('description', $drug->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="kt-label">Unit of Measure *</label>
                                <select name="unit_of_measure" class="kt-input" required>
                                    <option value="">Select Unit</option>
                                    @foreach(['tablets', 'capsules', 'bottles', 'vials', 'ampoules', 'sachets', 'tubes', 'units', 'packs'] as $unit)
                                        <option value="{{ $unit }}" {{ old('unit_of_measure', $drug->unit_of_measure) === $unit ? 'selected' : '' }}>{{ ucfirst($unit) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="kt-label">Category (Optional)</label>
                                <input type="text" name="category" class="kt-input" value="{{ old('category', $drug->category) }}" 
                                       placeholder="e.g., Analgesics, Antibiotics" list="categories">
                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $drug->is_active) ? 'checked' : '' }} class="kt-checkbox">
                                <span class="text-sm">Active (Drug can be used in procurements and requisitions)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="kt-card-footer flex justify-end gap-3">
                    <a href="{{ route('pharmacy.drugs.index') }}" class="kt-btn kt-btn-light">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Update Drug</button>
                </div>
            </div>
        </form>
    </div>
@endsection

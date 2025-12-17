@extends('layouts.app')

@section('title', isset($criterion) ? 'Edit Promotion Criteria' : 'Create Promotion Criteria')
@section('page-title', isset($criterion) ? 'Edit Promotion Criteria' : 'Create Promotion Criteria')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.promotion-criteria') }}">Promotion Criteria</a>
    <span>/</span>
    <span class="text-primary">{{ isset($criterion) ? 'Edit' : 'Create' }}</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">{{ isset($criterion) ? 'Edit Promotion Criteria' : 'Create Promotion Criteria' }}</h3>
        </div>
        <div class="kt-card-content">
            <form action="{{ isset($criterion) ? route('hrd.promotion-criteria.update', $criterion->id) : route('hrd.promotion-criteria.store') }}" 
                  method="POST" 
                  class="space-y-6">
                @csrf
                @if(isset($criterion))
                    @method('PUT')
                @endif

                <!-- Rank -->
                <div class="space-y-2">
                    <label for="rank" class="block text-sm font-medium text-foreground">
                        Rank <span class="text-danger">*</span>
                    </label>
                    <select name="rank" 
                            id="rank" 
                            class="kt-input @error('rank') kt-input-error @enderror"
                            required>
                        <option value="">Select Rank</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank }}" 
                                    {{ (old('rank', $criterion->rank ?? '') === $rank) ? 'selected' : '' }}>
                                {{ $rank }}
                            </option>
                        @endforeach
                    </select>
                    @error('rank')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Select the rank for which you want to set promotion eligibility criteria.
                    </p>
                </div>

                <!-- Years in Rank Required -->
                <div class="space-y-2">
                    <label for="years_in_rank_required" class="block text-sm font-medium text-foreground">
                        Years in Rank Required <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           name="years_in_rank_required" 
                           id="years_in_rank_required"
                           step="0.01"
                           min="0"
                           max="50"
                           value="{{ old('years_in_rank_required', $criterion->years_in_rank_required ?? '') }}"
                           class="kt-input @error('years_in_rank_required') kt-input-error @enderror"
                           placeholder="e.g., 3.00"
                           required>
                    @error('years_in_rank_required')
                        <p class="text-sm text-danger">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-secondary-foreground">
                        Enter the minimum number of years an officer must spend in this rank before being eligible for promotion.
                    </p>
                </div>

                <!-- Is Active -->
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', isset($criterion) ? $criterion->is_active : true) ? 'checked' : '' }}
                               class="kt-checkbox">
                        <span class="text-sm font-medium text-foreground">Active</span>
                    </label>
                    <p class="text-xs text-secondary-foreground">
                        Only active criteria will be used when generating promotion eligibility lists.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <a href="{{ route('hrd.promotion-criteria') }}" class="kt-btn kt-btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ isset($criterion) ? 'Update Criteria' : 'Create Criteria' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


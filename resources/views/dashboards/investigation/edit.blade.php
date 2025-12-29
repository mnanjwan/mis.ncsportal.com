@extends('layouts.app')

@section('title', 'Update Investigation Status')
@section('page-title', 'Update Investigation Status')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.index') }}">Investigations</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.show', $investigation->id) }}">Details</a>
    <span>/</span>
    <span class="text-primary">Update Status</span>
@endsection

@section('content')
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

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Update Investigation Status</h3>
        </div>
        <div class="kt-card-content">
            <!-- Officer Information -->
            <div class="mb-5 p-4 bg-muted/50 rounded-lg border border-input">
                <h4 class="font-semibold mb-3">Officer Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Name:</span>
                        <p class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Service Number:</span>
                        <p class="font-medium">{{ $investigation->officer->service_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Current Status:</span>
                        <p class="font-medium">
                            @if($investigation->status === 'INVITED')
                                <span class="kt-badge kt-badge-info">Invited</span>
                            @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                                <span class="kt-badge kt-badge-warning">Ongoing Investigation</span>
                            @elseif($investigation->status === 'INTERDICTED')
                                <span class="kt-badge kt-badge-danger">Interdicted</span>
                            @elseif($investigation->status === 'SUSPENDED')
                                <span class="kt-badge kt-badge-danger">Suspended</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('investigation.update', $investigation->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid gap-5">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Investigation Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" 
                                class="kt-input w-full @error('status') border-danger @enderror"
                                required>
                            <option value="">Select Status</option>
                            <option value="ONGOING_INVESTIGATION" {{ old('status', $investigation->status) === 'ONGOING_INVESTIGATION' ? 'selected' : '' }}>
                                Ongoing Investigation
                            </option>
                            <option value="INTERDICTED" {{ old('status', $investigation->status) === 'INTERDICTED' ? 'selected' : '' }}>
                                Interdicted
                            </option>
                            <option value="SUSPENDED" {{ old('status', $investigation->status) === 'SUSPENDED' ? 'selected' : '' }}>
                                Suspended
                            </option>
                        </select>
                        <p class="text-xs text-secondary-foreground mt-1">
                            <strong>Note:</strong> Officers with Ongoing Investigation, Interdiction, or Suspension status cannot appear on Promotion Eligibility Lists. Interdicted officers will appear on Accounts unit's interdicted officers list.
                        </p>
                        @error('status')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Investigation Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  rows="4" 
                                  class="kt-input w-full @error('notes') border-danger @enderror"
                                  placeholder="Enter any additional notes about this investigation status change...">{{ old('notes', $investigation->notes) }}</textarea>
                        @error('notes')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check-circle"></i> Update Status
                        </button>
                        <a href="{{ route('investigation.show', $investigation->id) }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



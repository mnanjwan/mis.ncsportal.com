@extends('layouts.app')

@section('title', 'Send Investigation Invitation')
@section('page-title', 'Send Investigation Invitation')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.search') }}">Search Officers</a>
    <span>/</span>
    <span class="text-primary">Send Invitation</span>
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
            <h3 class="kt-card-title">Send Investigation Invitation</h3>
        </div>
        <div class="kt-card-content">
            <!-- Officer Information -->
            <div class="mb-5 p-4 bg-muted/50 rounded-lg border border-input">
                <h4 class="font-semibold mb-3">Officer Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-secondary-foreground">Name:</span>
                        <p class="font-medium">{{ $officer->initials }} {{ $officer->surname }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Service Number:</span>
                        <p class="font-medium">{{ $officer->service_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Rank:</span>
                        <p class="font-medium">{{ $officer->substantive_rank }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-secondary-foreground">Command:</span>
                        <p class="font-medium">{{ $officer->presentStation->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('investigation.store') }}" method="POST">
                @csrf
                <input type="hidden" name="officer_id" value="{{ $officer->id }}">
                
                <div class="grid gap-5">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Investigation Invitation Message <span class="text-danger">*</span>
                        </label>
                        <textarea name="invitation_message" 
                                  rows="6" 
                                  class="kt-input w-full @error('invitation_message') border-danger @enderror"
                                  placeholder="Enter the investigation invitation message that will be sent to the officer..."
                                  required>{{ old('invitation_message') }}</textarea>
                        <p class="text-xs text-secondary-foreground mt-1">
                            This message will be sent to the officer via email and in-app notification.
                        </p>
                        @error('invitation_message')
                            <p class="text-danger text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-send"></i> Send Invitation
                        </button>
                        <a href="{{ route('investigation.search') }}" class="kt-btn kt-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



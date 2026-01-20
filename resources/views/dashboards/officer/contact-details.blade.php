@extends('layouts.app')

@section('title', 'Contact Details')
@section('page-title', 'Contact Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.settings') }}">Settings</a>
    <span>/</span>
    <span class="text-primary">Contact Details</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-alert kt-alert-success">
                <div class="kt-alert-content">
                    <div class="flex items-center gap-2">
                        <i class="ki-filled ki-check-circle" style="color: #28a745; font-size: 20px;"></i>
                        <div>
                            <strong style="color: #28a745 !important; font-weight: 600;">Success!</strong>
                            <span style="color: #155724 !important;">{{ session('success') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="kt-alert kt-alert-danger">
                <div class="kt-alert-content">
                    <strong style="color: #dc3545 !important;">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li style="color: #dc3545 !important;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Update Contact Details</h3>
                <p class="text-sm text-muted mt-1">Update your phone number and addresses.</p>
            </div>
            <div class="kt-card-content">
                @if($officer->quartered)
                    <div class="kt-alert kt-alert-warning mb-5">
                        <i class="ki-filled ki-information"></i>
                        <div>
                            <strong>Quartered Address:</strong> Your residential address is managed automatically from your allocated quarter. You can still update your phone number and permanent home address.
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('officer.settings.contact-details.update') }}" class="flex flex-col gap-5">
                    @csrf

                    <div class="grid lg:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Phone Number <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="phone_number"
                                class="kt-input @error('phone_number') border-danger @enderror"
                                required
                                value="{{ old('phone_number', $officer->phone_number) }}"
                                style="@error('phone_number') border-color: #dc3545 !important; @enderror"
                            >
                            @error('phone_number')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Residential Address <span class="text-danger">*</span></label>
                            <textarea
                                name="residential_address"
                                class="kt-input @error('residential_address') border-danger @enderror"
                                rows="4"
                                required
                                @if($officer->quartered) readonly @endif
                                style="@if($officer->quartered) background-color: rgba(0,0,0,0.03); cursor: not-allowed; @endif @error('residential_address') border-color: #dc3545 !important; @enderror"
                            >{{ old('residential_address', $officer->residential_address) }}</textarea>
                            @error('residential_address')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Permanent Home Address <span class="text-danger">*</span></label>
                            <textarea
                                name="permanent_home_address"
                                class="kt-input @error('permanent_home_address') border-danger @enderror"
                                rows="4"
                                required
                                style="@error('permanent_home_address') border-color: #dc3545 !important; @enderror"
                            >{{ old('permanent_home_address', $officer->permanent_home_address) }}</textarea>
                            @error('permanent_home_address')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                        <a href="{{ route('officer.profile') }}" class="kt-btn kt-btn-secondary">Cancel</a>
                        <button type="submit" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">
                            <i class="ki-filled ki-check" style="color: white;"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


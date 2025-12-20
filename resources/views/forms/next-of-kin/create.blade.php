@extends('layouts.app')

@section('title', 'Add Next of KIN')
@section('page-title', 'Add Next of KIN')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.next-of-kin.index') }}">Next of KIN</a>
    <span>/</span>
    <span class="text-primary">Add</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-danger text-xl mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-danger mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-danger space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Add Next of KIN Form -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Add Next of KIN</h3>
            </div>
            <div class="kt-card-content">
                <form action="{{ route('officer.next-of-kin.store') }}" method="POST" id="addForm">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-foreground mb-2">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter full name"
                                   required
                                   maxlength="255">
                        </div>

                        <!-- Relationship -->
                        <div>
                            <label for="relationship" class="block text-sm font-medium text-foreground mb-2">
                                Relationship <span class="text-danger">*</span>
                            </label>
                            <select id="relationship" 
                                    name="relationship" 
                                    class="kt-input w-full" 
                                    required>
                                <option value="">Select Relationship</option>
                                <option value="Spouse" {{ old('relationship') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                <option value="Father" {{ old('relationship') == 'Father' ? 'selected' : '' }}>Father</option>
                                <option value="Mother" {{ old('relationship') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                <option value="Brother" {{ old('relationship') == 'Brother' ? 'selected' : '' }}>Brother</option>
                                <option value="Sister" {{ old('relationship') == 'Sister' ? 'selected' : '' }}>Sister</option>
                                <option value="Son" {{ old('relationship') == 'Son' ? 'selected' : '' }}>Son</option>
                                <option value="Daughter" {{ old('relationship') == 'Daughter' ? 'selected' : '' }}>Daughter</option>
                                <option value="Uncle" {{ old('relationship') == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                <option value="Aunt" {{ old('relationship') == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                <option value="Other" {{ old('relationship') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-foreground mb-2">
                                Phone Number
                            </label>
                            <input type="text" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="{{ old('phone_number') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter phone number"
                                   maxlength="20">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   class="kt-input w-full" 
                                   placeholder="Enter email address"
                                   maxlength="255">
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-foreground mb-2">
                                Address
                            </label>
                            <textarea id="address" 
                                      name="address" 
                                      rows="4"
                                      class="kt-input w-full"
                                      placeholder="Enter address">{{ old('address') }}</textarea>
                        </div>

                        <!-- Is Primary -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="is_primary" 
                                       id="is_primary" 
                                       value="1" 
                                       class="kt-checkbox"
                                       {{ old('is_primary') ? 'checked' : '' }}>
                                <span class="text-sm">Set as Primary Next of KIN</span>
                            </label>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-info text-xl mt-0.5"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-info mb-1">Important Information</p>
                                        <ul class="list-disc list-inside text-xs text-secondary-foreground space-y-1">
                                            <li>Your request will be reviewed by the Welfare Section</li>
                                            <li>You will be notified once your request is processed</li>
                                            <li>Name and Relationship are required fields</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('officer.next-of-kin.index') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

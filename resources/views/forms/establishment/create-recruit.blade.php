@extends('layouts.app')

@section('title', 'Add New Recruit')
@section('page-title', 'Add New Recruit')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.dashboard') }}">Establishment</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('establishment.new-recruits') }}">New Recruits</a>
    <span>/</span>
    <span class="text-primary">Add New Recruit</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Add New Recruit</h3>
            </div>
            <div class="kt-card-content">
                @if(session('success'))
                    <div class="kt-card bg-success/10 border border-success/20 mb-5">
                        <div class="kt-card-content p-4">
                            <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
                        <div class="kt-card-content p-4">
                            <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('establishment.new-recruits.store') }}" method="POST">
                    @csrf

                    <div class="flex flex-col gap-5">
                        <!-- Appointment Number -->
                        <div>
                            <label for="appointment_number" class="block text-sm font-medium text-foreground mb-2">
                                Appointment Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="appointment_number" 
                                   name="appointment_number" 
                                   value="{{ old('appointment_number') }}"
                                   class="kt-input w-full" 
                                   required>
                            <p class="text-xs text-secondary-foreground mt-1">
                                Unique appointment number assigned by Unit
                            </p>
                        </div>

                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="initials" class="block text-sm font-medium text-foreground mb-2">
                                    Initials <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="initials" 
                                       name="initials" 
                                       value="{{ old('initials') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>

                            <div>
                                <label for="surname" class="block text-sm font-medium text-foreground mb-2">
                                    Surname <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="surname" 
                                       name="surname" 
                                       value="{{ old('surname') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="sex" class="block text-sm font-medium text-foreground mb-2">
                                    Sex <span class="text-danger">*</span>
                                </label>
                                <select id="sex" name="sex" class="kt-input w-full" required>
                                    <option value="">Select</option>
                                    <option value="M" {{ old('sex') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('sex') == 'F' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-foreground mb-2">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth') }}"
                                       class="kt-input w-full" 
                                       required
                                       max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                            </div>
                        </div>

                        <!-- Employment Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="date_of_first_appointment" class="block text-sm font-medium text-foreground mb-2">
                                    Date of First Appointment <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       id="date_of_first_appointment" 
                                       name="date_of_first_appointment" 
                                       value="{{ old('date_of_first_appointment') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>

                            <div>
                                <label for="substantive_rank" class="block text-sm font-medium text-foreground mb-2">
                                    Substantive Rank <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="substantive_rank" 
                                       name="substantive_rank" 
                                       value="{{ old('substantive_rank') }}"
                                       class="kt-input w-full" 
                                       required>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                    Email (Personal) <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       class="kt-input w-full" 
                                       required>
                                <p class="text-xs text-secondary-foreground mt-1">
                                    Personal email for onboarding
                                </p>
                            </div>

                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-foreground mb-2">
                                    Phone Number
                                </label>
                                <input type="text" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       value="{{ old('phone_number') }}"
                                       class="kt-input w-full">
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="kt-card bg-info/10 border border-info/20">
                            <div class="kt-card-content p-4">
                                <p class="text-sm text-secondary-foreground">
                                    <strong>Note:</strong> After creating the recruit, an onboarding email will be sent to the provided email address. 
                                    The officer will complete the full onboarding form with all required details.
                                </p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <a href="{{ route('establishment.new-recruits') }}" class="kt-btn kt-btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-check"></i>
                                Create Recruit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

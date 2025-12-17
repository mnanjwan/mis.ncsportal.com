@extends('layouts.app')

@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">System Settings</span>
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

    <!-- System Settings Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">System-Wide Configuration</h3>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-6">
                Manage system-wide parameters and configuration settings. Changes here affect the entire system.
            </p>

            <form action="{{ route('hrd.system-settings.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Retirement Settings -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-foreground border-b border-border pb-2">Retirement Settings</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="settings_retirement_age" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['retirement_age']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[retirement_age]" 
                                   id="settings_retirement_age"
                                   value="{{ old('settings.retirement_age', $settings['retirement_age']->setting_value ?? $settingsConfig['retirement_age']['default']) }}"
                                   min="{{ $settingsConfig['retirement_age']['min'] }}"
                                   max="{{ $settingsConfig['retirement_age']['max'] }}"
                                   class="kt-input @error('settings.retirement_age') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['retirement_age']['description'] }}</p>
                            @error('settings.retirement_age')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_retirement_years_of_service" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['retirement_years_of_service']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[retirement_years_of_service]" 
                                   id="settings_retirement_years_of_service"
                                   value="{{ old('settings.retirement_years_of_service', $settings['retirement_years_of_service']->setting_value ?? $settingsConfig['retirement_years_of_service']['default']) }}"
                                   min="{{ $settingsConfig['retirement_years_of_service']['min'] }}"
                                   max="{{ $settingsConfig['retirement_years_of_service']['max'] }}"
                                   class="kt-input @error('settings.retirement_years_of_service') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['retirement_years_of_service']['description'] }}</p>
                            @error('settings.retirement_years_of_service')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_pre_retirement_leave_months" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['pre_retirement_leave_months']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[pre_retirement_leave_months]" 
                                   id="settings_pre_retirement_leave_months"
                                   value="{{ old('settings.pre_retirement_leave_months', $settings['pre_retirement_leave_months']->setting_value ?? $settingsConfig['pre_retirement_leave_months']['default']) }}"
                                   min="{{ $settingsConfig['pre_retirement_leave_months']['min'] }}"
                                   max="{{ $settingsConfig['pre_retirement_leave_months']['max'] }}"
                                   class="kt-input @error('settings.pre_retirement_leave_months') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['pre_retirement_leave_months']['description'] }}</p>
                            @error('settings.pre_retirement_leave_months')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Leave Settings -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-foreground border-b border-border pb-2">Leave Settings</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="settings_annual_leave_days_gl07_below" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['annual_leave_days_gl07_below']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[annual_leave_days_gl07_below]" 
                                   id="settings_annual_leave_days_gl07_below"
                                   value="{{ old('settings.annual_leave_days_gl07_below', $settings['annual_leave_days_gl07_below']->setting_value ?? $settingsConfig['annual_leave_days_gl07_below']['default']) }}"
                                   min="{{ $settingsConfig['annual_leave_days_gl07_below']['min'] }}"
                                   max="{{ $settingsConfig['annual_leave_days_gl07_below']['max'] }}"
                                   class="kt-input @error('settings.annual_leave_days_gl07_below') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['annual_leave_days_gl07_below']['description'] }}</p>
                            @error('settings.annual_leave_days_gl07_below')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_annual_leave_days_gl08_above" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['annual_leave_days_gl08_above']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[annual_leave_days_gl08_above]" 
                                   id="settings_annual_leave_days_gl08_above"
                                   value="{{ old('settings.annual_leave_days_gl08_above', $settings['annual_leave_days_gl08_above']->setting_value ?? $settingsConfig['annual_leave_days_gl08_above']['default']) }}"
                                   min="{{ $settingsConfig['annual_leave_days_gl08_above']['min'] }}"
                                   max="{{ $settingsConfig['annual_leave_days_gl08_above']['max'] }}"
                                   class="kt-input @error('settings.annual_leave_days_gl08_above') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['annual_leave_days_gl08_above']['description'] }}</p>
                            @error('settings.annual_leave_days_gl08_above')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_annual_leave_max_applications" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['annual_leave_max_applications']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[annual_leave_max_applications]" 
                                   id="settings_annual_leave_max_applications"
                                   value="{{ old('settings.annual_leave_max_applications', $settings['annual_leave_max_applications']->setting_value ?? $settingsConfig['annual_leave_max_applications']['default']) }}"
                                   min="{{ $settingsConfig['annual_leave_max_applications']['min'] }}"
                                   max="{{ $settingsConfig['annual_leave_max_applications']['max'] }}"
                                   class="kt-input @error('settings.annual_leave_max_applications') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['annual_leave_max_applications']['description'] }}</p>
                            @error('settings.annual_leave_max_applications')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_pass_max_days" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['pass_max_days']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[pass_max_days]" 
                                   id="settings_pass_max_days"
                                   value="{{ old('settings.pass_max_days', $settings['pass_max_days']->setting_value ?? $settingsConfig['pass_max_days']['default']) }}"
                                   min="{{ $settingsConfig['pass_max_days']['min'] }}"
                                   max="{{ $settingsConfig['pass_max_days']['max'] }}"
                                   class="kt-input @error('settings.pass_max_days') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['pass_max_days']['description'] }}</p>
                            @error('settings.pass_max_days')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- RSA PIN Settings -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-foreground border-b border-border pb-2">RSA PIN Settings</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="settings_rsa_pin_prefix" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['rsa_pin_prefix']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="settings[rsa_pin_prefix]" 
                                   id="settings_rsa_pin_prefix"
                                   value="{{ old('settings.rsa_pin_prefix', $settings['rsa_pin_prefix']->setting_value ?? $settingsConfig['rsa_pin_prefix']['default']) }}"
                                   class="kt-input @error('settings.rsa_pin_prefix') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['rsa_pin_prefix']['description'] }}</p>
                            @error('settings.rsa_pin_prefix')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="settings_rsa_pin_length" class="block text-sm font-medium text-foreground">
                                {{ $settingsConfig['rsa_pin_length']['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[rsa_pin_length]" 
                                   id="settings_rsa_pin_length"
                                   value="{{ old('settings.rsa_pin_length', $settings['rsa_pin_length']->setting_value ?? $settingsConfig['rsa_pin_length']['default']) }}"
                                   min="{{ $settingsConfig['rsa_pin_length']['min'] }}"
                                   max="{{ $settingsConfig['rsa_pin_length']['max'] }}"
                                   class="kt-input @error('settings.rsa_pin_length') kt-input-error @enderror"
                                   required>
                            <p class="text-xs text-secondary-foreground">{{ $settingsConfig['rsa_pin_length']['description'] }}</p>
                            @error('settings.rsa_pin_length')
                                <p class="text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-border">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


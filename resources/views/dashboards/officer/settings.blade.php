@extends('layouts.app')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="#">Settings</a>
    <span>/</span>
    <span class="text-primary">Change Password</span>
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

        <!-- Change Password Section -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Change Password</h3>
                <p class="text-sm text-muted mt-1">Update your account password. Make sure to use a strong password.</p>
            </div>
            <div class="kt-card-content">
                <form method="POST" action="{{ route('officer.settings.change-password') }}" id="change-password-form" class="flex flex-col gap-5">
                    @csrf
                    
                    <div class="grid lg:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password"
                                   class="kt-input @error('current_password') border-danger @enderror" 
                                   required
                                   autocomplete="current-password"
                                   value="{{ old('current_password') }}"
                                   style="@error('current_password') border-color: #dc3545 !important; @enderror">
                            @error('current_password')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password"
                                   class="kt-input @error('new_password') border-danger @enderror" 
                                   required
                                   autocomplete="new-password"
                                   style="@error('new_password') border-color: #dc3545 !important; @enderror">
                            @error('new_password')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="kt-form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   name="new_password_confirmation" 
                                   id="new_password_confirmation"
                                   class="kt-input @error('new_password_confirmation') border-danger @enderror" 
                                   required
                                   autocomplete="new-password"
                                   style="@error('new_password_confirmation') border-color: #dc3545 !important; @enderror">
                            @error('new_password_confirmation')
                                <span class="text-sm" style="color: #dc3545 !important; font-weight: 500; display: block; margin-top: 4px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-input">
                        <button type="button" onclick="resetForm()" class="kt-btn kt-btn-secondary">Cancel</button>
                        <button type="submit" class="kt-btn text-white" style="background-color: #068b57; border-color: #068b57;">
                            <i class="ki-filled ki-check" style="color: white;"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('change-password-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const currentPassword = document.getElementById('current_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('new_password_confirmation').value;
                
                // Validate passwords match
                if (newPassword !== confirmPassword) {
                    Swal.fire({
                        title: 'Password Mismatch',
                        text: 'New password and confirmation do not match.',
                        icon: 'error',
                        confirmButtonColor: '#068b57'
                    });
                    return;
                }
                
                // Check if new password is same as current
                if (currentPassword === newPassword) {
                    Swal.fire({
                        title: 'Invalid Password',
                        text: 'New password must be different from your current password.',
                        icon: 'error',
                        confirmButtonColor: '#068b57'
                    });
                    return;
                }
                
                // Show confirmation
                Swal.fire({
                    title: 'Change Password?',
                    text: 'Are you sure you want to change your password? You will need to log in again with your new password.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Change Password',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#068b57',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ki-filled ki-loading"></i> Changing Password...';
                        form.submit();
                    }
                });
            });
        });
        
        function resetForm() {
            document.getElementById('change-password-form').reset();
            // Clear any error styling
            document.querySelectorAll('.border-danger').forEach(el => {
                el.classList.remove('border-danger');
            });
        }
    </script>
    @endpush
@endsection

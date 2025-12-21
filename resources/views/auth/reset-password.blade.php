<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NCS Employee Portal - Reset Password</title>
    <link href="{{ asset('ncs-employee-portal/dist/assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/media/app/favicon.svg') }}" rel="shortcut icon" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('ncs-employee-portal/dist/assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#088a56',
                        'primary-hover': '#066c43',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #333333;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Reset Password Card -->
        <div class="glass-card rounded-md p-8 sm:p-10">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="mx-auto h-20 w-20 bg-white rounded-md shadow-sm flex items-center justify-center mb-6 ring-1 ring-gray-100">
                    <img class="h-12 w-12 object-contain" src="{{ asset('ncs-employee-portal/dist/assets/media/app/portal-logo-circle.svg') }}" alt="Portal Logo">
                </div>
                <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-600 font-medium">Enter your new password</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-100 rounded p-4 flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-red-600 text-xl mt-0.5"></i>
                    <div class="text-sm text-red-600 font-medium">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-100 rounded p-4 flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-green-600 text-xl"></i>
                    <p class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('password.update') }}" method="POST" id="reset_password_form">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        New Password
                    </label>
                    <div class="relative rounded shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ki-filled ki-lock text-gray-400 text-lg"></i>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium" 
                            placeholder="Enter new password">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <button type="button" id="toggle-password" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors cursor-pointer">
                                <i class="ki-filled ki-eye text-lg" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirm New Password
                    </label>
                    <div class="relative rounded shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ki-filled ki-lock text-gray-400 text-lg"></i>
                        </div>
                        <input type="password" name="password_confirmation" id="password_confirmation" required 
                            class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium" 
                            placeholder="Confirm new password">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <button type="button" id="toggle-password-confirm" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors cursor-pointer">
                                <i class="ki-filled ki-eye text-lg" id="eye-icon-confirm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-btn" 
                    class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform active:scale-[0.98]">
                    <span class="btn-text mr-2">Reset Password</span>
                    <i class="ki-filled ki-arrow-right text-lg btn-icon"></i>
                </button>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">
                        <i class="ki-filled ki-arrow-left mr-1"></i>
                        Back to Login
                    </a>
                </div>
            </form>

            <!-- Footer Info -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <div class="flex justify-center">
                    <span class="inline-flex items-center px-4 py-2 rounded-sm bg-gray-50 border border-gray-100 text-xs font-medium text-gray-600">
                        <i class="ki-filled ki-shield-tick text-primary mr-2"></i>
                        Secure Official Portal
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="text-center mt-6">
            <p class="text-xs text-white/80 font-medium tracking-wide">
                &copy; {{ date('Y') }} Nigeria Customs Service. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        // Password toggle
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('ki-eye');
                eyeIcon.classList.add('ki-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('ki-eye-slash');
                eyeIcon.classList.add('ki-eye');
            }
        });

        // Confirm password toggle
        document.getElementById('toggle-password-confirm').addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirmation');
            const eyeIcon = document.getElementById('eye-icon-confirm');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('ki-eye');
                eyeIcon.classList.add('ki-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('ki-eye-slash');
                eyeIcon.classList.add('ki-eye');
            }
        });
        
        // Form submission
        document.getElementById('reset_password_form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnIcon = submitBtn.querySelector('.btn-icon');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Resetting...';
            btnIcon.classList.remove('ki-arrow-right');
            btnIcon.classList.add('ki-loading', 'animate-spin');
        });
    </script>
</body>
</html>


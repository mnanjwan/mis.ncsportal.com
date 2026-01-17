<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NCS Employee Portal - Sign In</title>
    <link href="{{ asset('logo.jpg') }}" rel="icon" type="image/jpeg" />
    <link href="{{ asset('logo.jpg') }}" rel="shortcut icon" />
    
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
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background pattern */
        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(8, 138, 86, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(8, 138, 86, 0.1) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        /* Decorative side panels */
        .side-panel {
            position: relative;
            background: linear-gradient(180deg, rgba(8, 138, 86, 0.08) 0%, rgba(8, 138, 86, 0.03) 100%);
            border-right: 1px solid rgba(8, 138, 86, 0.2);
            overflow: hidden;
        }
        
        .side-panel-right {
            border-right: none;
            border-left: 1px solid rgba(8, 138, 86, 0.2);
        }
        
        .decorative-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.4;
        }
        
        .shape {
            position: absolute;
            border: 2px solid rgba(8, 138, 86, 0.3);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .shape-3 {
            width: 100px;
            height: 100px;
            top: 60%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .shape-4 {
            width: 180px;
            height: 180px;
            bottom: 10%;
            left: 5%;
            animation-delay: 1s;
        }
        
        .shape-5 {
            width: 120px;
            height: 120px;
            top: 30%;
            right: 20%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        
        /* NCS branding text */
        .ncs-branding {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .ncs-branding strong {
            color: rgba(8, 138, 86, 0.8);
            font-size: 16px;
        }
        
        /* Mission statement panel */
        .mission-panel {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            z-index: 10;
        }
        
        .mission-panel h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #088a56;
            line-height: 1.2;
        }
        
        .mission-panel p {
            font-size: 15px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
        }
        
        .features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .features-list li i {
            color: #088a56;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Grid pattern overlay */
        .grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(8, 138, 86, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(8, 138, 86, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
        }
        
        /* Tooltip styling */
        .tooltip-container {
            position: relative;
        }
        
        .tooltip-content {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: calc(100% + 12px);
            min-width: 320px;
            max-width: 90vw;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 50;
            pointer-events: none;
        }
        
        .tooltip-container:hover .tooltip-content,
        .tooltip-container:focus-within .tooltip-content {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        .tooltip-arrow {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 100%;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 8px solid #fcd34d;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .side-panel {
                display: none;
            }
        }
        
        @media (max-width: 640px) {
            .tooltip-content {
                min-width: 280px;
                left: auto;
                right: 0;
                transform: none;
            }
            
            .tooltip-arrow {
                left: auto;
                right: 24px;
                transform: none;
            }
        }
    </style>
</head>
<body class="h-full relative overflow-hidden">
    <!-- Background pattern -->
    <div class="bg-pattern"></div>
    
    <div class="h-full flex">
        <!-- Left Side Panel -->
        <div class="hidden lg:flex lg:w-2/5 side-panel relative">
            <div class="grid-overlay"></div>
            <div class="decorative-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
            </div>
            <div class="mission-panel">
                <h3>Nigeria Customs Service</h3>
                <p>Empowering our workforce with innovative digital solutions for efficient service delivery and operational excellence.</p>
                <ul class="features-list">
                    <li><i class="ki-filled ki-verify"></i>Secure & Encrypted</li>
                    <li><i class="ki-filled ki-abstract-26"></i>Real-time Updates</li>
                    <li><i class="ki-filled ki-chart-simple"></i>Comprehensive Dashboards</li>
                    <li><i class="ki-filled ki-devices-2"></i>Mobile Responsive</li>
                </ul>
            </div>
        </div>
        
        <!-- Center Login Card -->
        <div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-md w-full space-y-8">
        <!-- Login Card -->
        <div class="glass-card rounded-md p-8 sm:p-10">
            <!-- Header -->
            <div class="text-center mb-10 relative">
                <div class="mx-auto h-20 w-20 bg-white rounded-md shadow-sm flex items-center justify-center mb-6 ring-1 ring-gray-100">
                    <img class="h-12 w-12 object-contain" src="{{ asset('logo.jpg') }}" alt="Portal Logo">
                </div>
                <div class="flex items-center justify-center gap-3">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome Back</h2>
                    <!-- Security Reminder Tooltip -->
                    <div class="tooltip-container">
                        <button type="button" class="text-amber-500 hover:text-amber-600 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 rounded-full p-1" aria-label="Security Reminder">
                            <i class="ki-filled ki-information-2 text-xl"></i>
                        </button>
                        <!-- Tooltip Content -->
                        <div class="tooltip-content">
                            <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-4 shadow-xl">
                                <h4 class="text-sm font-semibold text-amber-900 mb-2">Security Reminder</h4>
                                <ul class="text-xs text-amber-800 space-y-1.5 text-left">
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 mt-0.5 flex-shrink-0">•</span>
                                        <span><strong>Never share your password</strong> with anyone. NCS staff will never ask for your password.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 mt-0.5 flex-shrink-0">•</span>
                                        <span><strong>Beware of phishing attempts.</strong> Always verify you're on the official NCS portal before entering credentials.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 mt-0.5 flex-shrink-0">•</span>
                                        <span><strong>Logging in from public networks</strong> may pose a security risk. Use trusted networks when possible.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 mt-0.5 flex-shrink-0">•</span>
                                        <span>Enable <strong>Two-Factor Authentication (2FA)</strong> in your settings for enhanced security.</span>
                                    </li>
                                </ul>
                                <!-- Tooltip Arrow -->
                                <div class="tooltip-arrow"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-600 font-medium">Sign in to the NCS Employee Portal</p>
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-100 rounded p-4 flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-red-600 text-xl mt-0.5"></i>
                    <p class="text-sm text-red-600 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-100 rounded p-4 flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-green-600 text-xl"></i>
                    <p class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                </div>
            @endif


            <form class="space-y-6" action="{{ route('login') }}" method="POST" id="sign_in_form">
                @csrf
                
                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        Service Number or Email
                    </label>
                    <div class="relative rounded shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ki-filled ki-user text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" name="username" id="username" required 
                            class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium" 
                            placeholder="e.g. NCS12345 or officer@ncs.gov.ng"
                            value="{{ old('username') }}">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">
                            Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative rounded shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ki-filled ki-lock text-gray-400 text-lg"></i>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium" 
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <button type="button" id="toggle-password" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors cursor-pointer">
                                <i class="ki-filled ki-eye text-lg" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}
                        class="h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded cursor-pointer">
                    <label for="remember" class="ml-3 block text-sm font-medium text-gray-600 cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-btn" 
                    class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform active:scale-[0.98]">
                    <span class="btn-text mr-2">Sign In</span>
                    <i class="ki-filled ki-arrow-right text-lg btn-icon"></i>
                </button>
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
        <div class="text-center mt-6 px-4 py-4 space-y-1">
            <p class="text-xs text-white/80 font-medium tracking-wide">
                &copy; 2025 Nigeria Customs Service. All rights reserved.
            </p>
            <p class="text-xs text-white/60 font-medium tracking-wide">
                Designed by NCS ICT - MOD
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
        
        // Form submission
        document.getElementById('sign_in_form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnIcon = submitBtn.querySelector('.btn-icon');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Signing in...';
            btnIcon.classList.remove('ki-arrow-right');
            btnIcon.classList.add('ki-loading', 'animate-spin');
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NCS Employee Portal - Officer Sign Up</title>
    <link href="{{ asset('logo.jpg') }}" rel="icon" type="image/jpeg" />
    <link href="{{ asset('logo.jpg') }}" rel="shortcut icon" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('ncs-employee-portal/dist/assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#088a56', 'primary-hover': '#066c43' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%); position: relative; overflow: hidden; }
        .bg-pattern { position: absolute; inset: 0; background-image: radial-gradient(circle at 20% 50%, rgba(8, 138, 86, 0.15) 0%, transparent 50%), radial-gradient(circle at 80% 50%, rgba(8, 138, 86, 0.1) 0%, transparent 50%); animation: pulse 8s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }
        .side-panel { position: relative; background: linear-gradient(180deg, rgba(8, 138, 86, 0.08) 0%, rgba(8, 138, 86, 0.03) 100%); border-right: 1px solid rgba(8, 138, 86, 0.2); overflow: hidden; }
        .mission-panel { padding: 40px; display: flex; flex-direction: column; justify-content: center; color: white; position: relative; z-index: 10; }
        .mission-panel h3 { font-size: 28px; font-weight: 700; margin-bottom: 20px; color: #088a56; line-height: 1.2; }
        .mission-panel p { font-size: 15px; line-height: 1.8; color: rgba(255, 255, 255, 0.8); }
        .glass-card { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        @media (max-width: 1024px) { .side-panel { display: none; } }
    </style>
</head>
<body class="h-full relative overflow-hidden">
    <div class="bg-pattern"></div>
    <div class="h-full flex">
        <div class="hidden lg:flex lg:w-2/5 side-panel relative">
            <div class="mission-panel">
                <h3>Nigeria Customs Service</h3>
                <p>Register for the NCS Employee Portal. Enter your service number and email to start onboarding. No link will be sent by email — you continue straight to the form.</p>
            </div>
        </div>
        <div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-md w-full space-y-8">
                <div class="glass-card rounded-md p-8 sm:p-10">
                    <div class="text-center mb-8">
                        <div class="mx-auto h-20 w-20 bg-white rounded-md shadow-sm flex items-center justify-center mb-6 ring-1 ring-gray-100">
                            <img class="h-12 w-12 object-contain" src="{{ asset('logo.jpg') }}" alt="Portal Logo">
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Officer Sign Up</h2>
                        <p class="mt-2 text-sm text-gray-600">Start your portal registration</p>
                    </div>

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i class="ki-filled ki-information-2 text-red-600 text-xl mt-0.5 flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
                                    @if(session('error_action_hint'))
                                        <p class="mt-2 text-sm text-red-700">{{ session('error_action_hint') }}</p>
                                    @endif
                                    @if(session('error_action') === 'email_taken' || session('error_action') === 'email_mismatch')
                                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold text-primary hover:text-primary-hover">
                                            <i class="ki-filled ki-arrow-right text-base"></i>
                                            Go to sign in
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-100 rounded p-4 flex items-start gap-3">
                            <i class="ki-filled ki-information-2 text-red-600 text-xl mt-0.5"></i>
                            <ul class="text-sm text-red-600 font-medium list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="space-y-5" action="{{ route('officer-signup.submit') }}" method="POST" id="signup-form">
                        @csrf
                        <div>
                            <label for="service_number" class="block text-sm font-semibold text-gray-700 mb-2">Service Number <span class="text-red-500">*</span></label>
                            <div class="relative rounded shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ki-filled ki-profile-user text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" name="service_number" id="service_number" required
                                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium"
                                    placeholder="e.g., NCS57616"
                                    value="{{ old('service_number') }}">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <div class="relative rounded shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ki-filled ki-sms text-gray-400 text-lg"></i>
                                </div>
                                <input type="email" name="email" id="email" required
                                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium"
                                    placeholder="officer@example.com"
                                    value="{{ old('email') }}">
                            </div>
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                            <div class="relative rounded shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ki-filled ki-user text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" name="name" id="name" required
                                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium"
                                    placeholder="e.g., AB Cabel"
                                    value="{{ old('name') }}">
                            </div>
                        </div>
                        <button type="submit" id="submit-btn"
                            class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                            <span class="btn-text">Continue to onboarding</span>
                            <i class="ki-filled ki-arrow-right text-lg ml-2 btn-icon"></i>
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-primary hover:text-primary-hover">Already have an account? Sign in</a>
                    </div>
                </div>
                <p class="text-center text-xs text-white/60">&copy; {{ date('Y') }} Nigeria Customs Service</p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('signup-form').addEventListener('submit', function() {
            var btn = document.getElementById('submit-btn');
            var text = btn.querySelector('.btn-text');
            var icon = btn.querySelector('.btn-icon');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            if (text) text.textContent = 'Continuing...';
            if (icon) { icon.classList.remove('ki-arrow-right'); icon.classList.add('ki-loading', 'animate-spin'); }
        });
    </script>
</body>
</html>

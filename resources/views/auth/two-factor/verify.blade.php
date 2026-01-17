<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication - NCS Employee Portal</title>
    <link href="{{ asset('logo.jpg') }}" rel="icon" type="image/jpeg" />
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="glass-card rounded-md p-8 sm:p-10">
                <div class="text-center mb-8">
                    <div class="mx-auto h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <i class="ki-filled ki-shield-tick text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Two-Factor Authentication</h2>
                    <p class="mt-2 text-sm text-gray-600">Enter the code from your authenticator app</p>
                </div>

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

                <form action="{{ route('two-factor.verify') }}" method="POST" id="verify_form">
                    @csrf
                    
                    <div>
                        <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                            Authentication Code
                        </label>
                        <div class="relative rounded shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="ki-filled ki-lock text-gray-400 text-lg"></i>
                            </div>
                            <input type="text" 
                                   name="code" 
                                   id="code" 
                                   required 
                                   maxlength="10"
                                   class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium text-center text-2xl tracking-widest" 
                                   placeholder="000000"
                                   autocomplete="off"
                                   autofocus>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Enter the 6-digit code from your authenticator app, or use a recovery code</p>
                    </div>

                    <button type="submit" 
                            id="submit-btn"
                            class="w-full mt-6 flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform active:scale-[0.98]">
                        <span class="btn-text mr-2">Verify</span>
                        <i class="ki-filled ki-arrow-right text-lg btn-icon"></i>
                    </button>

                    <div class="mt-4 text-center">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-primary transition-colors">
                            ← Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus and format code input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        });

        // Form submission
        document.getElementById('verify_form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnIcon = submitBtn.querySelector('.btn-icon');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Verifying...';
            btnIcon.classList.remove('ki-arrow-right');
            btnIcon.classList.add('ki-loading', 'animate-spin');
        });
    </script>
</body>
</html>

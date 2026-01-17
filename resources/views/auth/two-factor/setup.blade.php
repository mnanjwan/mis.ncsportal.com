<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enable Two-Factor Authentication - NCS Employee Portal</title>
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
        <div class="max-w-2xl w-full space-y-8">
            <div class="glass-card rounded-md p-8 sm:p-10">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Enable Two-Factor Authentication</h2>
                    <p class="mt-2 text-sm text-gray-600">Add an extra layer of security to your account</p>
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

                <div class="space-y-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">Step 1: Scan the QR Code</h3>
                        <p class="text-sm text-blue-700 mb-4">Use your authenticator app (Google Authenticator, Authy, etc.) to scan this QR code:</p>
                        <div class="flex justify-center mb-4">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrCodeUrl) }}" 
                                 alt="QR Code" 
                                 class="border-2 border-gray-200 rounded-lg p-2 bg-white">
                        </div>
                        <p class="text-xs text-blue-600 mt-2">Or enter this code manually: <code class="bg-blue-100 px-2 py-1 rounded font-mono text-sm">{{ $secret }}</code></p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-yellow-900 mb-2">Step 2: Enter Verification Code</h3>
                        <p class="text-sm text-yellow-700">Enter the 6-digit code from your authenticator app to verify and enable 2FA:</p>
                    </div>

                    <form action="{{ route('two-factor.enable') }}" method="POST">
                        @csrf
                        <div>
                            <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Verification Code
                            </label>
                            <div class="relative rounded shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ki-filled ki-lock text-gray-400 text-lg"></i>
                                </div>
                                <input type="text" 
                                       name="code" 
                                       id="code" 
                                       required 
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm font-medium text-center text-2xl tracking-widest" 
                                       placeholder="000000"
                                       autocomplete="off"
                                       inputmode="numeric">
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Enter the 6-digit code from your authenticator app</p>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" 
                                    class="flex-1 flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                                <span>Enable 2FA</span>
                            </button>
                            <a href="{{ route('dashboard') }}" 
                               class="flex-1 flex justify-center items-center py-3.5 px-4 border border-gray-300 rounded shadow text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus and format code input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
        });
        codeInput.focus();
    </script>
</body>
</html>

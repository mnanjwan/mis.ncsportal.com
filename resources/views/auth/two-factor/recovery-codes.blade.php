<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recovery Codes - NCS Employee Portal</title>
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
                    <div class="mx-auto h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <i class="ki-filled ki-key text-primary text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Recovery Codes</h2>
                    <p class="mt-2 text-sm text-gray-600">Save these codes in a safe place</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-100 rounded p-4 flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-green-600 text-xl"></i>
                        <p class="text-sm text-green-600 font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="ki-filled ki-information-2 text-yellow-600 text-xl mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold mb-1">Important:</p>
                            <ul class="list-disc list-inside space-y-1 text-yellow-700">
                                <li>These codes can be used to access your account if you lose your device</li>
                                <li>Each code can only be used once</li>
                                <li>Store them in a secure location</li>
                                <li>You can regenerate new codes at any time</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($recoveryCodes as $code)
                            <div class="bg-white border border-gray-300 rounded p-3 font-mono text-sm text-center text-gray-900">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <form action="{{ route('two-factor.regenerate-recovery-codes') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full flex justify-center items-center py-3.5 px-4 border border-gray-300 rounded shadow text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                            Regenerate Codes
                        </button>
                    </form>
                    <a href="{{ route('dashboard') }}" 
                       class="flex-1 flex justify-center items-center py-3.5 px-4 border border-transparent rounded shadow-lg text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                        Continue to Dashboard
                    </a>
                </div>

                <div class="mt-6 text-center">
                    <button onclick="window.print()" 
                            class="text-sm font-semibold text-primary hover:text-primary-hover transition-colors">
                        <i class="ki-filled ki-printer mr-1"></i> Print Codes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style media="print">
        body {
            background: white;
        }
        .glass-card {
            background: white;
            box-shadow: none;
        }
        button, a[href] {
            display: none;
        }
    </style>
</body>
</html>

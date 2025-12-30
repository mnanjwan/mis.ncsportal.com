<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">

<head>
    <title>@yield('title', 'NCS Employee Portal')</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
    <link href="{{ asset('favicon.ico') }}" rel="icon" type="image/x-icon" />
    <link href="{{ asset('favicon.ico') }}" rel="shortcut icon" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite Assets (includes SweetAlert2) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Error messages should be red only when visible (not hidden) */
        .error-message:not(.hidden) {
            color: #dc3545 !important;
        }
        
        /* Laravel validation errors */
        .kt-alert-danger,
        .kt-alert-danger strong,
        .kt-alert-danger li,
        .kt-alert-danger p {
            color: #dc3545 !important;
        }
    </style>

    @stack('styles')
</head>

<body class="antialiased flex h-full text-base text-foreground bg-background">
    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }
            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.add(themeMode);
        }
    </script>
    <!-- End of Theme Mode -->
    
    <!-- Page -->
    <div class="flex grow">
        <!-- Main Content -->
        <div class="flex flex-col grow bg-background">
            <!-- Content -->
            <div class="flex flex-col grow kt-scrollable-y-auto">
                <main class="grow" role="content">
                    <div class="kt-container-fixed py-5">
                        @yield('content')
                    </div>
                </main>
            </div>
            <!-- End of Content -->
        </div>
        <!-- End of Main Content -->
    </div>
    <!-- End of Page -->
    
    <!-- Footer -->
    <footer class="mt-8 pt-6 pb-6 px-4 border-t border-input">
        <div class="kt-container-fixed">
            <div class="flex flex-col items-center justify-center gap-2 text-sm text-secondary-foreground py-4">
                <div>© 2025 Nigeria Customs Service. All rights reserved.</div>
                <div>Designed by NCS ICT - MOD</div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="{{ asset('ncs-employee-portal/dist/assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- End of Scripts -->
    
    @stack('scripts')
</body>

</html>


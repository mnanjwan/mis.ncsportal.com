<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">

<head>
    <title>@yield('title', 'NCS Employee Portal')</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/media/app/favicon.svg') }}" rel="icon" type="image/svg+xml" />
    <link href="{{ asset('ncs-employee-portal/dist/assets/media/app/favicon.svg') }}" rel="shortcut icon" />
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
    
    <!-- Scripts -->
    <script src="{{ asset('ncs-employee-portal/dist/assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- End of Scripts -->
    
    @stack('scripts')
</body>

</html>


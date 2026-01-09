<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">

<head>
    <title>@yield('title', 'NCS Employee Portal')</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
    <link href="{{ asset('logo.jpg') }}" rel="icon" type="image/jpeg" />
    <link href="{{ asset('logo.jpg') }}" rel="shortcut icon" />
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

<body
    class="antialiased flex h-full text-base text-foreground bg-background [--header-height:60px] [--sidebar-width:270px] lg:overflow-hidden bg-mono dark:bg-background">
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
    <!-- Base -->
    <div class="flex grow">
        <!-- Header -->
        <header
            class="flex lg:hidden items-center fixed z-10 top-0 start-0 end-0 shrink-0 bg-mono dark:bg-background h-(--header-height)"
            id="header">
            <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                <a href="{{ route('dashboard') }}">
                    <img class="size-[34px]"
                        src="{{ asset('logo.jpg') }}" />
                </a>
                <button class="kt-btn kt-btn-icon kt-btn-dim hover:text-white -me-2" data-kt-drawer-toggle="#sidebar">
                    <i class="ki-filled ki-menu"></i>
                </button>
            </div>
        </header>
        <!-- End of Header -->
        <!-- Wrapper -->
        <div class="flex flex-col lg:flex-row grow pt-(--header-height) lg:pt-0">
            <!-- Sidebar -->
            @include('components.sidebar')
            <!-- End of Sidebar -->
            <!-- Main -->
            <div class="flex flex-col grow lg:rounded-l-xl bg-background border border-input lg:ms-(--sidebar-width)"
                id="sidebar-container">
                <div class="flex flex-col grow kt-scrollable-y-auto lg:[--kt-scrollbar-width:auto] pt-5"
                    id="scrollable_content">
                    <main class="grow" role="content">
                        <!-- Toolbar -->
                        <div class="pb-5">
                            <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                                <div class="flex flex-col flex-wrap gap-1">
                                    <h1 class="font-medium text-lg text-mono">
                                        @yield('page-title', 'Dashboard')
                                    </h1>
                                    <div class="flex items-center gap-1 text-sm font-normal">
                                        <a class="text-secondary-foreground hover:text-primary"
                                            href="{{ route('dashboard') }}">
                                            Home
                                        </a>
                                        @hasSection('breadcrumbs')
                                            <span>/</span>
                                            @yield('breadcrumbs')
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End of Toolbar -->
                        <!-- Container -->
                        <div class="kt-container-fixed pb-5">
                            @yield('content')
                        </div>
                        <!-- End of Container -->
                    </main>
                </div>
            </div>
            <!-- End of Main -->
        </div>
        <!-- End of Wrapper -->
    </div>
    <!-- End of Base -->
    <!-- Notifications Drawer -->
    @include('components.notifications-drawer')
    <!-- End of Notifications Drawer -->
    <!-- End of Page -->
    <!-- Scripts -->
    <script src="{{ asset('ncs-employee-portal/dist/assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/dist/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- End of Scripts -->
    <script>
        window.API_CONFIG = {
            baseURL: '{{ url('/api/v1') }}',
            token: '{{ auth()->check() ? auth()->user()->createToken('web')->plainTextToken : '' }}'
        };
    </script>
    <script src="{{ asset('ncs-employee-portal/config/api.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/js/auth.js') }}"></script>
    <script src="{{ asset('ncs-employee-portal/js/utils.js') }}"></script>
    <script>
        // Ensure modals with 'hidden' class stay hidden on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.kt-modal.hidden').forEach(function(modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
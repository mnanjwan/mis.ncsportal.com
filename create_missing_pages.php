<?php
/**
 * Script to create all missing frontend pages
 * This generates HTML pages with proper structure, sidebar integration, and API connections
 */

$pages = [
    // Officer pages
    'dashboards/officer/profile.html' => [
        'title' => 'My Profile',
        'role' => 'Officer',
        'type' => 'profile'
    ],
    'dashboards/officer/emoluments-list.html' => [
        'title' => 'My Emoluments',
        'role' => 'Officer',
        'type' => 'list'
    ],
    'dashboards/officer/leave-applications-list.html' => [
        'title' => 'My Leave Applications',
        'role' => 'Officer',
        'type' => 'list'
    ],
    'dashboards/officer/pass-applications-list.html' => [
        'title' => 'My Pass Applications',
        'role' => 'Officer',
        'type' => 'list'
    ],
    
    // HRD pages
    'dashboards/hrd/officers-list.html' => [
        'title' => 'All Officers',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/officer-detail.html' => [
        'title' => 'Officer Details',
        'role' => 'HRD',
        'type' => 'detail'
    ],
    'dashboards/hrd/emolument-timeline.html' => [
        'title' => 'Emolument Timeline',
        'role' => 'HRD',
        'type' => 'form'
    ],
    'dashboards/hrd/staff-orders.html' => [
        'title' => 'Staff Orders',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/movement-orders.html' => [
        'title' => 'Movement Orders',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/promotion-eligibility.html' => [
        'title' => 'Promotion Eligibility',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/retirement-list.html' => [
        'title' => 'Retirement List',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/officer-courses.html' => [
        'title' => 'Officer Courses',
        'role' => 'HRD',
        'type' => 'list'
    ],
    'dashboards/hrd/reports.html' => [
        'title' => 'System Reports',
        'role' => 'HRD',
        'type' => 'reports'
    ],
    
    // Assessor/Validator pages
    'dashboards/assessor/emolument-detail.html' => [
        'title' => 'Emolument Details',
        'role' => 'Assessor',
        'type' => 'detail'
    ],
    'dashboards/validator/emolument-detail.html' => [
        'title' => 'Emolument Details',
        'role' => 'Validator',
        'type' => 'detail'
    ],
    
    // Area Controller pages
    'dashboards/area-controller/emoluments.html' => [
        'title' => 'Emoluments',
        'role' => 'Area Controller',
        'type' => 'list'
    ],
    'dashboards/area-controller/leave-pass.html' => [
        'title' => 'Leave & Pass',
        'role' => 'Area Controller',
        'type' => 'list'
    ],
    'dashboards/area-controller/manning-requests.html' => [
        'title' => 'Manning Requests',
        'role' => 'Area Controller',
        'type' => 'list'
    ],
    
    // DC Admin pages
    'dashboards/dc-admin/leave-pass.html' => [
        'title' => 'Leave & Pass',
        'role' => 'DC Admin',
        'type' => 'list'
    ],
    
    // Accounts pages
    'dashboards/accounts/deceased-officers-list.html' => [
        'title' => 'Deceased Officers',
        'role' => 'Accounts',
        'type' => 'list'
    ],
    
    // Board pages
    'dashboards/board/promotion-eligibility-list.html' => [
        'title' => 'Promotion Eligibility List',
        'role' => 'Board',
        'type' => 'list'
    ],
    
    // Building Unit pages
    'dashboards/building/quarter-allocation.html' => [
        'title' => 'Quarter Allocation',
        'role' => 'Building Unit',
        'type' => 'form'
    ],
    
    // Establishment pages
    'dashboards/establishment/service-numbers.html' => [
        'title' => 'Service Numbers',
        'role' => 'Establishment',
        'type' => 'list'
    ],
    'dashboards/establishment/new-recruits.html' => [
        'title' => 'New Recruits',
        'role' => 'Establishment',
        'type' => 'list'
    ],
    
    // Welfare pages
    'dashboards/welfare/deceased-officer-detail.html' => [
        'title' => 'Deceased Officer Details',
        'role' => 'Welfare',
        'type' => 'detail'
    ],
    
    // Forms
    'forms/manning-level/create.html' => [
        'title' => 'Create Manning Request',
        'role' => 'Staff Officer',
        'type' => 'form'
    ],
    'forms/staff-order/create.html' => [
        'title' => 'Create Staff Order',
        'role' => 'HRD',
        'type' => 'form'
    ],
    'forms/movement-order/create.html' => [
        'title' => 'Create Movement Order',
        'role' => 'HRD',
        'type' => 'form'
    ],
    'forms/promotion/create-eligibility-list.html' => [
        'title' => 'Create Promotion Eligibility List',
        'role' => 'Board',
        'type' => 'form'
    ],
    'forms/retirement/generate-list.html' => [
        'title' => 'Generate Retirement List',
        'role' => 'HRD',
        'type' => 'form'
    ],
    'forms/officer-course/nominate.html' => [
        'title' => 'Nominate Officer for Course',
        'role' => 'HRD',
        'type' => 'form'
    ],
    'forms/quarter/allocate.html' => [
        'title' => 'Allocate Quarter',
        'role' => 'Building Unit',
        'type' => 'form'
    ],
    'forms/deceased-officer/record.html' => [
        'title' => 'Record Deceased Officer',
        'role' => 'HRD',
        'type' => 'form'
    ],
    
    // Chat pages
    'chat/rooms.html' => [
        'title' => 'Chat Rooms',
        'role' => 'Officer',
        'type' => 'list'
    ],
    'chat/room.html' => [
        'title' => 'Chat Room',
        'role' => 'Officer',
        'type' => 'chat'
    ],
];

$baseDir = __DIR__ . '/ncs-employee-portal/';

foreach ($pages as $path => $config) {
    $fullPath = $baseDir . $path;
    $dir = dirname($fullPath);
    
    // Create directory if it doesn't exist
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Skip if file already exists
    if (file_exists($fullPath)) {
        echo "⏭️  Skipping (exists): $path\n";
        continue;
    }
    
    // Generate page content based on type
    $content = generatePageContent($config, $path);
    
    // Write file
    file_put_contents($fullPath, $content);
    echo "✅ Created: $path\n";
}

function generatePageContent($config, $relativePath) {
    $title = $config['title'];
    $role = $config['role'];
    $type = $config['type'];
    
    // Calculate relative path to dist
    $depth = substr_count($relativePath, '/') - 1;
    $distPath = str_repeat('../', $depth) . 'dist';
    $configPath = str_repeat('../', $depth) . 'config';
    $jsPath = str_repeat('../', $depth) . 'js';
    $componentsPath = str_repeat('../', $depth) . 'components';
    
    $html = <<<HTML
<!--
NCS Employee Portal - {$title}
Based on Metronic v9.3.8
-->
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    <title>NCS Employee Portal - {$title}</title>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <link href="{$distPath}/assets/media/app/favicon.ico" rel="shortcut icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="{$distPath}/assets/vendors/apexcharts/apexcharts.css" rel="stylesheet"/>
    <link href="{$distPath}/assets/vendors/keenicons/styles.bundle.css" rel="stylesheet"/>
    <link href="{$distPath}/assets/css/styles.css" rel="stylesheet"/>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background [--header-height:60px] [--sidebar-width:270px] lg:overflow-hidden bg-mono dark:bg-background">
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
    
    <!-- Page -->
    <div class="flex grow">
        <!-- Header -->
        <header class="flex lg:hidden items-center fixed z-10 top-0 start-0 end-0 shrink-0 bg-mono dark:bg-background h-(--header-height)" id="header">
            <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                <a href="#" id="dashboard-link">
                    <img class="size-[34px]" src="{$distPath}/assets/media/app/mini-logo-circle-success.svg"/>
                </a>
                <button class="kt-btn kt-btn-icon kt-btn-dim hover:text-white -me-2" data-kt-drawer-toggle="#sidebar">
                    <i class="ki-filled ki-menu"></i>
                </button>
            </div>
        </header>
        
        <!-- Wrapper -->
        <div class="flex flex-col lg:flex-row grow pt-(--header-height) lg:pt-0">
            <!-- Sidebar Container -->
            <div id="sidebar-container"></div>
            
            <!-- Main -->
            <div class="flex flex-col grow lg:rounded-l-xl bg-background border border-input lg:ms-(--sidebar-width)">
                <div class="flex flex-col grow kt-scrollable-y-auto lg:[--kt-scrollbar-width:auto] pt-5" id="scrollable_content">
                    <main class="grow" role="content">
                        <!-- Toolbar -->
                        <div class="pb-5">
                            <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                                <div class="flex flex-col flex-wrap gap-1">
                                    <h1 class="font-medium text-lg text-mono">{$title}</h1>
                                    <div class="flex items-center gap-1 text-sm font-normal">
                                        <a class="text-secondary-foreground hover:text-primary" href="#" id="home-link">Home</a>
                                        <span>/</span>
                                        <span class="text-mono">{$title}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container -->
                        <div class="kt-container-fixed">
                            <div id="page-content">
                                <!-- Content will be loaded here -->
                                <div class="kt-card">
                                    <div class="kt-card-content p-5">
                                        <p class="text-secondary-foreground">Page content will be loaded here...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{$distPath}/assets/js/core.bundle.js"></script>
    <script src="{$distPath}/assets/vendors/ktui/ktui.min.js"></script>
    <script src="{$distPath}/assets/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="{$configPath}/api.js"></script>
    <script src="{$jsPath}/auth.js"></script>
    <script src="{$jsPath}/utils.js"></script>
    <script src="{$componentsPath}/sidebar.js"></script>
    <script src="{$componentsPath}/layout.js"></script>
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', async () => {
            // Initialize auth
            const authenticated = await authManager.init();
            if (!authenticated) return;
            
            // Get user and role
            const user = authManager.getUser();
            const role = user.roles && user.roles.length > 0 ? user.roles[0] : '{$role}';
            
            // Check role access
            if (!authManager.hasRole('{$role}') && role !== 'HRD') {
                Utils.showError('You do not have access to this page');
                setTimeout(() => {
                    const layout = new LayoutComponent('', '');
                    window.location.href = layout.getDashboardPath();
                }, 2000);
                return;
            }
            
            // Initialize sidebar
            const sidebar = new SidebarComponent(user, role);
            sidebar.mount('sidebar-container');
            
            // Set dashboard link
            const layout = new LayoutComponent('{$title}', '');
            const dashboardLink = document.getElementById('dashboard-link');
            const homeLink = document.getElementById('home-link');
            if (dashboardLink) dashboardLink.href = layout.getDashboardPath();
            if (homeLink) homeLink.href = layout.getDashboardPath();
            
            // Load page content
            loadPageContent();
        });
        
        // Load page-specific content
        async function loadPageContent() {
            // This will be customized per page type
            const container = document.getElementById('page-content');
            if (container) {
                container.innerHTML = '<div class="kt-card"><div class="kt-card-content p-5"><p>Loading...</p></div></div>';
            }
        }
    </script>
</body>
</html>
HTML;
    
    return $html;
}

echo "\n✅ Page generation complete!\n";
echo "📝 Review the generated pages and customize content as needed.\n";


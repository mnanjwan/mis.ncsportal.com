<?php
/**
 * Script to update existing pages with sidebar integration
 */

$pagesToUpdate = [
    'dashboards/officer/dashboard.html',
    'dashboards/hrd/dashboard.html',
    'dashboards/staff-officer/dashboard.html',
    'dashboards/assessor/dashboard.html',
    'dashboards/validator/dashboard.html',
    'dashboards/area-controller/dashboard.html',
    'dashboards/dc-admin/dashboard.html',
    'dashboards/accounts/dashboard.html',
    'dashboards/board/dashboard.html',
    'dashboards/building/dashboard.html',
    'dashboards/establishment/dashboard.html',
    'dashboards/welfare/dashboard.html',
    'forms/onboarding/step1-personal.html',
    'forms/onboarding/step2-employment.html',
    'forms/onboarding/step3-banking.html',
    'forms/onboarding/step4-next-of-kin.html',
    'forms/emolument/raise.html',
    'forms/leave/apply.html',
    'forms/pass/apply.html',
];

$baseDir = __DIR__ . '/ncs-employee-portal/';

foreach ($pagesToUpdate as $path) {
    $fullPath = $baseDir . $path;
    
    if (!file_exists($fullPath)) {
        echo "⏭️  Skipping (not found): $path\n";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Check if already updated (has sidebar-container)
    if (strpos($content, 'sidebar-container') !== false) {
        echo "⏭️  Skipping (already updated): $path\n";
        continue;
    }
    
    // Calculate relative paths
    $depth = substr_count($path, '/') - 1;
    $distPath = str_repeat('../', $depth) . 'dist';
    $configPath = str_repeat('../', $depth) . 'config';
    $jsPath = str_repeat('../', $depth) . 'js';
    $componentsPath = str_repeat('../', $depth) . 'components';
    
    // Add scripts before closing body tag
    $scripts = <<<SCRIPTS
    <script src="{$configPath}/api.js"></script>
    <script src="{$jsPath}/auth.js"></script>
    <script src="{$jsPath}/utils.js"></script>
    <script src="{$componentsPath}/sidebar.js"></script>
    <script src="{$componentsPath}/layout.js"></script>
    <script>
        // Initialize sidebar
        document.addEventListener('DOMContentLoaded', async () => {
            const authenticated = await authManager.init();
            if (!authenticated) return;
            
            const user = authManager.getUser();
            const role = user.roles && user.roles.length > 0 ? user.roles[0] : 'Officer';
            
            const sidebar = new SidebarComponent(user, role);
            sidebar.mount('sidebar-container');
        });
    </script>
SCRIPTS;
    
    // Find sidebar div and add id if missing
    $content = preg_replace(
        '/(<div[^>]*class="[^"]*sidebar[^"]*"[^>]*)(>)/i',
        '$1 id="sidebar-container"$2',
        $content
    );
    
    // If no sidebar-container found, add it before main content
    if (strpos($content, 'sidebar-container') === false) {
        $content = preg_replace(
            '/(<div[^>]*class="[^"]*flex[^"]*grow[^"]*"[^>]*>)/i',
            '<div id="sidebar-container"></div>$1',
            $content
        );
    }
    
    // Add scripts before closing body tag
    $content = preg_replace(
        '/(<\/body>)/i',
        $scripts . '$1',
        $content
    );
    
    file_put_contents($fullPath, $content);
    echo "✅ Updated: $path\n";
}

echo "\n✅ Page updates complete!\n";


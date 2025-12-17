// Layout Component - Base layout with sidebar
class LayoutComponent {
    constructor(pageTitle, currentPath = '') {
        this.pageTitle = pageTitle;
        this.currentPath = currentPath;
    }

    async init() {
        // Initialize auth
        const authenticated = await authManager.init();
        if (!authenticated) {
            return false;
        }

        // Get user and role
        const user = authManager.getUser();
        const role = user.roles && user.roles.length > 0 ? user.roles[0] : 'Officer';

        // Initialize sidebar
        const sidebar = new SidebarComponent(user, role);
        sidebar.mount('sidebar-container');

        // Set page title
        document.title = `${this.pageTitle} - NCS Employee Portal`;

        return true;
    }

    renderHeader() {
        return `
            <!-- Header -->
            <header class="flex lg:hidden items-center fixed z-10 top-0 start-0 end-0 shrink-0 bg-mono dark:bg-background h-(--header-height)" id="header">
                <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                    <a href="${this.getDashboardPath()}">
                        <img class="size-[34px]" src="../../dist/assets/media/app/mini-logo-circle-success.svg"/>
                    </a>
                    <button class="kt-btn kt-btn-icon kt-btn-dim hover:text-white -me-2" data-kt-drawer-toggle="#sidebar">
                        <i class="ki-filled ki-menu"></i>
                    </button>
                </div>
            </header>
            <!-- End of Header -->
        `;
    }

    getDashboardPath() {
        const user = authManager.getUser();
        const role = user?.roles && user.roles.length > 0 ? user.roles[0] : 'Officer';
        const rolePaths = {
            'Officer': '../../dashboards/officer/dashboard.html',
            'HRD': '../../dashboards/hrd/dashboard.html',
            'Staff Officer': '../../dashboards/staff-officer/dashboard.html',
            'Assessor': '../../dashboards/assessor/dashboard.html',
            'Validator': '../../dashboards/validator/dashboard.html',
            'Area Controller': '../../dashboards/area-controller/dashboard.html',
            'DC Admin': '../../dashboards/dc-admin/dashboard.html',
            'Accounts': '../../dashboards/accounts/dashboard.html',
            'Board': '../../dashboards/board/dashboard.html',
            'Building Unit': '../../dashboards/building/dashboard.html',
            'Establishment': '../../dashboards/establishment/dashboard.html',
            'Welfare': '../../dashboards/welfare/dashboard.html'
        };
        return rolePaths[role] || rolePaths['Officer'];
    }

    renderToolbar(breadcrumbs = []) {
        const breadcrumbItems = breadcrumbs.map((crumb, index) => {
            if (index === breadcrumbs.length - 1) {
                return `<span class="text-mono">${crumb.label}</span>`;
            }
            return `<a class="text-secondary-foreground hover:text-primary" href="${crumb.href}">${crumb.label}</a>`;
        }).join(' / ');

        return `
            <!-- Toolbar -->
            <div class="pb-5">
                <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                    <div class="flex flex-col flex-wrap gap-1">
                        <h1 class="font-medium text-lg text-mono">${this.pageTitle}</h1>
                        <div class="flex items-center gap-1 text-sm font-normal">
                            ${breadcrumbItems}
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Toolbar -->
        `;
    }
}


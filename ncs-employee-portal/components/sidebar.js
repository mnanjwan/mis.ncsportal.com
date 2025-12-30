// Sidebar Component - Reusable sidebar for all pages
class SidebarComponent {
    constructor(user, role) {
        this.user = user;
        this.role = role || 'Officer';
        this.menuItems = this.getMenuItemsForRole(role);
    }

    getMenuItemsForRole(role) {
        const baseMenu = [
            { icon: 'ki-filled ki-home-3', title: 'Dashboard', href: this.getDashboardPath(role) }
        ];

        const roleMenus = {
            'Officer': [
                ...baseMenu,
                { icon: 'ki-filled ki-wallet', title: 'Raise Emolument', href: '../../forms/emolument/raise.html' },
                { icon: 'ki-filled ki-calendar', title: 'Apply for Leave', href: '../../forms/leave/apply.html' },
                { icon: 'ki-filled ki-calendar-tick', title: 'Apply for Pass', href: '../../forms/pass/apply.html' },
                { icon: 'ki-filled ki-profile-circle', title: 'My Profile', href: 'profile.html' }
            ],
            'HRD': [
                ...baseMenu,
                { icon: 'ki-filled ki-people', title: 'Officers', href: 'officers.html' },
                { icon: 'ki-filled ki-calendar-2', title: 'Emolument Timeline', href: 'emolument-timeline.html' },
                { icon: 'ki-filled ki-file-up', title: 'Staff Orders', href: 'staff-orders.html' },
                { icon: 'ki-filled ki-chart-simple', title: 'Reports', href: 'reports.html' }
            ],
            'Staff Officer': [
                ...baseMenu,
                { icon: 'ki-filled ki-calendar', title: 'Leave & Pass', href: 'leave-pass.html' },
                { icon: 'ki-filled ki-people', title: 'Manning Level', href: 'manning-level.html' },
                { icon: 'ki-filled ki-calendar-tick', title: 'Duty Roster', href: 'roaster.html' },
                { icon: 'ki-filled ki-profile-circle', title: 'Officers', href: 'officers.html' }
            ],
            'Assessor': [
                ...baseMenu,
                { icon: 'ki-filled ki-wallet', title: 'Emoluments', href: 'emoluments.html' }
            ],
            'Validator': [
                ...baseMenu,
                { icon: 'ki-filled ki-wallet', title: 'Emoluments', href: 'emoluments.html' }
            ],
            'Area Controller': [
                ...baseMenu,
                { icon: 'ki-filled ki-wallet', title: 'Emoluments', href: 'emoluments.html' },
                { icon: 'ki-filled ki-calendar', title: 'Leave & Pass', href: 'leave-pass.html' }
            ],
            'DC Admin': [
                ...baseMenu,
                { icon: 'ki-filled ki-calendar', title: 'Leave & Pass', href: 'leave-pass.html' }
            ],
            'Accounts': [
                ...baseMenu,
                { icon: 'ki-filled ki-wallet', title: 'Validated Officers', href: 'validated-officers.html' }
            ],
            'Board': [
                ...baseMenu,
                { icon: 'ki-filled ki-arrow-up', title: 'Promotions', href: 'promotions.html' }
            ],
            'Building Unit': [
                ...baseMenu,
                { icon: 'ki-filled ki-home-2', title: 'Quarters', href: 'quarters.html' }
            ],
            'Establishment': [
                ...baseMenu,
                { icon: 'ki-filled ki-abstract-26', title: 'Service Numbers', href: 'service-numbers.html' }
            ],
            'Welfare': [
                ...baseMenu,
                { icon: 'ki-filled ki-heart', title: 'Deceased Officers', href: 'deceased-officers.html' }
            ]
        };

        return roleMenus[role] || roleMenus['Officer'];
    }

    getDashboardPath(role) {
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

    render() {
        const userDisplayName = this.user?.officer?.name || this.user?.email || 'User';
        const serviceNumber = this.user?.officer?.service_number || 'N/A';

        return `
            <!-- Sidebar -->
            <div class="flex-col fixed top-0 bottom-0 z-20 hidden lg:flex items-stretch shrink-0 w-(--sidebar-width) dark [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start flex top-0 bottom-0" id="sidebar">
                <!-- Sidebar Header -->
                <div class="flex flex-col gap-2.5" id="sidebar_header">
                    <div class="flex items-center gap-2.5 px-3.5 h-[70px]">
                        <a href="${this.getDashboardPath(this.role)}">
                            <img class="size-[34px]" src="/logo.jpg"/>
                        </a>
                        <div class="kt-menu kt-menu-default grow" data-kt-menu="true">
                            <div class="kt-menu-item grow" data-kt-menu-item-offset="0, 15px" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                                <div class="kt-menu-label cursor-pointer text-mono font-medium grow justify-between">
                                    <span class="text-lg font-medium text-inverse grow">
                                        NCS Portal
                                    </span>
                                    <div class="flex flex-col text-mono font-medium">
                                        <span class="kt-menu-arrow">
                                            <i class="ki-filled ki-up"></i>
                                        </span>
                                        <span class="kt-menu-arrow">
                                            <i class="ki-filled ki-down"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="kt-menu-dropdown w-48 py-2">
                                    <div class="kt-menu-item">
                                        <a class="kt-menu-link" href="profile.html" tabindex="0">
                                            <span class="kt-menu-icon">
                                                <i class="ki-filled ki-profile-circle"></i>
                                            </span>
                                            <span class="kt-menu-title">My Profile</span>
                                        </a>
                                    </div>
                                    <div class="kt-menu-item">
                                        <a class="kt-menu-link" href="#" onclick="authManager.logout(); return false;" tabindex="0">
                                            <span class="kt-menu-icon">
                                                <i class="ki-filled ki-exit-right"></i>
                                            </span>
                                            <span class="kt-menu-title">Sign Out</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 px-3.5">
                        <button class="kt-btn kt-btn-icon kt-btn-secondary [&_i]:text-white" data-kt-modal-toggle="#search_modal">
                            <i class="ki-filled ki-magnifier"></i>
                        </button>
                    </div>
                </div>
                <!-- End of Sidebar Header -->
                
                <!-- Sidebar menu -->
                <div class="flex items-stretch grow shrink-0 justify-center my-5" id="sidebar_menu">
                    <div class="kt-scrollable-y-auto grow" data-kt-scrollable="true" data-kt-scrollable-dependencies="#sidebar_header, #sidebar_footer" data-kt-scrollable-height="auto" data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_menu">
                        <!-- Primary Menu -->
                        <div class="mb-5">
                            <h3 class="text-sm text-muted-foreground uppercase ps-5 inline-block mb-3">Menu</h3>
                            <div class="kt-menu flex flex-col w-full gap-1.5 px-3.5" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_primary_menu">
                                ${this.menuItems.map((item, index) => `
                                    <div class="kt-menu-item">
                                        <a class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md kt-menu-item-active:bg-accent/60 kt-menu-link-hover:bg-accent/60 ${index === 0 ? 'kt-menu-item-active' : ''}" href="${item.href}">
                                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono">
                                                <i class="${item.icon}"></i>
                                            </span>
                                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-active:text-mono kt-menu-link-hover:text-mono">
                                                ${item.title}
                                            </span>
                                        </a>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of Sidebar menu -->
                
                <!-- Sidebar Footer -->
                <div class="flex flex-center justify-between shrink-0 ps-4 pe-3.5 mb-3.5" id="sidebar_footer">
                    <div class="kt-card shadow-none bg-muted/70">
                        <div class="kt-card-content flex flex-col gap-3 p-4">
                            <div class="flex items-center gap-2.5">
                                <div class="kt-avatar size-10">
                                    <div class="kt-avatar-image">
                                        <img alt="avatar" src="../../dist/assets/media/avatars/300-1.png"/>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-0.5 grow">
                                    <span class="text-sm font-semibold text-mono">${userDisplayName}</span>
                                    <span class="text-xs text-secondary-foreground">SVC: ${serviceNumber}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary" data-kt-drawer-toggle="#notifications_drawer">
                            <i class="ki-filled ki-notification-status text-lg"></i>
                        </button>
                        <a class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary" href="#" onclick="authManager.logout(); return false;">
                            <i class="ki-filled ki-exit-right"></i>
                        </a>
                    </div>
                </div>
                <!-- End of Sidebar Footer -->
            </div>
            <!-- End of Sidebar -->
        `;
    }

    mount(containerId = 'sidebar-container') {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = this.render();
            // Reinitialize KT components
            if (typeof KTComponents !== 'undefined') {
                KTComponents.init();
            }
        }
    }
}


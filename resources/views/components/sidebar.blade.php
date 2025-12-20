@php
    $user = auth()->user();
    $officer = $user->officer ?? null;
    
    // Load ONLY ACTIVE roles
    $user->load(['roles' => function($query) {
        $query->wherePivot('is_active', true);
    }]);
    
    $roles = $user->roles->pluck('name')->toArray();
    
    // Determine primary role based on priority (same as DashboardController)
    $rolePriorities = [
        'HRD',
        'Board',
        'Accounts',
        'Welfare',
        'Establishment',
        'TRADOC',
        'ICT',
        'Building Unit',
        'Area Controller',
        'DC Admin',
        'Zone Coordinator',
        'Validator',
        'Assessor',
        'Staff Officer',
        'Officer'
    ];
    
    $primaryRole = 'Officer'; // Default
    foreach ($rolePriorities as $priorityRole) {
        if (in_array($priorityRole, $roles)) {
            $primaryRole = $priorityRole;
            break;
        }
    }

    // Load officer relationship if not already loaded
    if (!$officer && $user->relationLoaded('officer')) {
        $officer = $user->officer;
    } elseif (!$officer) {
        $officer = $user->officer()->first();
    }

    // Check if onboarding is complete for Officer role
    $onboardingComplete = true;
    if ($primaryRole === 'Officer' && $officer) {
        $onboardingComplete = $officer->hasCompletedOnboarding();
    }

    // Get menu items based on role
    $menuItems = [];
    $dashboardPath = route('dashboard');

    switch ($primaryRole) {
        case 'Officer':
            if ($onboardingComplete) {
                // Show full menu for completed onboarding
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('officer.dashboard')],
                [
                    'title' => 'Emoluments',
                    'icon' => 'ki-filled ki-wallet',
                    'submenu' => [
                        ['title' => 'My Emoluments', 'href' => route('officer.emoluments')],
                        ['title' => 'Raise Emolument', 'href' => route('emolument.raise')],
                    ]
                ],
                [
                    'title' => 'Applications',
                    'icon' => 'ki-filled ki-calendar',
                    'submenu' => [
                        ['title' => 'Leave Applications', 'href' => route('officer.leave-applications')],
                        ['title' => 'Pass Applications', 'href' => route('officer.pass-applications')],
                        ['title' => 'Account Changes', 'href' => route('officer.account-change.index')],
                        ['title' => 'Next of KIN', 'href' => route('officer.next-of-kin.index')],
                    ]
                ],
                ['icon' => 'ki-filled ki-profile-circle', 'title' => 'My Profile', 'href' => route('officer.profile')],
                [
                    'title' => 'Settings',
                    'icon' => 'ki-filled ki-setting-2',
                    'submenu' => [
                        ['title' => 'Change Password', 'href' => route('officer.settings')],
                    ]
                ],
            ];
            } else {
                // Show only onboarding link for incomplete onboarding
                $menuItems = [
                    ['icon' => 'ki-filled ki-user', 'title' => 'Complete Onboarding', 'href' => route('onboarding.step1')],
                ];
            }
            break;
        case 'HRD':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('hrd.dashboard')],
                ['icon' => 'ki-filled ki-people', 'title' => 'Officers', 'href' => route('hrd.officers')],
                ['icon' => 'ki-filled ki-user', 'title' => 'Officer Onboarding', 'href' => route('hrd.onboarding')],
                [
                    'title' => 'User Management',
                    'icon' => 'ki-filled ki-user',
                    'submenu' => [
                        ['title' => 'Role Assignments', 'href' => route('hrd.role-assignments')],
                    ]
                ],
                [
                    'title' => 'Orders',
                    'icon' => 'ki-filled ki-file-up',
                    'submenu' => [
                        ['title' => 'Staff Orders', 'href' => route('hrd.staff-orders')],
                        ['title' => 'Movement Orders', 'href' => route('hrd.movement-orders')],
                    ]
                ],
                ['icon' => 'ki-filled ki-calendar-2', 'title' => 'Emolument Timeline', 'href' => route('hrd.emolument-timeline')],
                ['icon' => 'ki-filled ki-calendar', 'title' => 'Leave Types', 'href' => route('hrd.leave-types')],
                ['icon' => 'ki-filled ki-people', 'title' => 'Manning Requests', 'href' => route('hrd.manning-requests')],
                ['icon' => 'ki-filled ki-book', 'title' => 'Course Nominations', 'href' => route('hrd.courses')],
                [
                    'title' => 'Promotions & Retirement',
                    'icon' => 'ki-filled ki-arrow-up',
                    'submenu' => [
                        ['title' => 'Promotion Criteria', 'href' => route('hrd.promotion-criteria')],
                        ['title' => 'Promotion Eligibility', 'href' => route('hrd.promotion-eligibility')],
                        ['title' => 'Retirement List', 'href' => route('hrd.retirement-list')],
                    ]
                ],
                ['icon' => 'ki-filled ki-chart-simple', 'title' => 'Reports', 'href' => route('hrd.reports')],
                [
                    'title' => 'Settings',
                    'icon' => 'ki-filled ki-setting-2',
                    'submenu' => [
                        ['title' => 'Zones', 'href' => route('hrd.zones.index')],
                        ['title' => 'Commands', 'href' => route('hrd.commands.index')],
                        ['title' => 'System Settings', 'href' => route('hrd.system-settings')],
                    ]
                ],
            ];
            break;
        case 'Zone Coordinator':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('zone-coordinator.dashboard')],
                [
                    'title' => 'Staff Orders',
                    'icon' => 'ki-filled ki-file-up',
                    'submenu' => [
                        ['title' => 'View Orders', 'href' => route('zone-coordinator.staff-orders')],
                        ['title' => 'Create Order', 'href' => route('zone-coordinator.staff-orders.create')],
                    ]
                ],
                ['icon' => 'ki-filled ki-people', 'title' => 'Zone Officers', 'href' => route('zone-coordinator.officers')],
            ];
            break;
        case 'Staff Officer':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('staff-officer.dashboard')],
                ['icon' => 'ki-filled ki-calendar', 'title' => 'Leave & Pass', 'href' => route('staff-officer.leave-pass')],
                ['icon' => 'ki-filled ki-people', 'title' => 'Manning Level', 'href' => route('staff-officer.manning-level')],
                ['icon' => 'ki-filled ki-calendar-tick', 'title' => 'Duty Roster', 'href' => route('staff-officer.roster')],
                ['icon' => 'ki-filled ki-profile-circle', 'title' => 'Officers', 'href' => route('staff-officer.officers')],
                ['icon' => 'ki-filled ki-heart', 'title' => 'Report Deceased', 'href' => route('staff-officer.deceased-officers.create')],
            ];
            break;
        case 'Assessor':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('assessor.dashboard')],
                ['icon' => 'ki-filled ki-wallet', 'title' => 'Emoluments', 'href' => route('assessor.emoluments')],
            ];
            break;
        case 'Validator':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('validator.dashboard')],
                ['icon' => 'ki-filled ki-wallet', 'title' => 'Emoluments', 'href' => route('validator.emoluments')],
            ];
            break;
        case 'Area Controller':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('area-controller.dashboard')],
                ['icon' => 'ki-filled ki-wallet', 'title' => 'Emoluments', 'href' => route('area-controller.emoluments')],
                ['icon' => 'ki-filled ki-calendar', 'title' => 'Leave & Pass', 'href' => route('area-controller.leave-pass')],
                ['icon' => 'ki-filled ki-people', 'title' => 'Manning Requests', 'href' => route('area-controller.manning-level')],
                ['icon' => 'ki-filled ki-calendar-tick', 'title' => 'Duty Rosters', 'href' => route('area-controller.roster')],
                ['icon' => 'ki-filled ki-heart', 'title' => 'Report Deceased', 'href' => route('area-controller.deceased-officers.create')],
            ];
            break;
        case 'DC Admin':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('dc-admin.dashboard')],
                ['icon' => 'ki-filled ki-calendar', 'title' => 'Leave & Pass', 'href' => route('dc-admin.leave-pass')],
            ];
            break;
        case 'Accounts':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('accounts.dashboard')],
                ['icon' => 'ki-filled ki-wallet', 'title' => 'Validated Officers', 'href' => route('accounts.validated-officers')],
                ['icon' => 'ki-filled ki-notepad-edit', 'title' => 'Account Change Requests', 'href' => route('accounts.account-change.pending')],
                ['icon' => 'ki-filled ki-heart', 'title' => 'Deceased Officers', 'href' => route('accounts.deceased-officers')],
            ];
            break;
        case 'Board':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('board.dashboard')],
                ['icon' => 'ki-filled ki-arrow-up', 'title' => 'Promotions', 'href' => route('board.promotions')],
            ];
            break;
        case 'Building Unit':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('building.dashboard')],
                ['icon' => 'ki-filled ki-home-2', 'title' => 'Quarters', 'href' => route('building.quarters')],
            ];
            break;
        case 'Establishment':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('establishment.dashboard')],
                ['icon' => 'ki-filled ki-abstract-26', 'title' => 'Service Numbers', 'href' => route('establishment.service-numbers')],
                ['icon' => 'ki-filled ki-user-plus', 'title' => 'New Recruits', 'href' => route('establishment.new-recruits')],
                ['icon' => 'ki-filled ki-file', 'title' => 'Training Results', 'href' => route('establishment.training-results')],
            ];
            break;
        case 'Welfare':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('welfare.dashboard')],
                ['icon' => 'ki-filled ki-people', 'title' => 'Next of KIN Requests', 'href' => route('welfare.next-of-kin.pending')],
                ['icon' => 'ki-filled ki-heart', 'title' => 'Deceased Officers', 'href' => route('welfare.deceased-officers')],
            ];
            break;
        case 'TRADOC':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('tradoc.dashboard')],
                ['icon' => 'ki-filled ki-file-up', 'title' => 'Upload Training Results', 'href' => route('tradoc.upload')],
                ['icon' => 'ki-filled ki-chart-simple', 'title' => 'Sorted Results', 'href' => route('tradoc.sorted-results')],
            ];
            break;
        case 'ICT':
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => route('ict.dashboard')],
            ];
            break;
        default:
            $menuItems = [
                ['icon' => 'ki-filled ki-home-3', 'title' => 'Dashboard', 'href' => $dashboardPath],
            ];
    }

    $userDisplayName = $officer ? ($officer->initials . ' ' . $officer->surname) : $user->email;
    $serviceNumber = 'N/A';
    if ($officer && !empty($officer->service_number)) {
        $serviceNumber = $officer->service_number;
    }
    $currentRoute = request()->route()->getName();
@endphp

<div class="flex-col fixed top-0 bottom-0 z-20 hidden lg:flex items-stretch shrink-0 w-(--sidebar-width) dark [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start flex top-0 bottom-0" id="sidebar">
    <!-- Sidebar Header -->
    <div class="flex flex-col gap-2.5" id="sidebar_header">
        <div class="flex items-center gap-2.5 px-3.5 h-[70px]">
            <a href="{{ route('dashboard') }}" class="flex items-center shrink-0">
                <img class="size-[34px]"
                    src="{{ asset('ncs-employee-portal/dist/assets/media/app/portal-logo-circle.svg') }}"
                    alt="Portal Logo" />
            </a>
            <div class="kt-menu kt-menu-default grow min-w-0" data-kt-menu="true">
                <div class="kt-menu-item grow" data-kt-menu-item-offset="0, 15px"
                    data-kt-menu-item-placement="bottom-start" data-kt-menu-item-toggle="dropdown"
                    data-kt-menu-item-trigger="hover">
                    <div class="kt-menu-label cursor-pointer text-mono font-medium grow justify-between items-center">
                        <span class="text-lg font-medium text-inverse grow truncate">NCS Portal</span>
                        <div class="flex flex-col text-mono font-medium shrink-0">
                            <span class="kt-menu-arrow"><i class="ki-filled ki-up"></i></span>
                            <span class="kt-menu-arrow"><i class="ki-filled ki-down"></i></span>
                        </div>
                    </div>
                    <div class="kt-menu-dropdown w-48 py-2">
                        <div class="kt-menu-item">
                            <a class="kt-menu-link" href="{{ route('officer.profile') }}">
                                <span class="kt-menu-icon"><i class="ki-filled ki-profile-circle"></i></span>
                                <span class="kt-menu-title">My Profile</span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a class="kt-menu-link" href="{{ route('officer.settings') }}">
                                <span class="kt-menu-icon"><i class="ki-filled ki-setting-2"></i></span>
                                <span class="kt-menu-title">Change Password</span>
                            </a>
                        </div>
                        <div class="kt-menu-item">
                            <a class="kt-menu-link" href="#"
                                data-kt-modal-toggle="#logout-confirm-modal">
                                <span class="kt-menu-icon"><i class="ki-filled ki-exit-right"></i></span>
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
        <div class="kt-scrollable-y-auto grow" data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header, #sidebar_footer" data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_menu">
            <!-- Primary Menu -->
            <div class="mb-5">
                <h3 class="text-sm text-muted-foreground uppercase ps-5 inline-block mb-3">Menu</h3>
                <div class="kt-menu flex flex-col w-full gap-1.5 px-3.5" data-kt-menu="true"
                    data-kt-menu-accordion-expand-all="false" id="sidebar_primary_menu">
                    @foreach($menuItems as $item)
                        @if(isset($item['submenu']))
                            @php
                                $hasActiveChild = collect($item['submenu'])->contains(function ($subItem) {
                                    return request()->url() === url($subItem['href']);
                                });
                            @endphp
                            <div class="kt-menu-item {{ $hasActiveChild ? 'here show' : '' }}" data-kt-menu-trigger="click"
                                onclick="this.classList.toggle('show'); this.classList.toggle('here');">
                                <a class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md" href="javascript:void(0)">
                                    <span class="kt-menu-icon items-start text-lg text-secondary-foreground">
                                        <i class="{{ $item['icon'] ?? 'ki-filled ki-menu' }}"></i>
                                    </span>
                                    <span class="kt-menu-title text-sm text-foreground font-medium">
                                        {{ $item['title'] }}
                                    </span>
                                    <span class="kt-menu-arrow">
                                        <i class="ki-filled ki-down text-2xs"></i>
                                    </span>
                                </a>
                                <div class="kt-menu-accordion" onclick="event.stopPropagation()">
                                    @foreach($item['submenu'] as $subItem)
                                        @php
                                            $isSubActive = request()->url() === url($subItem['href']);
                                        @endphp
                                        <div class="kt-menu-item">
                                            <a class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md {{ $isSubActive ? 'kt-menu-item-active' : '' }}"
                                                href="{{ $subItem['href'] }}">
                                                <span class="kt-menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="kt-menu-title text-sm text-foreground font-medium">
                                                    - {{ $subItem['title'] }}
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @php
                                $isActive = request()->url() === url($item['href']);
                            @endphp
                            <div class="kt-menu-item">
                                <a class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md {{ $isActive ? 'kt-menu-item-active' : '' }}"
                                    href="{{ $item['href'] }}">
                                    <span class="kt-menu-icon items-start text-lg text-secondary-foreground">
                                        <i class="{{ $item['icon'] ?? 'ki-filled ki-menu' }}"></i>
                                    </span>
                                    <span class="kt-menu-title text-sm text-foreground font-medium">
                                        {{ $item['title'] }}
                                    </span>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- End of Sidebar menu -->
    <!-- Sidebar Footer -->
    <div class="flex items-center justify-between shrink-0 ps-4 pe-3.5 mb-3.5 gap-2" id="sidebar_footer">
        <!-- User -->
        <div class="kt-card shadow-none bg-muted/70 grow min-w-0 overflow-hidden">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="kt-avatar size-10 shrink-0">
                        <div class="kt-avatar-image">
                            <img id="sidebar-profile-picture" alt="avatar"
                                src="{{ ($officer && $officer->profile_picture_url) ? asset('storage/' . $officer->profile_picture_url) : asset('ncs-employee-portal/dist/assets/media/avatars/300-1.png') }}" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-0.5 grow min-w-0 overflow-hidden">
                        <span class="text-sm font-semibold text-mono truncate block">{{ $user->email }}</span>
                        <span class="text-xs text-secondary-foreground truncate block">SVC: {{ $serviceNumber }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of User -->
        <div class="flex items-center gap-1.5 shrink-0">
            <!-- Notifications -->
            <button class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary"
                data-kt-drawer-toggle="#notifications_drawer">
                <i class="ki-filled ki-notification-status text-lg"></i>
            </button>
            <!-- End of Notifications -->
            <a class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary" href="#"
                data-kt-modal-toggle="#logout-confirm-modal">
                <i class="ki-filled ki-exit-right"></i>
            </a>
        </div>
    </div>
    <!-- End of Sidebar Footer -->
</div>
<!-- End of Sidebar -->

<!-- Logout Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="logout-confirm-modal">
    <div class="kt-modal-content max-w-[400px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                    <i class="ki-filled ki-information text-warning text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Confirm Sign Out</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to sign out? You will need to log in again to access your account.
            </p>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <button class="kt-btn kt-btn-primary" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="kt-menu-icon"><i class="ki-filled ki-exit-right"></i></span>
                <span>Sign Out</span>
            </button>
        </div>
    </div>
</div>
<!-- End of Logout Confirmation Modal -->

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
<!-- Search Modal -->
<div class="kt-modal" data-kt-modal="true" id="search_modal">
    <div class="kt-modal-content max-w-[600px] top-[15%]">
        <div class="kt-modal-header py-4 px-5">
            <i class="ki-filled ki-magnifier text-muted-foreground text-xl"></i>
            <input class="kt-input kt-input-ghost" name="query" placeholder="Tap to start search" type="text" value=""/>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-0 pb-5">
            <div class="kt-scrollable-y-auto" data-kt-scrollable="true" data-kt-scrollable-max-height="auto" data-kt-scrollable-offset="300px">
                <div class="flex flex-col gap-2.5">
                    <div>
                        <div class="text-xs text-secondary-foreground font-medium pt-2.5 pb-1.5 ps-5">
                            Quick Links
                        </div>
                        <div class="kt-menu kt-menu-default px-0.5 flex-col">
                            @php
                                $user = auth()->user();
                                $role = $user->roles->first()?->name ?? 'Officer';
                                $dashboardRoute = match($role) {
                                    'Officer' => route('officer.dashboard'),
                                    'HRD' => route('hrd.dashboard'),
                                    'Staff Officer' => route('staff-officer.dashboard'),
                                    'Assessor' => route('assessor.dashboard'),
                                    'Validator' => route('validator.dashboard'),
                                    'Area Controller' => route('area-controller.dashboard'),
                                    'DC Admin' => route('dc-admin.dashboard'),
                                    'Accounts' => route('accounts.dashboard'),
                                    'Board' => route('board.dashboard'),
                                    'Building Unit' => route('building.dashboard'),
                                    'Establishment' => route('establishment.dashboard'),
                                    'Welfare' => route('welfare.dashboard'),
                                    default => route('dashboard'),
                                };
                            @endphp
                            <div class="kt-menu-item">
                                <a class="kt-menu-link" href="{{ $dashboardRoute }}">
                                    <span class="kt-menu-icon">
                                        <i class="ki-filled ki-home-3"></i>
                                    </span>
                                    <span class="kt-menu-title">Dashboard</span>
                                </a>
                            </div>
                            @if($role === 'Officer')
                                <div class="kt-menu-item">
                                    <a class="kt-menu-link" href="{{ route('emolument.raise') }}">
                                        <span class="kt-menu-icon">
                                            <i class="ki-filled ki-wallet"></i>
                                        </span>
                                        <span class="kt-menu-title">Raise Emolument</span>
                                    </a>
                                </div>
                                <div class="kt-menu-item">
                                    <a class="kt-menu-link" href="{{ route('leave.apply') }}">
                                        <span class="kt-menu-icon">
                                            <i class="ki-filled ki-calendar"></i>
                                        </span>
                                        <span class="kt-menu-title">Apply for Leave</span>
                                    </a>
                                </div>
                                <div class="kt-menu-item">
                                    <a class="kt-menu-link" href="{{ route('pass.apply') }}">
                                        <span class="kt-menu-icon">
                                            <i class="ki-filled ki-calendar-tick"></i>
                                        </span>
                                        <span class="kt-menu-title">Apply for Pass</span>
                                    </a>
                                </div>
                                <div class="kt-menu-item">
                                    <a class="kt-menu-link" href="{{ route('officer.profile') }}">
                                        <span class="kt-menu-icon">
                                            <i class="ki-filled ki-profile-circle"></i>
                                        </span>
                                        <span class="kt-menu-title">My Profile</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Search Modal -->



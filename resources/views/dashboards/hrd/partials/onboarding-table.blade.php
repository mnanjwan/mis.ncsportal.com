<!-- Desktop Table View -->
<div class="hidden lg:block">
    <div class="overflow-x-auto">
        <table class="kt-table w-full">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Name</th>
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Email</th>
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Email Status</th>
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Onboarding Status</th>
                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Initiated</th>
                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($onboardingOfficers as $officer)
                    @php
                        $emailDelivered = $officer->user->email_verified_at !== null;
                        $onboardingCompleted = $officer->user && 
                            $officer->date_of_birth && 
                            $officer->date_of_first_appointment && 
                            $officer->bank_name && 
                            $officer->pfa_name;
                    @endphp
                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                        <td class="py-3 px-4 text-sm font-mono text-foreground">
                            {{ $officer->service_number ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-sm font-medium text-foreground">
                                {{ ($officer->initials ?? '') . ' ' . ($officer->surname ?? '') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                            {{ $officer->user->email ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($emailDelivered)
                                <span class="kt-badge kt-badge-success">
                                    <i class="ki-filled ki-check-circle"></i> Delivered
                                </span>
                            @else
                                <span class="kt-badge kt-badge-warning">
                                    <i class="ki-filled ki-information"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($onboardingCompleted)
                                <span class="kt-badge kt-badge-success">
                                    <i class="ki-filled ki-check-circle"></i> Completed
                                </span>
                            @else
                                <span class="kt-badge kt-badge-warning">
                                    <i class="ki-filled ki-clock"></i> In Progress
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                            {{ $officer->user->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$onboardingCompleted)
                                    <button type="button" 
                                            onclick="openEditEmailModal({{ $officer->id }}, '{{ $officer->user->email }}', '{{ addslashes(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')) }}')"
                                            class="kt-btn kt-btn-sm kt-btn-ghost" 
                                            title="Edit Email">
                                        <i class="ki-filled ki-pencil"></i> Edit Email
                                    </button>
                                @endif
                                <form action="{{ route('hrd.onboarding.resend-link', $officer->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost" title="Resend Email">
                                        <i class="ki-filled ki-arrows-circle"></i> Resend
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            @if(isset($searchTerm) && $searchTerm)
                                <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No officers found matching "<strong>{{ $searchTerm }}</strong>"</p>
                            @else
                                <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No onboarding initiated yet</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Card View -->
<div class="lg:hidden">
    <div class="flex flex-col gap-4">
        @forelse($onboardingOfficers as $officer)
            @php
                $emailDelivered = $officer->user->email_verified_at !== null;
                $onboardingCompleted = $officer->user && 
                    $officer->date_of_birth && 
                    $officer->date_of_first_appointment && 
                    $officer->bank_name && 
                    $officer->pfa_name;
            @endphp
            <div class="flex flex-col gap-3 p-4 rounded-lg bg-muted/50 border border-input">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-semibold text-foreground">
                            {{ ($officer->initials ?? '') . ' ' . ($officer->surname ?? '') }}
                        </span>
                        <span class="text-xs text-secondary-foreground font-mono">
                            {{ $officer->service_number ?? 'N/A' }}
                        </span>
                        <span class="text-xs text-secondary-foreground">
                            {{ $officer->user->email ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($emailDelivered)
                        <span class="kt-badge kt-badge-success text-xs">Email Delivered</span>
                    @else
                        <span class="kt-badge kt-badge-warning text-xs">Email Pending</span>
                    @endif
                    @if($onboardingCompleted)
                        <span class="kt-badge kt-badge-success text-xs">Completed</span>
                    @else
                        <span class="kt-badge kt-badge-warning text-xs">In Progress</span>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-input">
                    @if(!$onboardingCompleted)
                        <button type="button" 
                                onclick="openEditEmailModal({{ $officer->id }}, '{{ $officer->user->email }}', '{{ addslashes(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')) }}')"
                                class="kt-btn kt-btn-sm kt-btn-ghost" 
                                title="Edit Email">
                            <i class="ki-filled ki-pencil"></i> Edit
                        </button>
                    @endif
                    <form action="{{ route('hrd.onboarding.resend-link', $officer->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-arrows-circle"></i> Resend
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                @if(isset($searchTerm) && $searchTerm)
                    <i class="ki-filled ki-magnifier text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers found matching "<strong>{{ $searchTerm }}</strong>"</p>
                @else
                    <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No onboarding initiated yet</p>
                @endif
            </div>
        @endforelse
    </div>
</div>

<!-- Pagination -->
<x-pagination :paginator="$onboardingOfficers" item-name="officers" />

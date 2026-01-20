@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Officer Dashboard')

@section('breadcrumbs')
    <span class="text-primary">Officer Dashboard</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Profile Picture Update Required (Post-Promotion) -->
    @if(isset($officer) && $officer && method_exists($officer, 'needsProfilePictureUpdateAfterPromotion') && $officer->needsProfilePictureUpdateAfterPromotion())
        <div class="kt-card mb-5" style="background-color: #fee2e2; border: 2px solid #dc3545;">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information text-xl" style="color: #dc3545;"></i>
                    <div class="flex-1">
                        <p class="text-sm font-semibold mb-1" style="color: #b91c1c;">
                            Action Required: Update your profile picture
                        </p>
                        <p class="text-sm" style="color: #dc2626;">
                            Change Profile Picture hasn’t been done yet. You will be unable to raise emolument until you update your profile picture.
                        </p>
                        <a href="{{ route('officer.profile') }}" class="kt-btn kt-btn-sm mt-3" style="background-color: #dc3545; border-color: #dc3545; color: #ffffff;">
                            <i class="ki-filled ki-profile-circle"></i> Go to My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Pending Queries Alert -->
    @if(isset($pendingQueries) && $pendingQueries->count() > 0)
        <div class="kt-card mb-5" style="background-color: #fee2e2; border: 2px solid #dc3545;">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-xl" style="color: #dc3545;"></i>
                    <div class="flex-1">
                        <p class="text-sm font-semibold mb-1" style="color: #b91c1c;">
                            You have {{ $pendingQueries->count() }} pending query{{ $pendingQueries->count() !== 1 ? 's' : '' }} that require{{ $pendingQueries->count() === 1 ? 's' : '' }} your response
                        </p>
                        @foreach($pendingQueries as $query)
                            <p class="text-sm mb-2" style="color: #dc2626;">
                                Query issued on {{ $query->issued_at ? $query->issued_at->format('d/m/Y') : 'N/A' }} by {{ $query->issuedBy->name ?? $query->issuedBy->email ?? 'Staff Officer' }}
                            </p>
                        @endforeach
                        <a href="{{ route('officer.queries.index') }}" class="kt-btn kt-btn-sm mt-2" style="background-color: #dc3545; border-color: #dc3545; color: #ffffff;">
                            <i class="ki-filled ki-question"></i> View & Respond to Queries
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Pending Quarter Allocations Alert -->
    @if(isset($pendingAllocations) && $pendingAllocations->count() > 0)
        <div class="kt-card mb-5" style="background-color: #fef3c7; border: 2px solid #f59e0b;">
            <div class="kt-card-content p-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-home-2 text-xl" style="color: #f59e0b;"></i>
                    <div class="flex-1">
                        <p class="text-sm font-semibold mb-3" style="color: #d97706;">
                            You have {{ $pendingAllocations->count() }} pending quarter allocation{{ $pendingAllocations->count() !== 1 ? 's' : '' }} requiring your action
                        </p>
                        @foreach($pendingAllocations as $allocation)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-b' : '' }}" style="border-color: #fbbf24;">
                                @if($allocation->quarter)
                                <p class="text-sm mb-2" style="color: #92400e;">
                                    <strong>Quarter {{ $allocation->quarter->quarter_number }}</strong> ({{ $allocation->quarter->quarter_type }}) allocated on {{ $allocation->created_at->format('d/m/Y H:i') }}
                                </p>
                                @endif
                                <div class="flex gap-2">
                                    <button 
                                        onclick="acceptAllocation({{ $allocation->id }})"
                                        class="kt-btn kt-btn-sm" style="background-color: #10b981; border-color: #10b981; color: #ffffff;">
                                        <i class="ki-filled ki-check"></i> Accept
                                    </button>
                                    <button 
                                        onclick="rejectAllocation({{ $allocation->id }})"
                                        class="kt-btn kt-btn-sm" style="background-color: #ef4444; border-color: #ef4444; color: #ffffff;">
                                        <i class="ki-filled ki-cross"></i> Reject
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Quick Actions Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Emolument Status</span>
                            <span class="text-2xl font-semibold text-mono" id="emolument-status">
                                {{ $emolumentStatus }}
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                            <i class="ki-filled ki-wallet text-2xl text-primary"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground" id="timeline-info">
                            @if($activeTimeline)
                                Timeline: {{ $activeTimeline->start_date->format('d/m/Y') }} -
                                {{ $activeTimeline->end_date->format('d/m/Y') }}
                            @else
                                No Active Timeline
                            @endif
                        </span>
                        <a class="kt-btn kt-btn-primary justify-center" href="{{ route('emolument.raise') }}">Raise
                            Emolument</a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Leave Balance</span>
                            <span class="text-2xl font-semibold text-mono" id="leave-balance">
                                {{ $leaveBalance }} Days
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                            <i class="ki-filled ki-calendar text-2xl text-success"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground">Annual Leave Available</span>
                        <a class="kt-btn kt-btn-success justify-center" href="{{ route('leave.apply') }}">Apply for
                            Leave</a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Pass Eligibility</span>
                            <span class="text-2xl font-semibold text-mono" id="pass-status">
                                {{ $passStatus }}
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                            <i class="ki-filled ki-calendar-tick text-2xl text-info"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground">Max 5 days per application</span>
                        <a class="kt-btn kt-btn-info justify-center" href="{{ route('pass.apply') }}">Apply for Pass</a>
                    </div>
                </div>
            </div>

            @if($officer && !$officer->quartered)
            <div class="kt-card">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Quarter Status</span>
                            <span class="text-2xl font-semibold text-mono">
                                Not Quartered
                            </span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                            <i class="ki-filled ki-home-2 text-2xl text-warning"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-xs text-secondary-foreground">Request accommodation</span>
                        <a class="kt-btn kt-btn-warning justify-center" href="{{ route('officer.quarter-requests.create') }}">
                            <i class="ki-filled ki-plus"></i> Request Quarter
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <!-- End of Quick Actions -->

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Recent Applications</h3>
                    <div class="kt-card-toolbar">
                        <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            View All
                        </a>
                    </div>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="recent-applications">
                        @forelse($recentApplications as $app)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-calendar text-primary"></i>
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-mono">{{ $app->type }}</span>
                                        <span class="text-xs text-secondary-foreground">Submitted:
                                            {{ $app->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                @php
                                    $statusClass = match ($app->status) {
                                        'APPROVED' => 'success',
                                        'PENDING' => 'warning',
                                        'REJECTED' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">{{ $app->status }}</span>
                            </div>
                        @empty
                            <p class="text-secondary-foreground text-center py-4">No recent applications</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-4 border-t border-border">
                        <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-outline w-full justify-center">
                            <i class="ki-filled ki-calendar-tick"></i> View Application History
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Roster</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="roster-info">
                        @if($officer)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Service Number</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->service_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Rank</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->substantive_rank ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Zone</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->zone->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Command</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Unit</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->unit ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Roster Role</span>
                                <span class="text-sm font-semibold text-mono">{{ $rosterRole ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-secondary-foreground">Roster Unit/Title</span>
                                <span class="text-sm font-semibold text-mono">{{ $currentRosterTitle ?? 'N/A' }}</span>
                            </div>
                        @else
                            <p class="text-secondary-foreground text-center py-4">Officer profile not found</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Recent Activities -->

        <!-- Course Nominations -->
        @if(($upcomingCourses && $upcomingCourses->count() > 0) || ($recentCourses && $recentCourses->count() > 0))
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Course Nominations</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('officer.course-nominations') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @if($upcomingCourses && $upcomingCourses->count() > 0)
                        @foreach($upcomingCourses as $course)
                            <div class="p-3 rounded-lg border border-info/20 bg-info/5">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex items-center justify-center size-10 rounded-full bg-info/10">
                                            <i class="ki-filled ki-book text-info"></i>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-sm font-medium text-foreground">{{ $course->course_name }}</span>
                                            <span class="text-xs text-secondary-foreground">
                                                Start: {{ $course->start_date->format('d/m/Y') }}
                                                @if($course->end_date)
                                                    • End: {{ $course->end_date->format('d/m/Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">Upcoming</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    
                    @if($recentCourses && $recentCourses->count() > 0)
                        @foreach($recentCourses->take($upcomingCourses && $upcomingCourses->count() > 0 ? (5 - $upcomingCourses->count()) : 5) as $course)
                            @if(!$upcomingCourses || !$upcomingCourses->contains('id', $course->id))
                                <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center size-10 rounded-full {{ $course->is_completed ? 'bg-success/10' : 'bg-warning/10' }}">
                                            <i class="ki-filled ki-book {{ $course->is_completed ? 'text-success' : 'text-warning' }}"></i>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-sm font-medium text-foreground">{{ $course->course_name }}</span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $course->start_date->format('d/m/Y') }}
                                                @if($course->is_completed && $course->completion_date)
                                                    • Completed: {{ $course->completion_date->format('d/m/Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-{{ $course->is_completed ? 'success' : 'warning' }} kt-badge-sm">
                                        {{ $course->is_completed ? 'Completed' : 'Pending' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-border">
                    <a href="{{ route('officer.course-nominations') }}" class="kt-btn kt-btn-outline w-full justify-center">
                        <i class="ki-filled ki-book"></i> View All Course Nominations
                    </a>
                </div>
            </div>
        </div>
        @endif
        <!-- End of Course Nominations -->

        <!-- Pending Quarter Allocations -->
        @if($pendingAllocations && $pendingAllocations->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending Quarter Allocations</h3>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @foreach($pendingAllocations as $allocation)
                        <div class="p-4 rounded-lg border border-warning/20 bg-warning/5">
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">Quarter Allocation</span>
                                        <span class="text-xs text-secondary-foreground">
                                            Allocated on: {{ $allocation->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">PENDING</span>
                                </div>
                                
                                @if($allocation->quarter)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-border">
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Quarter Number:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">{{ $allocation->quarter->quarter_number }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Quarter Type:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">{{ $allocation->quarter->quarter_type }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Allocation Date:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">{{ $allocation->allocated_date->format('d/m/Y') }}</span>
                                    </div>
                                    @if($allocation->allocatedBy && $allocation->allocatedBy->officer)
                                    <div>
                                        <span class="text-xs text-secondary-foreground">Allocated By:</span>
                                        <span class="text-sm font-semibold text-mono ml-2">{{ $allocation->allocatedBy->officer->full_name }}</span>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <div class="flex gap-2 pt-2">
                                    <button 
                                        onclick="acceptAllocation({{ $allocation->id }})"
                                        class="kt-btn kt-btn-success kt-btn-sm flex-1">
                                        <i class="ki-filled ki-check"></i> Accept
                                    </button>
                                    <button 
                                        onclick="rejectAllocation({{ $allocation->id }})"
                                        class="kt-btn kt-btn-danger kt-btn-sm flex-1">
                                        <i class="ki-filled ki-cross"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <!-- End of Pending Quarter Allocations -->

        <!-- Pending APER Form Assignments -->
        @if($pendingAperAssignments && $pendingAperAssignments->count() > 0)
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending APER Form Assignments</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-warning kt-badge-sm">{{ $pendingAperAssignments->count() }} Pending</span>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="flex flex-col gap-4">
                    @foreach($pendingAperAssignments as $form)
                        <div class="p-4 rounded-lg border border-warning/20 bg-warning/5">
                            <div class="flex flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                                            <i class="ki-filled ki-notepad-edit text-warning"></i>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $form->assignment_type }} - APER Form {{ $form->year }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                Officer: {{ $form->officer->initials }} {{ $form->officer->surname }} 
                                                ({{ $form->officer->service_number }})
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                Assigned: {{ $form->updated_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">Action Required</span>
                                </div>
                                
                                <div class="flex gap-2 pt-2">
                                    <a href="{{ route($form->assignment_route, $form->assignment_route_param) }}" 
                                       class="kt-btn kt-btn-warning kt-btn-sm flex-1">
                                        <i class="ki-filled ki-notepad-edit"></i> 
                                        {{ $form->assignment_type === 'Reporting Officer' ? 'Review Form' : 'Countersign Form' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-border">
                    <div class="flex gap-2">
                        @if($pendingAperAssignments->contains('assignment_type', 'Reporting Officer'))
                            <a href="{{ route('officer.aper-forms.search-officers') }}" class="kt-btn kt-btn-outline flex-1 justify-center">
                                <i class="ki-filled ki-notepad-edit"></i> Reporting Officer Forms
                            </a>
                        @endif
                        @if($pendingAperAssignments->contains('assignment_type', 'Countersigning Officer'))
                            <a href="{{ route('officer.aper-forms.countersigning.search') }}" class="kt-btn kt-btn-outline flex-1 justify-center">
                                <i class="ki-filled ki-check-circle"></i> Countersigning Forms
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- End of Pending APER Form Assignments -->
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Reject Quarter Allocation</h3>
            <form id="rejectionForm">
                <input type="hidden" id="allocationId" name="allocation_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Reason (Optional)</label>
                    <textarea 
                        id="rejectionReason" 
                        name="rejection_reason" 
                        rows="3" 
                        class="w-full border border-input rounded-md p-2"
                        placeholder="Enter reason for rejection..."
                        maxlength="500"></textarea>
                    <span class="text-xs text-secondary-foreground mt-1">Maximum 500 characters</span>
                </div>
                <div class="flex gap-2 justify-end">
                    <button 
                        type="button"
                        onclick="closeRejectionModal()"
                        class="kt-btn kt-btn-secondary kt-btn-sm">
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="kt-btn kt-btn-danger kt-btn-sm">
                        Reject Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function acceptAllocation(allocationId) {
            if (!confirm('Are you sure you want to accept this quarter allocation?')) {
                return;
            }

            // Use form submission for web route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/officer/quarters/allocations/${allocationId}/accept`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }

        function rejectAllocation(allocationId) {
            document.getElementById('allocationId').value = allocationId;
            document.getElementById('rejectionReason').value = '';
            document.getElementById('rejectionModal').classList.remove('hidden');
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.add('hidden');
        }

        document.getElementById('rejectionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const allocationId = document.getElementById('allocationId').value;
            const rejectionReason = document.getElementById('rejectionReason').value;

            // Use form submission for web route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/officer/quarters/allocations/${allocationId}/reject`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);
            
            if (rejectionReason) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'rejection_reason';
                reasonInput.value = rejectionReason;
                form.appendChild(reasonInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    </script>
@endsection
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
                    <h3 class="kt-card-title">Service Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-4" id="service-info">
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
                                <span class="text-sm text-secondary-foreground">Present Station</span>
                                <span class="text-sm font-semibold text-mono">{{ $officer->presentStation->name ?? 'N/A' }}</span>
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
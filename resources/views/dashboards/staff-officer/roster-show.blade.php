@extends('layouts.app')

@section('title', 'Duty Roster Details')
@section('page-title', 'Duty Roster Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.roster') }}">Duty Roster</a>
    <span>/</span>
    <span class="text-primary">View</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-cross-circle text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if(!$roster)
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">You don't have access to this roster.</p>
            </div>
        </div>
    </div>
@else
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-bold text-foreground">
                    @if($roster->unit)
                        {{ $roster->unit }} - 
                    @endif
                    Duty Roster Details
                </h1>
                <p class="text-sm text-secondary-foreground mt-1">
                    Period: {{ $roster->roster_period_start->format('M d, Y') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                </p>
            </div>
            <div class="flex-shrink-0 flex gap-2">
                @if($roster->status === 'DRAFT')
                    <a href="{{ route('staff-officer.roster.edit', $roster->id) }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil"></i> Edit
                    </a>
                @elseif($roster->status === 'APPROVED')
                    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#request-edit-modal">
                        <i class="ki-filled ki-pencil"></i> Request to Edit
                    </button>
                @endif
                <a href="{{ route('staff-officer.roster') }}" class="kt-btn kt-btn-outline">
                    <i class="ki-filled ki-left"></i> Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Roster Status</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex items-center gap-4 flex-wrap">
                            @php
                                $statusClass = match ($roster->status) {
                                    'ACTIVE' => 'success',
                                    'APPROVED' => 'success',
                                    'SUBMITTED' => 'warning',
                                    'DRAFT' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-lg">
                                {{ $roster->status }}
                            </span>
                            @if($roster->approved_at)
                                <span class="text-sm text-secondary-foreground">
                                    Approved: {{ $roster->approved_at->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Roster Details -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Roster Information</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($roster->unit)
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Unit</label>
                                <p class="text-sm text-foreground mt-1 font-semibold">{{ $roster->unit }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Command</label>
                                <p class="text-sm text-foreground mt-1">{{ $roster->command->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Period Start</label>
                                <p class="text-sm text-foreground mt-1">{{ $roster->roster_period_start->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Period End</label>
                                <p class="text-sm text-foreground mt-1">{{ $roster->roster_period_end->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Prepared By</label>
                                <p class="text-sm text-foreground mt-1">{{ $roster->preparedBy->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Total Assignments</label>
                                <p class="text-sm text-foreground mt-1">{{ $roster->assignments->count() }}</p>
                            </div>
                            @if($roster->oicOfficer)
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Officer in Charge (OIC)</label>
                                <p class="text-sm text-foreground mt-1">
                                    {{ $roster->oicOfficer->initials }} {{ $roster->oicOfficer->surname }} ({{ $roster->oicOfficer->service_number }})
                                </p>
                            </div>
                            @endif
                            @if($roster->secondInCommandOfficer)
                            <div>
                                <label class="text-sm font-medium text-secondary-foreground">Second In Command (2IC)</label>
                                <p class="text-sm text-foreground mt-1">
                                    {{ $roster->secondInCommandOfficer->initials }} {{ $roster->secondInCommandOfficer->surname }} ({{ $roster->secondInCommandOfficer->service_number }})
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assignments -->
                <div class="kt-card overflow-hidden">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Officer Assignments</h3>
                    </div>
                    <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                        @if($roster->assignments->count() > 0)
                            <!-- Table with horizontal scroll wrapper -->
                            <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                                <table class="kt-table" style="min-width: 700px; width: 100%;">
                                    <thead>
                                        <tr class="border-b border-border">
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Officer</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Shift</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roster->assignments as $assignment)
                                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                                <td class="py-3 px-4" style="white-space: nowrap;">
                                                    <span class="text-sm font-medium text-foreground">
                                                        {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                                        @if($roster->oic_officer_id == $assignment->officer_id)
                                                            <span class="kt-badge kt-badge-success kt-badge-sm ml-2">OIC</span>
                                                        @elseif($roster->second_in_command_officer_id == $assignment->officer_id)
                                                            <span class="kt-badge kt-badge-info kt-badge-sm ml-2">2IC</span>
                                                        @endif
                                                    </span>
                                                    <div class="text-xs text-secondary-foreground">
                                                        {{ $assignment->officer->service_number ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                                    {{ $assignment->shift ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 px-4">
                                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No assignments yet</p>
                                @if($roster->status === 'DRAFT')
                                    <a href="{{ route('staff-officer.roster.edit', $roster->id) }}" class="kt-btn kt-btn-primary">
                                        <i class="ki-filled ki-plus"></i> Add Assignments
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Actions</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex flex-col gap-3">
                            @if($roster->status === 'DRAFT')
                                <a href="{{ route('staff-officer.roster.edit', $roster->id) }}" class="kt-btn kt-btn-primary w-full">
                                    <i class="ki-filled ki-pencil"></i> Edit Roster
                                </a>
                            @elseif($roster->status === 'APPROVED')
                                <button type="button" class="kt-btn kt-btn-primary w-full" data-kt-modal-toggle="#request-edit-modal">
                                    <i class="ki-filled ki-pencil"></i> Request to Edit
                                </button>
                            @endif
                            @if($roster->status === 'DRAFT' && $roster->assignments->count() > 0)
                                <button type="button" class="kt-btn kt-btn-success w-full" data-kt-modal-toggle="#submit-modal">
                                        <i class="ki-filled ki-check"></i> Submit for Approval
                                    </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    /* Prevent page from expanding beyond viewport on mobile */
    @media (max-width: 768px) {
        body {
            overflow-x: hidden;
        }

        .kt-card {
            max-width: 100vw;
        }
    }

    /* Smooth scrolling for mobile */
    .table-scroll-wrapper {
        position: relative;
        max-width: 100%;
    }

    /* Custom scrollbar for webkit browsers */
    .scrollbar-thin::-webkit-scrollbar {
        height: 8px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<!-- Submit for Approval Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="submit-modal">
    <div class="kt-modal-content max-w-[400px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-success/10">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Submit for Approval</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to submit this roster for DC Admin approval? Once submitted, you will not be able to edit it until it is reviewed.
            </p>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <form action="{{ route('staff-officer.roster.submit', $roster->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="kt-btn kt-btn-success">
                    <i class="ki-filled ki-check"></i> Submit for Approval
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Request to Edit Confirmation Modal -->
<div class="kt-modal" data-kt-modal="true" id="request-edit-modal">
    <div class="kt-modal-content max-w-[400px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-warning/10">
                    <i class="ki-filled ki-information text-warning text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Request to Edit Roster</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            <p class="text-sm text-secondary-foreground mb-3">
                You are about to request to edit an approved roster. This will:
            </p>
            <ul class="text-sm text-secondary-foreground space-y-2 list-disc list-inside mb-3">
                <li>Change the roster status back to DRAFT</li>
                <li>Allow you to make changes to the roster</li>
                <li>Require re-approval before the changes take effect</li>
                <li>Notify only officers who are added or removed from the roster</li>
            </ul>
            <p class="text-sm text-secondary-foreground">
                Are you sure you want to proceed?
            </p>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">
                Cancel
            </button>
            <a href="{{ route('staff-officer.roster.edit', $roster->id) }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-pencil"></i> Proceed to Edit
            </a>
        </div>
    </div>
</div>
@endsection


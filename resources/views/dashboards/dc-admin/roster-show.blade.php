@extends('layouts.app')

@section('title', 'Duty Roster Details')
@section('page-title', 'Duty Roster Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.roster') }}">Duty Rosters</a>
    <span>/</span>
    <span class="text-primary">Review</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="flex items-center justify-between">
        <a href="{{ route('dc-admin.roster') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back
        </a>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Duty Roster Details</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Command</label>
                    <p class="text-sm text-foreground mt-1">{{ $roster->command->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Period</label>
                    <p class="text-sm text-foreground mt-1">
                        {{ $roster->roster_period_start->format('M d, Y') }} - {{ $roster->roster_period_end->format('M d, Y') }}
                    </p>
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

            <div class="border-t border-border pt-6">
                <h4 class="text-lg font-semibold mb-4">Officer Assignments</h4>
                @if($roster->assignments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Duty Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Shift</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roster->assignments as $assignment)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3 px-4 text-sm">
                                            {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                            @if($roster->oic_officer_id == $assignment->officer_id)
                                                <span class="kt-badge kt-badge-success kt-badge-sm ml-2">OIC</span>
                                            @elseif($roster->second_in_command_officer_id == $assignment->officer_id)
                                                <span class="kt-badge kt-badge-info kt-badge-sm ml-2">2IC</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm">{{ $assignment->duty_date->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $assignment->shift ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $assignment->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="border-t border-border pt-6 mt-6">
                <div class="flex gap-3">
                    <form action="{{ route('dc-admin.roster.approve', $roster->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="kt-btn kt-btn-success" onclick="return confirm('Approve this roster?')">
                            <i class="ki-filled ki-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="kt-btn kt-btn-danger" onclick="showRejectModal()">
                        <i class="ki-filled ki-cross"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="kt-card max-w-md w-full mx-4">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Reject Roster</h3>
        </div>
        <form action="{{ route('dc-admin.roster.reject', $roster->id) }}" method="POST" class="kt-card-content">
            @csrf
            <div class="flex flex-col gap-4">
                <div>
                    <label class="kt-form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="kt-input" rows="4" placeholder="Enter reason for rejection" required></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.querySelector('#reject-modal form').reset();
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection



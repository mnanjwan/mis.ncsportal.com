@extends('layouts.app')

@section('title', 'Manning Request Details')
@section('page-title', 'Manning Request Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.dashboard') }}">DC Admin</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('dc-admin.manning-level') }}">Manning Requests</a>
    <span>/</span>
    <span class="text-primary">Review</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="flex items-center justify-between">
        <a href="{{ route('dc-admin.manning-level') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back
        </a>
    </div>

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Manning Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</h3>
        </div>
        <div class="kt-card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Command</label>
                    <p class="text-sm text-foreground mt-1">{{ $request->command->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Zone</label>
                    <p class="text-sm text-foreground mt-1">{{ $request->command->zone->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Requested By</label>
                    <p class="text-sm text-foreground mt-1">{{ $request->requestedBy->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-secondary-foreground">Submitted Date</label>
                    <p class="text-sm text-foreground mt-1">{{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : 'N/A' }}</p>
                </div>
                @if($request->notes)
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-secondary-foreground">Notes</label>
                    <p class="text-sm text-foreground mt-1">{{ $request->notes }}</p>
                </div>
                @endif
            </div>

            <div class="border-t border-border pt-6">
                <h4 class="text-lg font-semibold mb-4">Manning Requirements</h4>
                @if($request->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Quantity</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Sex Requirement</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Qualification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($request->items as $item)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3 px-4 text-sm">{{ $item->rank }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $item->quantity_needed }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $item->sex_requirement === 'ANY' ? 'Any' : ($item->sex_requirement === 'M' ? 'Male' : 'Female') }}</td>
                                        <td class="py-3 px-4 text-sm">{{ $item->qualification_requirement ?? 'Any' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="border-t border-border pt-6 mt-6">
                <div class="flex gap-3">
                    <button type="button" class="kt-btn kt-btn-success" onclick="showApproveModal()">
                        <i class="ki-filled ki-check"></i> Approve
                    </button>
                    <button type="button" class="kt-btn kt-btn-danger" onclick="showRejectModal()">
                        <i class="ki-filled ki-cross"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="kt-card max-w-md w-full mx-4">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Approve Manning Request</h3>
        </div>
        <form action="{{ route('dc-admin.manning-level.approve', $request->id) }}" method="POST" class="kt-card-content">
            @csrf
            <div class="flex flex-col gap-4">
                <div>
                    <p class="text-sm text-secondary-foreground">Are you sure you want to approve this manning request? Once approved, it will be forwarded to HRD for officer matching.</p>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" class="kt-btn kt-btn-outline" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" class="kt-btn kt-btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="kt-card max-w-md w-full mx-4">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Reject Manning Request</h3>
        </div>
        <form action="{{ route('dc-admin.manning-level.reject', $request->id) }}" method="POST" class="kt-card-content">
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
function showApproveModal() {
    document.getElementById('approve-modal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approve-modal').classList.add('hidden');
}

function showRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.querySelector('#reject-modal form').reset();
}

document.getElementById('approve-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection


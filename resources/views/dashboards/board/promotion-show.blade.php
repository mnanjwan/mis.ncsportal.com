@extends('layouts.app')

@section('title', 'Promotion Eligibility List Review')
@section('page-title', 'Promotion Eligibility List Review')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('board.dashboard') }}">Board</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('board.promotions') }}">Promotions</a>
    <span>/</span>
    <span class="text-primary">Review</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('board.promotions') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Promotion Lists
        </a>
    </div>

    <!-- List Summary -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-2xl font-semibold text-mono">
                        Promotion Eligibility List (Year {{ $list->year ?? 'N/A' }})
                    </h2>
                    <span class="kt-badge kt-badge-sm kt-badge-warning">
                        {{ $list->status ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <span class="text-secondary-foreground">
                        Generated: <span class="font-semibold text-mono">{{ $list->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                    </span>
                    <span class="text-secondary-foreground">
                        Officers: <span class="font-semibold text-mono">{{ $list->items?->count() ?? 0 }}</span>
                    </span>
                    @if($list->generatedBy)
                        <span class="text-secondary-foreground">
                            By: <span class="font-semibold text-mono">{{ $list->generatedBy->email ?? 'N/A' }}</span>
                        </span>
                    @endif
                </div>
                <div class="text-sm text-secondary-foreground">
                    This list is ready for Board review. Bulk approval action will be available on this page.
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Approve (Selected Officers Only) -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Bulk Approve Promotions</h3>
        </div>
        <div class="kt-card-content">
            <form id="bulkApproveForm" action="{{ route('board.promotions.bulk-approve', $list->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-secondary-foreground mb-2">Promotion Effective Date</label>
                    <input type="date" name="promotion_date" class="kt-input w-full" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-foreground mb-2">Board Meeting Date (Optional)</label>
                    <input type="date" name="board_meeting_date" class="kt-input w-full" value="">
                </div>
                <div>
                    <button type="submit" class="kt-btn kt-btn-primary w-full" id="bulkApproveSubmitBtn">
                        <i class="ki-filled ki-check"></i> Approve Promotions
                    </button>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-secondary-foreground mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="kt-textarea w-full" placeholder="Optional notes for this promotion batch..."></textarea>
                </div>
            </form>
            <p class="text-xs text-secondary-foreground mt-3">
                This action will apply only to officers you select in the table below.
            </p>
        </div>
    </div>

    <!-- Officers -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Eligible Officers</h3>
        </div>
        <div class="kt-card-content">
            @if($list->items && $list->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" id="selectAllItems" class="kt-checkbox">
                                        <span>Select</span>
                                    </label>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">S/N</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Next Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Years in Rank</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list->items as $item)
                                @php
                                    $promotionService = app(\App\Services\PromotionService::class);
                                    $currentRank = $promotionService->normalizeRankToAbbreviation($item->officer?->substantive_rank ?? $item->current_rank);
                                    $nextRank = $promotionService->getNextRank($currentRank);
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        <input
                                            type="checkbox"
                                            class="kt-checkbox promotion-item-checkbox"
                                            name="selected_items[]"
                                            value="{{ $item->id }}"
                                            form="bulkApproveForm"
                                        >
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                        {{ $item->officer->service_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-foreground">
                                        {{ $item->officer ? (($item->officer->initials ?? '') . ' ' . ($item->officer->surname ?? '')) : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $currentRank ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $nextRank ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $item->years_in_rank ?? 0 }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $item->state ?? ($item->officer->state_of_origin ?? 'N/A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-secondary-foreground">No officers found in this eligibility list.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('bulkApproveForm');
    const selectAll = document.getElementById('selectAllItems');
    const checkboxes = Array.from(document.querySelectorAll('.promotion-item-checkbox'));

    function getSelectedCount() {
        return checkboxes.filter(cb => cb.checked).length;
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!selectAll) return;
            const allChecked = checkboxes.length > 0 && checkboxes.every(x => x.checked);
            const noneChecked = checkboxes.every(x => !x.checked);
            selectAll.indeterminate = !allChecked && !noneChecked;
            selectAll.checked = allChecked;
        });
    });

    if (!form) return;

    form.addEventListener('submit', (e) => {
        const selectedCount = getSelectedCount();
        if (selectedCount <= 0) {
            e.preventDefault();
            alert('Please select at least one officer to approve.');
            return;
        }

        // Confirmation modal (SweetAlert if available; fallback to confirm()).
        const promotionDate = form.querySelector('input[name=\"promotion_date\"]')?.value || '';
        const boardMeetingDate = form.querySelector('input[name=\"board_meeting_date\"]')?.value || '';

        const message = `
You are about to approve promotion for ${selectedCount} selected officer(s).

What this will do:
- Create/update promotion records for the selected officers
- Update each officer's rank to the “Next Rank” shown
- Set Date of Present Appointment to the promotion effective date
- Trigger the post-promotion profile-picture requirement for each approved officer

Promotion effective date: ${promotionDate || 'N/A'}
Board meeting date: ${boardMeetingDate || 'N/A'}
        `.trim();

        if (window.Swal && typeof window.Swal.fire === 'function') {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Bulk Approval',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endpush


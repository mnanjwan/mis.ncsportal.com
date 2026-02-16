@extends('layouts.app')

@section('title', 'Promotion Eligibility List Details')
@section('page-title', 'Promotion Eligibility List Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.promotion-eligibility') }}">Promotion Eligibility</a>
    <span>/</span>
    <span class="text-primary">View List</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Back Button -->
        <div class="flex items-center justify-between">
            <a href="{{ route('hrd.promotion-eligibility') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Promotion Eligibility Lists
            </a>
        </div>

        <!-- List Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-mono">Promotion Eligibility List - Year {{ $list->year ?? 'N/A' }}</h2>
                        <div class="flex items-center gap-3">
                            @php $status = $list->status ?? 'DRAFT'; @endphp
                            <span class="kt-badge kt-badge-sm {{
                                $status === 'SUBMITTED_TO_BOARD' ? 'kt-badge-warning' :
                                ($status === 'FINALIZED' ? 'kt-badge-success' : 'kt-badge-secondary')
                            }}">
                                {{ $status }}
                            </span>
                            <a href="{{ route('hrd.promotion-eligibility.export', $list->id) }}" 
                               class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-file-down"></i> Export CSV
                            </a>
                            <a href="{{ route('print.promotion-eligibility.print', $list->id) }}" 
                               class="kt-btn kt-btn-sm kt-btn-primary" 
                               target="_blank">
                                <i class="ki-filled ki-printer"></i> Print
                            </a>
                            @if(($list->items?->count() ?? 0) > 0)
                                @if(($list->status ?? 'DRAFT') === 'DRAFT')
                                    <form action="{{ route('hrd.promotion-eligibility.finalize', $list->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            Finalize
                                        </button>
                                    </form>
                                @elseif(($list->status ?? '') === 'FINALIZED')
                                    <form action="{{ route('hrd.promotion-eligibility.submit-to-board', $list->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                                            Submit to Board
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="text-secondary-foreground">
                            Generated: <span class="font-semibold text-mono">{{ $list->created_at->format('d/m/Y') }}</span>
                        </span>
                        @if($list->generatedBy)
                            <span class="text-secondary-foreground">
                                By: <span class="font-semibold text-mono">{{ $list->generatedBy->email ?? 'N/A' }}</span>
                            </span>
                        @endif
                        <span class="text-secondary-foreground">
                            Officers: <span class="font-semibold text-mono">{{ $list->items->count() ?? 0 }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officers List -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Eligible Officers</h3>
                <div class="kt-card-toolbar w-full sm:w-[520px]">
                    <div class="flex items-stretch gap-2 w-full">
                        <label for="eligibilitySearchInput" class="sr-only">Search eligible officers</label>
                        <input
                            id="eligibilitySearchInput"
                            type="text"
                            class="kt-input w-full flex-1"
                            placeholder="Live search (name, service number, rank, state...)"
                            autocomplete="off"
                        />
                        <button
                            id="eligibilitySearchClear"
                            type="button"
                            class="kt-btn kt-btn-outline whitespace-nowrap min-w-[90px] hidden"
                        >
                            Clear
                        </button>
                    </div>
                    <div class="mt-1 flex justify-end">
                        <span id="eligibilitySearchMeta" class="text-xs text-secondary-foreground whitespace-nowrap hidden sm:inline">
                            Showing {{ $list->items->count() ?? 0 }} of {{ $list->items->count() ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="kt-card-content">
                @if($list->items && $list->items->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block">
                        <div class="overflow-x-auto">
                            <table class="kt-table w-full">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">S/N</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Current Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Years in Rank</th>
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">State</th>
                                    </tr>
                                </thead>
                                <tbody id="eligibilityTableBody">
                                    @foreach($list->items as $item)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors eligibility-row">
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                                @if($item->officer)
                                                    {{ $item->officer->service_number ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-sm font-medium text-foreground">
                                                    @if($item->officer)
                                                        {{ $item->officer->initials ?? '' }} {{ $item->officer->surname ?? '' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $item->current_rank ?? ($item->officer->display_rank ?? 'N/A') }}
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $item->years_in_rank ?? 0 }} years
                                            </td>
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                {{ $item->state ?? ($item->officer->state_of_origin ?? 'N/A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr id="eligibilityNoResultsRow" class="hidden">
                                        <td colspan="6" class="py-8 px-4 text-sm text-secondary-foreground text-center">
                                            No officers match your search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="flex flex-col gap-4" id="eligibilityCards">
                            @foreach($list->items as $item)
                                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input eligibility-card">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                            <span class="text-sm font-semibold text-success">
                                                {{ $item->serial_number ?? $loop->iteration }}
                                            </span>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-semibold text-foreground">
                                                @if($item->officer)
                                                    {{ $item->officer->initials ?? '' }} {{ $item->officer->surname ?? '' }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                            <span class="text-xs text-secondary-foreground font-mono">
                                                SVC: {{ $item->officer->service_number ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $item->current_rank ?? ($item->officer->display_rank ?? 'N/A') }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                Years in Rank: {{ $item->years_in_rank ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="eligibilityNoResultsMobile" class="hidden text-center py-10 text-sm text-secondary-foreground">
                            No officers match your search.
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-arrow-up text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No officers in this eligibility list</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

@push('scripts')
<script>
(() => {
    const input = document.getElementById('eligibilitySearchInput');
    const clearBtn = document.getElementById('eligibilitySearchClear');
    const meta = document.getElementById('eligibilitySearchMeta');

    const rows = Array.from(document.querySelectorAll('.eligibility-row'));
    const cards = Array.from(document.querySelectorAll('.eligibility-card'));
    const noRow = document.getElementById('eligibilityNoResultsRow');
    const noMobile = document.getElementById('eligibilityNoResultsMobile');

    if (!input || (!rows.length && !cards.length)) return;

    const normalize = (s) => (s || '')
        .toString()
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();

    const rowIndex = rows.map((el) => ({ el, hay: normalize(el.textContent) }));
    const cardIndex = cards.map((el) => ({ el, hay: normalize(el.textContent) }));

    const total = Math.max(rowIndex.length, cardIndex.length);

    let t;
    const apply = () => {
        const q = normalize(input.value);

        let shown = 0;
        rowIndex.forEach(({ el, hay }) => {
            const match = !q || hay.includes(q);
            el.style.display = match ? '' : 'none';
            if (match) shown++;
        });

        cardIndex.forEach(({ el, hay }) => {
            const match = !q || hay.includes(q);
            el.style.display = match ? '' : 'none';
        });

        if (meta) {
            meta.textContent = `Showing ${shown} of ${total}`;
        }

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', !q);
        }

        if (noRow) {
            noRow.classList.toggle('hidden', shown !== 0);
        }
        if (noMobile) {
            noMobile.classList.toggle('hidden', shown !== 0);
        }
    };

    input.addEventListener('input', () => {
        window.clearTimeout(t);
        t = window.setTimeout(apply, 80);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            input.value = '';
            input.focus();
            apply();
        });
    }

    apply();
})();
</script>
@endpush
@endsection


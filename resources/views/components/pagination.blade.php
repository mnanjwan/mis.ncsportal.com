@props(['paginator', 'itemName' => 'items'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $total = $paginator->total();
    $from = $paginator->firstItem() ?? 0;
    $to = $paginator->lastItem() ?? 0;
    $hasPages = $paginator->hasPages();
@endphp

@if($hasPages)
    <div class="mt-6 pt-4 border-t border-border px-4 pb-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-sm text-secondary-foreground">
                Showing <span class="font-medium">{{ $from }}</span> to <span class="font-medium">{{ $to }}</span> of <span class="font-medium">{{ $total }}</span> {{ $itemName }}
            </div>
            <div class="flex items-center gap-1 flex-wrap justify-center">
                {{-- First & Previous buttons --}}
                @if($current > 1)
                    <a href="{{ $paginator->url(1) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-double-left"></i>
                    </a>
                    <a href="{{ $paginator->previousPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-left"></i> Previous
                    </a>
                @endif

                {{-- Page numbers --}}
                @php
                    $startPage = max(1, $current - 2);
                    $endPage = min($last, $current + 2);
                    
                    // Adjust if we're near the beginning
                    if ($current <= 3) {
                        $endPage = min(5, $last);
                    }
                    
                    // Adjust if we're near the end
                    if ($current >= $last - 2) {
                        $startPage = max(1, $last - 4);
                    }
                @endphp

                {{-- Show first page if not in range --}}
                @if($startPage > 1)
                    <a href="{{ $paginator->url(1) }}" class="kt-btn kt-btn-sm kt-btn-secondary">1</a>
                    @if($startPage > 2)
                        <span class="px-2 text-secondary-foreground">...</span>
                    @endif
                @endif

                {{-- Page number buttons --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    @if($i === $current)
                        <span class="kt-btn kt-btn-sm kt-btn-primary" style="pointer-events: none;">{{ $i }}</span>
                    @else
                        <a href="{{ $paginator->url($i) }}" class="kt-btn kt-btn-sm kt-btn-secondary">{{ $i }}</a>
                    @endif
                @endfor

                {{-- Show last page if not in range --}}
                @if($endPage < $last)
                    @if($endPage < $last - 1)
                        <span class="px-2 text-secondary-foreground">...</span>
                    @endif
                    <a href="{{ $paginator->url($last) }}" class="kt-btn kt-btn-sm kt-btn-secondary">{{ $last }}</a>
                @endif

                {{-- Next & Last buttons --}}
                @if($current < $last)
                    <a href="{{ $paginator->nextPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                        Next <i class="ki-filled ki-right"></i>
                    </a>
                    <a href="{{ $paginator->url($last) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                        <i class="ki-filled ki-double-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="mt-6 pt-4 border-t border-border px-4 pb-4">
        <div class="flex items-center justify-between">
            <div class="text-sm text-secondary-foreground">
                Showing <span class="font-medium">{{ $from }}</span> to <span class="font-medium">{{ $to }}</span> of <span class="font-medium">{{ $total }}</span> {{ $itemName }}
            </div>
        </div>
    </div>
@endif

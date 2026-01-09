@extends('layouts.app')

@section('title', 'ICT Dashboard')
@section('page-title', 'ICT Dashboard')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('ict.dashboard') }}">ICT</a>
    <span>/</span>
    <span class="text-primary">Email Management</span>
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
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Needing Email</span>
                        <span class="text-2xl font-semibold text-mono">{{ $stats['needing_email'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10">
                        <i class="ki-filled ki-message-2 text-2xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">With Customs Email</span>
                        <span class="text-2xl font-semibold text-mono">{{ $stats['with_customs_email'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                        <i class="ki-filled ki-check text-2xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Migrated</span>
                        <span class="text-2xl font-semibold text-mono">{{ $stats['migrated'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                        <i class="ki-filled ki-file-check text-2xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="kt-card">
            <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-normal text-secondary-foreground">Total Officers</span>
                        <span class="text-2xl font-semibold text-mono">{{ ($stats['needing_email'] ?? 0) + ($stats['with_customs_email'] ?? 0) + ($stats['migrated'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                        <i class="ki-filled ki-people text-2xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Officers Needing Email -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers Needing Email Creation</h3>
            <div class="kt-card-toolbar flex items-center gap-3">
                @if($officersNeedingEmail->count() > 0)
                    <form id="bulkCreateForm" action="{{ route('ict.bulk-create-emails') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="kt-btn kt-btn-primary"
                                onclick="return confirm('Are you sure you want to create emails for ALL officers? This action cannot be undone.')">
                            <i class="ki-filled ki-add-files"></i> Bulk Create All
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($officersNeedingEmail->count() > 0)
                <form id="emailForm" action="{{ route('ict.create-emails') }}" method="POST">
                    @csrf
                    <div class="flex items-center gap-3 p-4 border-b border-border">
                        <input type="checkbox" id="selectAll" class="kt-checkbox" onchange="toggleSelectAll(this)">
                        <label for="selectAll" class="text-sm font-medium text-foreground cursor-pointer">Select All</label>
                        <button type="submit" 
                                class="kt-btn kt-btn-sm kt-btn-primary"
                                onclick="return confirm('Are you sure you want to create emails for the selected officers?')">
                            <i class="ki-filled ki-message-add"></i> Create Emails for Selected
                        </button>
                    </div>
                    
                    <div class="table-scroll-wrapper overflow-x-auto">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <input type="checkbox" class="kt-checkbox" disabled>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer Name</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Personal Email</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Customs Email</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Email Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Station</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($officersNeedingEmail as $officer)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <input type="checkbox" 
                                                   name="officer_ids[]" 
                                                   value="{{ $officer->id }}" 
                                                   class="kt-checkbox officer-checkbox">
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-medium text-foreground">{{ $officer->service_number ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-foreground">
                                                    {{ ($officer->initials ?? '') . ' ' . ($officer->surname ?? '') }}
                                                </span>
                                                @if($officer->rank)
                                                    <span class="text-xs text-secondary-foreground">{{ $officer->rank->name ?? '' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">{{ $officer->personal_email ?? ($officer->email ?? 'N/A') }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($officer->customs_email)
                                                <span class="text-sm text-foreground">{{ $officer->customs_email }}</span>
                                            @else
                                                <span class="text-sm text-muted-foreground italic">
                                                    {{ strtolower($officer->service_number ?? '') }}@customs.gov.ng
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($officer->email_status === 'personal')
                                                <span class="kt-badge kt-badge-warning">Personal</span>
                                            @elseif($officer->email_status === 'customs')
                                                <span class="kt-badge kt-badge-success">Customs</span>
                                            @elseif($officer->email_status === 'migrated')
                                                <span class="kt-badge kt-badge-info">Migrated</span>
                                            @else
                                                <span class="kt-badge kt-badge-secondary">None</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">{{ $officer->presentStation->name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6 pt-4 border-t border-border px-4 pb-4">
                        @if($officersNeedingEmail->hasPages())
                            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                <div class="text-sm text-secondary-foreground">
                                    Showing <span class="font-medium">{{ $officersNeedingEmail->firstItem() }}</span> to <span class="font-medium">{{ $officersNeedingEmail->lastItem() }}</span> of <span class="font-medium">{{ $officersNeedingEmail->total() }}</span> officers
                                </div>
                                <div class="flex items-center gap-1 flex-wrap justify-center">
                                    {{-- First & Previous buttons --}}
                                    @if($officersNeedingEmail->currentPage() > 1)
                                        <a href="{{ $officersNeedingEmail->url(1) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-double-left"></i>
                                        </a>
                                        <a href="{{ $officersNeedingEmail->previousPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-left"></i> Previous
                                        </a>
                                    @endif

                                    {{-- Page numbers --}}
                                    @php
                                        $current = $officersNeedingEmail->currentPage();
                                        $last = $officersNeedingEmail->lastPage();
                                        $startPage = max(1, $current - 2);
                                        $endPage = min($last, $current + 2);
                                        
                                        if ($current <= 3) {
                                            $endPage = min(5, $last);
                                        }
                                        
                                        if ($current >= $last - 2) {
                                            $startPage = max(1, $last - 4);
                                        }
                                    @endphp

                                    @if($startPage > 1)
                                        <a href="{{ $officersNeedingEmail->url(1) }}" class="kt-btn kt-btn-sm kt-btn-secondary">1</a>
                                        @if($startPage > 2)
                                            <span class="px-2 text-secondary-foreground">...</span>
                                        @endif
                                    @endif

                                    @for($i = $startPage; $i <= $endPage; $i++)
                                        @if($i == $current)
                                            <span class="kt-btn kt-btn-sm kt-btn-primary" style="pointer-events: none;">{{ $i }}</span>
                                        @else
                                            <a href="{{ $officersNeedingEmail->url($i) }}" class="kt-btn kt-btn-sm kt-btn-secondary">{{ $i }}</a>
                                        @endif
                                    @endfor

                                    @if($endPage < $last)
                                        @if($endPage < $last - 1)
                                            <span class="px-2 text-secondary-foreground">...</span>
                                        @endif
                                        <a href="{{ $officersNeedingEmail->url($last) }}" class="kt-btn kt-btn-sm kt-btn-secondary">{{ $last }}</a>
                                    @endif

                                    {{-- Next & Last buttons --}}
                                    @if($current < $last)
                                        <a href="{{ $officersNeedingEmail->nextPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            Next <i class="ki-filled ki-right"></i>
                                        </a>
                                        <a href="{{ $officersNeedingEmail->url($last) }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-double-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-secondary-foreground">
                                    Showing <span class="font-medium">{{ $officersNeedingEmail->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $officersNeedingEmail->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $officersNeedingEmail->total() }}</span> officers
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                    <p class="text-secondary-foreground text-lg font-medium mb-2">All officers have been processed!</p>
                    <p class="text-sm text-muted-foreground">No officers are currently needing email creation.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.officer-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// Update select all checkbox state when individual checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.officer-checkbox');
    const selectAll = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>
@endsection


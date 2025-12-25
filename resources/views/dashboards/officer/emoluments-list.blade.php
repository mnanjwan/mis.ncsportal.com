@extends('layouts.app')

@section('title', 'My Emoluments')
@section('page-title', 'My Emoluments')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Emoluments</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Emoluments List Card -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Emolument History</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('emolument.raise') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Raise New Emolument
                </a>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 800px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Year
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Submitted
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Assessed
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Validated
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($emoluments as $emolument)
                                @php
                                    $statusClass = match($emolument->status) {
                                        'RAISED' => 'warning',
                                        'ASSESSED' => 'info',
                                        'VALIDATED' => 'success',
                                        'PROCESSED' => 'success',
                                        'REJECTED' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $emolument->year }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                            {{ $emolument->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $emolument->submitted_at ? $emolument->submitted_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $emolument->validated_at ? $emolument->validated_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        <a href="{{ route('emolument.show', $emolument->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No emoluments found</p>
                                        <a href="{{ route('emolument.raise') }}" class="kt-btn kt-btn-primary">
                                            Raise Your First Emolument
                                        </a>
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
                    @forelse($emoluments as $emolument)
                        @php
                            $statusClass = match($emolument->status) {
                                'RAISED' => 'warning',
                                'ASSESSED' => 'info',
                                'VALIDATED' => 'success',
                                'PROCESSED' => 'success',
                                'REJECTED' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-wallet text-primary text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">Year: {{ $emolument->year }}</span>
                                    <span class="text-xs text-secondary-foreground">
                                        Submitted: {{ $emolument->submitted_at ? $emolument->submitted_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    @if($emolument->assessed_at)
                                        <span class="text-xs text-secondary-foreground">
                                            Assessed: {{ $emolument->assessed_at->format('d/m/Y') }}
                                        </span>
                                    @endif
                                    @if($emolument->validated_at)
                                        <span class="text-xs text-secondary-foreground">
                                            Validated: {{ $emolument->validated_at->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                    {{ $emolument->status }}
                                </span>
                                <a href="{{ route('emolument.show', $emolument->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-wallet text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No emoluments found</p>
                            <a href="{{ route('emolument.raise') }}" class="kt-btn kt-btn-primary">
                                Raise Your First Emolument
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($emoluments->hasPages())
                <div class="mt-6 pt-4 border-t border-border px-4">
                    {{ $emoluments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

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
@endsection

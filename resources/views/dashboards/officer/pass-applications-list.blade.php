@extends('layouts.app')

@section('title', 'My Pass Applications')
@section('page-title', 'My Pass Applications')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Pass Applications</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-success">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm font-medium text-danger">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

<div class="grid gap-5 lg:gap-7.5">
    <!-- Pass Applications List Card -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Pass Application History</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('pass.apply') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Apply for Pass
                </a>
            </div>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 700px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Days
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Period
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Applied
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($passes as $pass)
                                @php
                                    $statusClass = match($pass->status) {
                                        'APPROVED' => 'success',
                                        'REJECTED' => 'danger',
                                        'CANCELLED' => 'secondary',
                                        default => 'warning'
                                    };
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $pass->number_of_days }} days
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $pass->start_date->format('d/m/Y') }} - {{ $pass->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                            {{ $pass->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $pass->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                        <a href="{{ route('officer.pass-applications.show', $pass->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="View Details">
                                            <i class="ki-filled ki-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No pass applications found</p>
                                        <a href="{{ route('pass.apply') }}" class="kt-btn kt-btn-primary">
                                            Apply for Pass
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
                    @forelse($passes as $pass)
                        @php
                            $statusClass = match($pass->status) {
                                'APPROVED' => 'success',
                                'REJECTED' => 'danger',
                                'CANCELLED' => 'secondary',
                                default => 'warning'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                    <i class="ki-filled ki-calendar-tick text-success text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">{{ $pass->number_of_days }} days</span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $pass->start_date->format('d/m/Y') }} to {{ $pass->end_date->format('d/m/Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Applied: {{ $pass->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                    {{ $pass->status }}
                                </span>
                                <a href="{{ route('officer.pass-applications.show', $pass->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                   title="View Details">
                                    <i class="ki-filled ki-eye"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No pass applications found</p>
                            <a href="{{ route('pass.apply') }}" class="kt-btn kt-btn-primary">
                                Apply for Pass
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($passes->hasPages())
                <div class="mt-6 pt-4 border-t border-border px-4">
                    {{ $passes->links() }}
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

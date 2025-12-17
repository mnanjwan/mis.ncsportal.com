@extends('layouts.app')

@section('title', 'My Pass Applications')
@section('page-title', 'My Pass Applications')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Pass Applications</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Pass Applications List Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Pass Application History</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('pass.apply') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Apply for Pass
                </a>
            </div>
        </div>
        <div class="kt-card-content">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Days
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Period
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Applied
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
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
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $pass->number_of_days }} days
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $pass->start_date->format('d/m/Y') }} - {{ $pass->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                            {{ $pass->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $pass->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('officer.pass-applications.show', $pass->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground mb-4"></i>
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
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
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
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $passes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

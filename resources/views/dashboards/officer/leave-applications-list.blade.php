@extends('layouts.app')

@section('title', 'My Leave Applications')
@section('page-title', 'My Leave Applications')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Officer</a>
    <span>/</span>
    <span class="text-primary">Leave Applications</span>
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
    <!-- Leave Applications List Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Leave Application History</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('leave.apply') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Apply for Leave
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
                                    Leave Type
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Period
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Days
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Submitted
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                @php
                                    $statusClass = match($application->status) {
                                        'APPROVED' => 'success',
                                        'REJECTED' => 'danger',
                                        'CANCELLED' => 'secondary',
                                        default => 'warning'
                                    };
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $application->leaveType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $application->number_of_days ?? 'N/A' }} days
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                            {{ $application->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $application->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('officer.leave-applications.show', $application->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="View Details">
                                            <i class="ki-filled ki-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No leave applications found</p>
                                        <a href="{{ route('leave.apply') }}" class="kt-btn kt-btn-primary">
                                            <i class="ki-filled ki-plus"></i> Apply for Leave
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
                    @forelse($applications as $application)
                        @php
                            $statusClass = match($application->status) {
                                'APPROVED' => 'success',
                                'REJECTED' => 'danger',
                                'CANCELLED' => 'secondary',
                                default => 'warning'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-calendar text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $application->leaveType->name ?? 'N/A' }} - {{ $application->number_of_days ?? 'N/A' }} days
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $application->start_date->format('d/m/Y') }} to {{ $application->end_date->format('d/m/Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Applied: {{ $application->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                    {{ $application->status }}
                                </span>
                                <a href="{{ route('officer.leave-applications.show', $application->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-calendar text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No leave applications found</p>
                            <a href="{{ route('leave.apply') }}" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-plus"></i> Apply for Leave
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($applications->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

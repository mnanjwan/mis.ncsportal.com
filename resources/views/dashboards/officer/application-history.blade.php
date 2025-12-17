@extends('layouts.app')

@section('title', 'Application History')
@section('page-title', 'Application History')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('officer.dashboard') }}">Home</a>
    <span>/</span>
    <span class="text-primary">Application History</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Applications</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('officer.application-history') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Type Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Type</label>
                            <select name="type" class="kt-input w-full">
                                <option value="">All Types</option>
                                <option value="leave" {{ request('type') == 'leave' ? 'selected' : '' }}>Leave</option>
                                <option value="pass" {{ request('type') == 'pass' ? 'selected' : '' }}>Pass</option>
                            </select>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Status</label>
                            <select name="status" class="kt-input w-full">
                                <option value="">All Statuses</option>
                                <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                                <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                                <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <!-- Year Select -->
                        <div class="w-full md:w-36">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['type', 'status', 'year']))
                                <a href="{{ route('officer.application-history') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Application History</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $applications->total() }} records
                    </span>
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
                                        Type
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Leave/Pass Type
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
                                        $statusClass = match ($application->status) {
                                            'APPROVED' => 'success',
                                            'REJECTED' => 'danger',
                                            'CANCELLED' => 'secondary',
                                            default => 'warning'
                                        };
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $application->application_type }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $application->type_name }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $application->start_date->format('d/m/Y') }} - 
                                            {{ $application->end_date->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $application->number_of_days }} days
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $application->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $application->submitted_date ? $application->submitted_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($application->application_type === 'Leave')
                                                <a href="{{ route('officer.leave-applications.show', $application->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @else
                                                <a href="{{ route('officer.pass-applications.show', $application->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No applications found</p>
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
                                $statusClass = match ($application->status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                    'CANCELLED' => 'secondary',
                                    default => 'warning'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full {{ $application->application_type === 'Leave' ? 'bg-info/10' : 'bg-primary/10' }}">
                                        <i class="ki-filled ki-{{ $application->application_type === 'Leave' ? 'calendar' : 'wallet' }} {{ $application->application_type === 'Leave' ? 'text-info' : 'text-primary' }} text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $application->application_type }} - {{ $application->type_name }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $application->number_of_days }} days
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Submitted: {{ $application->submitted_date ? $application->submitted_date->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                        {{ $application->status }}
                                    </span>
                                    @if($application->application_type === 'Leave')
                                        <a href="{{ route('officer.leave-applications.show', $application->id) }}"
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    @else
                                        <a href="{{ route('officer.pass-applications.show', $application->id) }}"
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No applications found</p>
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


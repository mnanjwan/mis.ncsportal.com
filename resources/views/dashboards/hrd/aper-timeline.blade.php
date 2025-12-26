@extends('layouts.app')

@section('title', 'APER Timeline')
@section('page-title', 'APER Timeline')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">APER Timeline</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Timelines List Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">APER Timelines</h3>
            <div class="kt-card-toolbar">
                <a href="{{ route('hrd.aper-timeline.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Create New Timeline
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
                                    Year
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Period
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Description
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Status
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Created
                                </th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timelines as $timeline)
                                @php
                                    $statusClass = match($timeline->is_active ? 'ACTIVE' : 'INACTIVE') {
                                        'ACTIVE' => 'success',
                                        'COMPLETED' => 'info',
                                        default => 'secondary'
                                    };
                                    $status = $timeline->is_active ? 'ACTIVE' : 'INACTIVE';
                                @endphp
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $timeline->year }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $timeline->start_date->format('d/m/Y') }} - {{ $timeline->end_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ \Illuminate\Support\Str::limit($timeline->description ?? 'No description', 50) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $timeline->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($timeline->is_active)
                                            <a href="{{ route('hrd.aper-timeline.extend', $timeline->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                Extend
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <i class="ki-filled ki-calendar-2 text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No timelines found</p>
                                        <a href="{{ route('hrd.aper-timeline.create') }}" class="kt-btn kt-btn-primary">
                                            Create First Timeline
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
                    @forelse($timelines as $timeline)
                        @php
                            $statusClass = match($timeline->is_active ? 'ACTIVE' : 'INACTIVE') {
                                'ACTIVE' => 'success',
                                'COMPLETED' => 'info',
                                default => 'secondary'
                            };
                            $status = $timeline->is_active ? 'ACTIVE' : 'INACTIVE';
                        @endphp
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                    <i class="ki-filled ki-calendar-2 text-primary text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $timeline->year }}: {{ $timeline->start_date->format('d/m/Y') }} - {{ $timeline->end_date->format('d/m/Y') }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ \Illuminate\Support\Str::limit($timeline->description ?? 'No description', 60) }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Created: {{ $timeline->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                    {{ $status }}
                                </span>
                                @if($timeline->is_active)
                                    <a href="{{ route('hrd.aper-timeline.extend', $timeline->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost">
                                        Extend
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                    <div class="text-center py-12">
                        <i class="ki-filled ki-calendar-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No timelines found</p>
                            <a href="{{ route('hrd.aper-timeline.create') }}" class="kt-btn kt-btn-primary">
                            Create First Timeline
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($timelines->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $timelines->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


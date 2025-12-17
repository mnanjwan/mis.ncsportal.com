@extends('layouts.app')

@section('title', 'Manning Level')
@section('page-title', 'Manning Level')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Manning Level</span>
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

@if(!$command)
    <div class="kt-card">
        <div class="kt-card-content">
            <div class="text-center py-12">
                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                <p class="text-secondary-foreground">You are not assigned to a command. Please contact HRD for command assignment.</p>
            </div>
        </div>
    </div>
@else
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Actions -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-foreground">Manning Level Requests</h2>
            <a href="{{ route('staff-officer.manning-level.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i> Create Request
            </a>
        </div>
        
        <!-- Manning Level Requests -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requests for {{ $command->name }}</h3>
            </div>
            <div class="kt-card-content">
                @forelse($requests as $request)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted mb-4 last:mb-0">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                <i class="ki-filled ki-people text-primary text-xl"></i>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-semibold text-foreground">
                                    Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-xs text-secondary-foreground">
                                    {{ $request->items->count() }} requirement(s) | 
                                    Created: {{ $request->created_at->format('M d, Y') }}
                                </span>
                                @if($request->items->count() > 0)
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($request->items->take(3) as $item)
                                            <span class="text-xs px-2 py-1 rounded bg-muted text-secondary-foreground">
                                                {{ $item->rank ?? 'N/A' }} ({{ $item->quantity_needed }})
                                            </span>
                                        @endforeach
                                        @if($request->items->count() > 3)
                                            <span class="text-xs text-secondary-foreground">+{{ $request->items->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            @php
                                $statusColors = [
                                    'DRAFT' => 'secondary',
                                    'SUBMITTED' => 'warning',
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'danger',
                                    'FULFILLED' => 'info',
                                ];
                                $statusColor = $statusColors[$request->status] ?? 'secondary';
                            @endphp
                            <span class="kt-badge kt-badge-{{ $statusColor }} kt-badge-sm">{{ $request->status }}</span>
                            <a href="{{ route('staff-officer.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                <i class="ki-filled ki-eye"></i> View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No manning level requests found</p>
                        <a href="{{ route('staff-officer.manning-level.create') }}" class="kt-btn kt-btn-primary">
                            Create First Request
                        </a>
                    </div>
                @endforelse
            </div>
            @if($requests->hasPages())
                <div class="kt-card-content border-t border-input pt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
@endif
@endsection

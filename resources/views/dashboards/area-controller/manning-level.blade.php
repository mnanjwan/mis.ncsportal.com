@extends('layouts.app')

@section('title', 'Manning Requests')
@section('page-title', 'Manning Requests - Pending Approval')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    <span>/</span>
    <span class="text-primary">Manning Requests</span>
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

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Submitted Manning Requests</h3>
        </div>
        <div class="kt-card-content">
            @if(isset($requests) && $requests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="kt-table w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Request ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Zone</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Requested By</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Submitted Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Items</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="text-sm font-medium text-foreground">#{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $request->command->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $request->command->zone->name ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $request->requestedBy->email ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->submitted_at ? $request->submitted_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $request->items->count() }} items</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('area-controller.manning-level.show', $request->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No submitted manning requests found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


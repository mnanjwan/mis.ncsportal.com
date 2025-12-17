@extends('layouts.app')

@section('title', 'Manning Request Details')
@section('page-title', 'Manning Request Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests') }}">Manning Requests</a>
    <span>/</span>
    <span class="text-primary">View Details</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrd.manning-requests') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Manning Requests
        </a>
    </div>

    <!-- Request Header -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-mono">Manning Request #{{ $request->id }}</h2>
                    <span class="kt-badge kt-badge-success kt-badge-sm">
                        {{ $request->status }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-secondary-foreground">Command:</span>
                        <span class="font-semibold text-mono ml-2">{{ $request->command->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">Requested By:</span>
                        <span class="font-semibold text-mono ml-2">{{ $request->requestedBy->email ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">Approved By:</span>
                        <span class="font-semibold text-mono ml-2">{{ $request->approvedBy->initials ?? '' }} {{ $request->approvedBy->surname ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-foreground">Approved Date:</span>
                        <span class="font-semibold text-mono ml-2">{{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
                @if($request->notes)
                    <div class="pt-4 border-t border-border">
                        <p class="text-sm text-secondary-foreground">
                            <strong>Notes:</strong> {{ $request->notes }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Request Items -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Manning Requirements</h3>
        </div>
        <div class="kt-card-content">
            @if($request->items && $request->items->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Quantity</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Sex</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Qualification</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($request->items as $item)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $item->rank }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $item->quantity_needed }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $item->sex_requirement }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $item->qualification_requirement ?? 'Any' }}</td>
                                        <td class="py-3 px-4">
                                            @if($item->matched_officer_id)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Matched</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if(!$item->matched_officer_id)
                                                <a href="{{ route('hrd.manning-requests.match', ['id' => $request->id, 'item_id' => $item->id]) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                                    <i class="ki-filled ki-search"></i> Find Matches
                                                </a>
                                            @else
                                                <span class="text-xs text-secondary-foreground">Already Matched</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @foreach($request->items as $item)
                            <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-foreground">{{ $item->rank }}</span>
                                    @if($item->matched_officer_id)
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Matched</span>
                                    @else
                                        <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground mb-3">
                                    <div>Quantity: <span class="font-semibold">{{ $item->quantity_needed }}</span></div>
                                    <div>Sex: <span class="font-semibold">{{ $item->sex_requirement }}</span></div>
                                    <div class="col-span-2">Qualification: <span class="font-semibold">{{ $item->qualification_requirement ?? 'Any' }}</span></div>
                                </div>
                                @if(!$item->matched_officer_id)
                                    <a href="{{ route('hrd.manning-requests.match', ['id' => $request->id, 'item_id' => $item->id]) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-primary w-full">
                                        <i class="ki-filled ki-search"></i> Find Matches
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No requirements found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


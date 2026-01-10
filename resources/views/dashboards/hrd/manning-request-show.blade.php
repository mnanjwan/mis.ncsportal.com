@extends('layouts.app')

@section('title', 'Manning Request Details')
@section('page-title', 'Manning Request Details')

@section('breadcrumbs')
    @php
        $routePrefix = $routePrefix ?? 'hrd';
        $dashboardRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.dashboard') : route('hrd.dashboard');
        $manningRequestsRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-requests') : route('hrd.manning-requests');
        $breadcrumbLabel = $routePrefix === 'zone-coordinator' ? 'Zone Coordinator' : 'HRD';
    @endphp
    <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">{{ $breadcrumbLabel }}</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ $manningRequestsRoute }}">Manning Requests</a>
    <span>/</span>
    <span class="text-primary">View Details</span>
@endsection

@section('content')
@php
    $routePrefix = $routePrefix ?? 'hrd';
    $manningRequestsRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-requests') : route('hrd.manning-requests');
    $draftRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-deployments.draft') : route('hrd.manning-deployments.draft');
    $printRoute = $routePrefix === 'zone-coordinator' ? '#' : route('hrd.manning-requests.print', $request->id);
@endphp
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ $manningRequestsRoute }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Manning Requests
        </a>
        @if($routePrefix !== 'zone-coordinator')
        <a href="{{ $printRoute }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary">
            <i class="ki-filled ki-printer"></i> Print
        </a>
        @endif
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

    <!-- Summary Card -->
    @php
        $totalItems = $request->items->count();
        $publishedItems = $request->items->whereNotNull('matched_officer_id')->count();
        $draftItems = isset($itemsInDraft) ? $itemsInDraft->count() : 0;
        $pendingItems = $totalItems - $publishedItems - $draftItems;
    @endphp
    <div class="kt-card">
        <div class="kt-card-content p-5">
            <h3 class="text-lg font-semibold mb-4">Request Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-lg bg-warning/10 border border-warning/20">
                    <div class="text-sm text-secondary-foreground mb-1">Pending Matching</div>
                    <div class="text-2xl font-semibold text-warning">{{ $pendingItems }}</div>
                </div>
                <div class="p-4 rounded-lg bg-info/10 border border-info/20">
                    <div class="text-sm text-secondary-foreground mb-1">In Draft</div>
                    <div class="text-2xl font-semibold text-info">{{ $draftItems }}</div>
                </div>
                <div class="p-4 rounded-lg bg-success/10 border border-success/20">
                    <div class="text-sm text-secondary-foreground mb-1">Published</div>
                    <div class="text-2xl font-semibold text-success">{{ $publishedItems }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Items -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Manning Requirements</h3>
        </div>
        <div class="kt-card-content">
            <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-sm text-info">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Note:</strong> When you find matches, officers will be added to a draft deployment. Review and adjust the draft before publishing to finalize the deployment.
                </p>
            </div>
            @php
                $pendingItemsCount = $request->items->whereNull('matched_officer_id')
                    ->whereNotIn('id', $itemsInDraft ?? [])
                    ->count();
                $draftItemsCount = isset($itemsInDraft) ? $itemsInDraft->count() : 0;
            @endphp
            <div class="mb-4 flex items-center justify-end gap-3">
                @php
                    $routePrefix = $routePrefix ?? 'hrd';
                @endphp
                @if($pendingItemsCount > 0)
                    <button type="button" 
                            data-kt-modal-toggle="#match-all-ranks-modal" 
                            class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-search"></i> Find Matches for All Ranks ({{ $pendingItemsCount }})
                    </button>
                @endif
                @if($draftItemsCount > 0)
                    <a href="{{ $draftRoute }}" 
                       class="kt-btn kt-btn-info">
                        <i class="ki-filled ki-file-add"></i> View in Draft ({{ $draftItemsCount }})
                    </a>
                @endif
            </div>
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
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Published</span>
                                            @elseif(isset($itemsInDraft) && $itemsInDraft->contains($item->id))
                                                <span class="kt-badge kt-badge-info kt-badge-sm">In Draft</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
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
                                        <span class="kt-badge kt-badge-success kt-badge-sm">Published</span>
                                    @elseif(isset($itemsInDraft) && $itemsInDraft->contains($item->id))
                                        <span class="kt-badge kt-badge-info kt-badge-sm">In Draft</span>
                                    @else
                                        <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground">
                                    <div>Quantity: <span class="font-semibold">{{ $item->quantity_needed }}</span></div>
                                    <div>Sex: <span class="font-semibold">{{ $item->sex_requirement }}</span></div>
                                    <div class="col-span-2">Qualification: <span class="font-semibold">{{ $item->qualification_requirement ?? 'Any' }}</span></div>
                                </div>
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

<!-- Match All Ranks Confirmation Modal -->
@php
    $routePrefix = $routePrefix ?? 'hrd';
@endphp
@if($pendingItemsCount > 0)
<div class="kt-modal" data-kt-modal="true" id="match-all-ranks-modal">
    <div class="kt-modal-content max-w-[500px]">
        <div class="kt-modal-header py-4 px-5">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-full bg-primary/10">
                    <i class="ki-filled ki-search text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Find Matches for All Ranks</h3>
            </div>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body py-5 px-5">
            @php
                $routePrefix = $routePrefix ?? 'hrd';
                $matchAllRoute = $routePrefix === 'zone-coordinator' 
                    ? route('zone-coordinator.manning-requests.match-all', $request->id)
                    : route('hrd.manning-requests.match-all', $request->id);
            @endphp
            <form id="match-all-ranks-form" method="POST" action="{{ $matchAllRoute }}">
                @csrf
                <p class="text-sm text-secondary-foreground mb-4">
                    This will automatically match and add officers for all <strong>{{ $pendingItemsCount }}</strong> pending rank(s) to the draft deployment at once.
                </p>
                <div class="p-3 bg-info/10 border border-info/20 rounded-lg">
                    <p class="text-xs text-info">
                        <i class="ki-filled ki-information"></i> 
                        <strong>Note:</strong> Officers will be automatically matched and added to the draft. You can review and adjust the draft before publishing.
                    </p>
                </div>
            </form>
        </div>
        <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
            <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
            <button type="button" class="kt-btn kt-btn-primary" onclick="document.getElementById('match-all-ranks-form').submit();">
                <i class="ki-filled ki-search"></i> Find Matches for All Ranks
            </button>
        </div>
    </div>
</div>
@endif
@endsection


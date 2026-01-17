@extends('layouts.app')

@section('title', 'Manning Requests')
@section('page-title', 'Manning Requests')

@section('breadcrumbs')
    @php
        $routePrefix = $routePrefix ?? 'hrd';
        $dashboardRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.dashboard') : route('hrd.dashboard');
        $breadcrumbLabel = $routePrefix === 'zone-coordinator' ? 'Zone Coordinator' : 'HRD';
    @endphp
    <a class="text-secondary-foreground hover:text-primary" href="{{ $dashboardRoute }}">{{ $breadcrumbLabel }}</a>
    <span>/</span>
    <span class="text-primary">Manning Requests</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="kt-card bg-success/10 border border-success/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="kt-card bg-danger/10 border border-danger/20">
            <div class="kt-card-content p-4">
                <div class="flex items-center gap-3">
                    <i class="ki-filled ki-information text-danger text-xl"></i>
                    <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Header Actions -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-semibold text-mono">Manning Requests</h2>
            <p class="text-sm text-secondary-foreground mt-1">Manage approved manning requests and officer matching</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $routePrefix = $routePrefix ?? 'hrd';
                $draftRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-deployments.draft') : route('hrd.manning-deployments.draft');
                $publishedRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-deployments.published') : route('hrd.manning-deployments.published');
                $printSelectedRoute = $routePrefix === 'zone-coordinator' ? '#' : route('hrd.manning-requests.print-selected');
            @endphp
            @if($routePrefix !== 'zone-coordinator')
            <button id="print-selected-btn" class="kt-btn kt-btn-sm kt-btn-secondary hidden" onclick="printSelected()">
                <i class="ki-filled ki-printer"></i> Print Selected
            </button>
            @endif
            <a href="{{ $draftRoute }}" class="kt-btn kt-btn-sm kt-btn-primary">
                <i class="ki-filled ki-file-add"></i> Draft Deployment
            </a>
            <a href="{{ $publishedRoute }}" class="kt-btn kt-btn-sm kt-btn-secondary">
                <i class="ki-filled ki-check"></i> Published Deployments
            </a>
        </div>
    </div>

    <!-- Manning Requests Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            @php
                $routePrefix = $routePrefix ?? 'hrd';
                $title = $routePrefix === 'zone-coordinator' ? 'Approved Manning Requests (Zone Type Only)' : 'Approved Manning Requests (General Type Only)';
                $badge = $routePrefix === 'zone-coordinator' ? 'Zone Manning Level' : 'General Manning Level';
                $note = $routePrefix === 'zone-coordinator' 
                    ? 'This page shows only Zone Manning Level requests (GL 7 and below) for commands in your zone. General Manning Level requests are handled by HRD.'
                    : 'This page shows only General Manning Level requests (all ranks). Zone Manning Level requests (GL 7 and below) are handled by Zone Coordinators via Movement Orders.';
            @endphp
            <h3 class="kt-card-title">{{ $title }}</h3>
            <div class="kt-card-toolbar">
                <span class="kt-badge kt-badge-info kt-badge-sm">{{ $badge }}</span>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-sm text-info">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Note:</strong> {{ $note }}
                </p>
            </div>
            <!-- Tabs -->
            <div class="flex border-b border-border mb-5">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'pending']) }}" 
                   class="px-4 py-2 text-sm font-medium border-b-2 {{ request('tab', 'pending') === 'pending' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-primary' }} flex items-center gap-2">
                    Pending
                    <span class="kt-badge kt-badge-sm {{ request('tab', 'pending') === 'pending' ? 'kt-badge-primary' : 'kt-badge-ghost' }}">
                        {{ $pendingCount ?? 0 }}
                    </span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'in_draft']) }}" 
                   class="px-4 py-2 text-sm font-medium border-b-2 {{ request('tab') === 'in_draft' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-primary' }} flex items-center gap-2">
                    In Draft
                    <span class="kt-badge kt-badge-sm {{ request('tab') === 'in_draft' ? 'kt-badge-info' : 'kt-badge-ghost' }}">
                        {{ $inDraftCount ?? 0 }}
                    </span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'published']) }}" 
                   class="px-4 py-2 text-sm font-medium border-b-2 {{ request('tab') === 'published' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-primary' }} flex items-center gap-2">
                    Published
                    <span class="kt-badge kt-badge-sm {{ request('tab') === 'published' ? 'kt-badge-success' : 'kt-badge-ghost' }}">
                        {{ $publishedCount ?? 0 }}
                    </span>
                </a>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground w-12">
                                        <input type="checkbox" id="select-all" onchange="toggleAll(this)">
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'command', 'sort_order' => request('sort_by') === 'command' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Command
                                            @if(request('sort_by') === 'command')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Requested By
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Date Requested
                                            @if(request('sort_by') === 'created_at' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' || !request('sort_order') ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Items
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'approved_at', 'sort_order' => request('sort_by') === 'approved_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Approved Date
                                            @if(request('sort_by') === 'approved_at')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <input type="checkbox" class="request-checkbox" value="{{ $request->id }}" onchange="updatePrintButton()">
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $request->command->name ?? 'N/A' }}
                                            </span>
                                            @if(isset($request->has_items_in_draft) && $request->has_items_in_draft)
                                                <span class="kt-badge kt-badge-info kt-badge-sm" title="Has items in draft deployment">
                                                    <i class="ki-filled ki-file-add"></i> In Draft
                                                </span>
                                            @endif
                                            @if(isset($request->is_published) && $request->is_published)
                                                <span class="kt-badge kt-badge-success kt-badge-sm" title="All items have been published">
                                                    <i class="ki-filled ki-check"></i> Published
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->requestedBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->created_at ? $request->created_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->items->count() }} requirement(s)
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->approved_at ? $request->approved_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @php
                                            $routePrefix = $routePrefix ?? 'hrd';
                                            $showRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-requests.show', $request->id) : route('hrd.manning-requests.show', $request->id);
                                            $draftRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-deployments.draft') : route('hrd.manning-deployments.draft');
                                        @endphp
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ $showRoute }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View Details
                                            </a>
                                            @if(isset($request->has_items_in_draft) && $request->has_items_in_draft)
                                                <a href="{{ $draftRoute }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-info">
                                                    <i class="ki-filled ki-file-add"></i> View Draft
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">
                                            @if(request('tab') === 'in_draft')
                                                No manning requests with items in draft found
                                            @elseif(request('tab') === 'published')
                                                No published manning requests found
                                            @else
                                                No pending manning requests found
                                            @endif
                                        </p>
                                        <p class="text-xs text-secondary-foreground">
                                            @if(request('tab') === 'in_draft')
                                                Requests with items in draft deployment will appear here.
                                            @elseif(request('tab') === 'published')
                                                Fully published requests will appear here.
                                            @else
                                                Approved requests from Area Controllers will appear here.
                                            @endif
                                        </p>
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
                    @forelse($requests as $request)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" class="request-checkbox" value="{{ $request->id }}" onchange="updatePrintButton()">
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-people text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $request->command->name ?? 'N/A' }}
                                        </span>
                                        @if(isset($request->has_items_in_draft) && $request->has_items_in_draft)
                                            <span class="kt-badge kt-badge-info kt-badge-sm" title="Has items in draft deployment">
                                                <i class="ki-filled ki-file-add"></i> In Draft
                                            </span>
                                        @endif
                                        @if(isset($request->is_published) && $request->is_published)
                                            <span class="kt-badge kt-badge-success kt-badge-sm" title="All items have been published">
                                                <i class="ki-filled ki-check"></i> Published
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $request->items->count() }} requirement(s)
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Requested: {{ $request->created_at ? $request->created_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Approved: {{ $request->approved_at ? $request->approved_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $routePrefix = $routePrefix ?? 'hrd';
                                    $showRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-requests.show', $request->id) : route('hrd.manning-requests.show', $request->id);
                                    $draftRoute = $routePrefix === 'zone-coordinator' ? route('zone-coordinator.manning-deployments.draft') : route('hrd.manning-deployments.draft');
                                @endphp
                                <a href="{{ $showRoute }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View Details
                                </a>
                                @if(isset($request->has_items_in_draft) && $request->has_items_in_draft)
                                    <a href="{{ $draftRoute }}" 
                                       class="kt-btn kt-btn-sm kt-btn-info">
                                        <i class="ki-filled ki-file-add"></i> View Draft
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">
                                @if(request('tab') === 'in_draft')
                                    No manning requests with items in draft found
                                @elseif(request('tab') === 'published')
                                    No published manning requests found
                                @else
                                    No pending manning requests found
                                @endif
                            </p>
                            <p class="text-xs text-secondary-foreground">
                                @if(request('tab') === 'in_draft')
                                    Requests with items in draft deployment will appear here.
                                @elseif(request('tab') === 'published')
                                    Fully published requests will appear here.
                                @else
                                    Approved requests from Area Controllers will appear here.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <x-pagination :paginator="$requests->withQueryString()" item-name="requests" />
        </div>
    </div>
</div>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updatePrintButton();
}

function updatePrintButton() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const printBtn = document.getElementById('print-selected-btn');
    if (checkboxes.length > 0) {
        printBtn.classList.remove('hidden');
    } else {
        printBtn.classList.add('hidden');
    }
}

@php
    $routePrefix = $routePrefix ?? 'hrd';
    $printSelectedUrl = $routePrefix === 'zone-coordinator' ? '#' : route('hrd.manning-requests.print-selected');
@endphp
function printSelected() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one manning request to print.');
        return;
    }
    
    // Open print page in new window (only for HRD)
    const url = '{{ $printSelectedUrl }}?ids=' + selectedIds.join(',');
    if (url !== '#?ids=' + selectedIds.join(',')) {
        window.open(url, '_blank');
    } else {
        alert('Print selected is only available for HRD.');
    }
}
</script>
@endsection


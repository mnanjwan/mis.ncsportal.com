@extends('layouts.app')

@section('title', 'Manning Requests')
@section('page-title', 'Manning Requests')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
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

    <!-- Manning Requests Card -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Approved Manning Requests</h3>
        </div>
        <div class="kt-card-content">
            <p class="text-sm text-secondary-foreground mb-4">
                View approved manning requests and trigger matching to find suitable officers for posting.
            </p>

            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
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
                                        Items
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'approved_at', 'sort_order' => request('sort_by') === 'approved_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Approved Date
                                            @if(request('sort_by') === 'approved_at' || !request('sort_by'))
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
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $request->command->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->requestedBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->items->count() }} requirement(s)
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $request->approved_at ? $request->approved_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('hrd.manning-requests.show', $request->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                                        <p class="text-secondary-foreground mb-4">No approved manning requests found</p>
                                        <p class="text-xs text-secondary-foreground">
                                            Approved requests from Area Controllers will appear here.
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
                                <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                    <i class="ki-filled ki-people text-info text-xl"></i>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ $request->command->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        {{ $request->items->count() }} requirement(s)
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Approved: {{ $request->approved_at ? $request->approved_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="{{ route('hrd.manning-requests.show', $request->id) }}" 
                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                    View
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground mb-4">No approved manning requests found</p>
                            <p class="text-xs text-secondary-foreground">
                                Approved requests from Area Controllers will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="mt-6 pt-4 border-t border-border">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


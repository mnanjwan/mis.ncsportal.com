@extends('layouts.app')

@section('title', 'Movement Orders')
@section('page-title', 'Movement Orders')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Movement Orders</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Movement Orders List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Movement Orders</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.movement-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Movement Order
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'order_number', 'sort_order' => request('sort_by') === 'order_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Order Number
                                            @if(request('sort_by') === 'order_number')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'criteria', 'sort_order' => request('sort_by') === 'criteria' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Criteria (Months)
                                            @if(request('sort_by') === 'criteria')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Manning Request
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Status
                                            @if(request('sort_by') === 'status')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Created
                                            @if(request('sort_by') === 'created_at' || !request('sort_by'))
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
                                @forelse($orders as $order)
                                    @php
                                        $statusClass = match($order->status ?? 'DRAFT') {
                                            'PUBLISHED' => 'success',
                                            'CANCELLED' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $order->order_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->criteria_months_at_station ?? 'N/A' }} months
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->manningRequest ? 'Request #' . $order->manningRequest->id : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $order->status ?? 'DRAFT' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('hrd.movement-orders.show', $order->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground mb-4">No movement orders found</p>
                                            <a href="{{ route('hrd.movement-orders.create') }}" class="kt-btn kt-btn-primary">
                                                Create First Movement Order
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
                        @forelse($orders as $order)
                            @php
                                $statusClass = match($order->status ?? 'DRAFT') {
                                    'PUBLISHED' => 'success',
                                    'CANCELLED' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-abstract-26 text-primary text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $order->order_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Criteria: {{ $order->criteria_months_at_station ?? 'N/A' }} months
                                        </span>
                                        @if($order->manningRequest)
                                            <span class="text-xs text-secondary-foreground">
                                                Manning Request #{{ $order->manningRequest->id }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-secondary-foreground">
                                            Created: {{ $order->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                        {{ $order->status ?? 'DRAFT' }}
                                    </span>
                                    <a href="{{ route('hrd.movement-orders.show', $order->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No movement orders found</p>
                                <a href="{{ route('hrd.movement-orders.create') }}" class="kt-btn kt-btn-primary">
                                    Create First Movement Order
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


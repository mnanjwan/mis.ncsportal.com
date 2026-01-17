@extends('layouts.app')

@section('title', 'Movement Orders')
@section('page-title', 'Movement Orders')

@section('breadcrumbs')
    @if(isset($routePrefix) && $routePrefix === 'zone-coordinator')
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.dashboard') }}">Zone Coordinator</a>
    @else
        <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    @endif
    <span>/</span>
    <span class="text-primary">Movement Orders</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Movement Orders List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Movement Orders</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Movement Order
                    </a>
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Mobile scroll hint -->
                <div class="block md:hidden px-4 py-3 bg-muted/50 border-b border-border">
                    <div class="flex items-center gap-2 text-xs text-secondary-foreground">
                        <i class="ki-filled ki-arrow-left-right"></i>
                        <span>Swipe left to view more columns</span>
                    </div>
                </div>

                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
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
                                            @php
                                                $isPublished = ($order->status ?? 'DRAFT') === 'PUBLISHED';
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium {{ $isPublished ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-800 border border-gray-200' }}">
                                                {{ $order->status ?? 'DRAFT' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.show', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                                @if($order->status !== 'CANCELLED' && $order->status !== 'PUBLISHED')
                                                    <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.edit', $order->id) }}" 
                                                       class="kt-btn kt-btn-sm kt-btn-secondary">
                                                        Edit
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground mb-4">No movement orders found</p>
                                            <a href="{{ route(($routePrefix ?? 'hrd') . '.movement-orders.create') }}" class="kt-btn kt-btn-primary">
                                                Create First Movement Order
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>

                <!-- Pagination -->
                <x-pagination :paginator="$orders->withQueryString()" item-name="orders" />
            </div>
        </div>
    </div>

    <style>
        /* Prevent page from expanding beyond viewport on mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .kt-card {
                max-width: 100vw;
            }
        }

        /* Smooth scrolling for mobile */
        .table-scroll-wrapper {
            position: relative;
            max-width: 100%;
        }

        /* Custom scrollbar for webkit browsers */
        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endsection


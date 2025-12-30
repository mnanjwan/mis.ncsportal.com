@extends('layouts.app')

@section('title', 'Internal Staff Orders')
@section('page-title', 'Internal Staff Orders')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Internal Staff Orders</span>
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

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-foreground">Internal Staff Orders</h2>
                @if($command)
                    <p class="text-sm text-secondary-foreground mt-1">{{ $command->name }}</p>
                @endif
            </div>
            <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i> Create Internal Staff Order
            </a>
        </div>

        <!-- Internal Staff Orders List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Internal Staff Orders</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-info kt-badge-sm">{{ $orders->total() }} Order(s)</span>
                </div>
            </div>
            <div class="kt-card-content">
                <!-- Search Form -->
                <form method="GET" action="{{ route('staff-officer.internal-staff-orders.index') }}" class="mb-4">
                    <div class="flex gap-2">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by order number or description..." 
                               class="kt-input flex-1">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('staff-officer.internal-staff-orders.index') }}" class="kt-btn kt-btn-ghost">
                                <i class="ki-filled ki-cross"></i> Clear
                            </a>
                        @endif
                    </div>
                </form>

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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'order_date', 'sort_order' => request('sort_by') === 'order_date' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Order Date
                                            @if(request('sort_by') === 'order_date')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Description</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Created
                                            @if(request('sort_by') === 'created_at')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $order->order_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">
                                                {{ Str::limit($order->description ?? 'N/A', 50) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('staff-officer.internal-staff-orders.show', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="View">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                                                   target="_blank"
                                                   class="kt-btn kt-btn-sm kt-btn-primary"
                                                   title="Print">
                                                    <i class="ki-filled ki-printer"></i>
                                                </a>
                                                <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="Edit">
                                                    <i class="ki-filled ki-pencil"></i>
                                                </a>
                                                <form action="{{ route('staff-officer.internal-staff-orders.destroy', $order->id) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this internal staff order?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="kt-btn kt-btn-sm kt-btn-danger"
                                                            title="Delete">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 px-4 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="ki-filled ki-information-2 text-3xl text-muted-foreground"></i>
                                                <p class="text-secondary-foreground">No internal staff orders found.</p>
                                                <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-2">
                                                    <i class="ki-filled ki-plus"></i> Create Your First Internal Staff Order
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4">
                    @forelse($orders as $order)
                        <div class="kt-card">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-foreground mb-1">{{ $order->order_number }}</h4>
                                        <p class="text-xs text-secondary-foreground">
                                            Date: {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if($order->description)
                                    <p class="text-sm text-foreground mb-3">{{ Str::limit($order->description, 100) }}</p>
                                @endif

                                <div class="flex items-center gap-2 pt-3 border-t border-border">
                                    <a href="{{ route('staff-officer.internal-staff-orders.show', $order->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost flex-1">
                                        <i class="ki-filled ki-eye"></i> View
                                    </a>
                                    <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                                       target="_blank"
                                       class="kt-btn kt-btn-sm kt-btn-primary flex-1">
                                        <i class="ki-filled ki-printer"></i> Print
                                    </a>
                                    <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-pencil"></i>
                                    </a>
                                    <form action="{{ route('staff-officer.internal-staff-orders.destroy', $order->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this internal staff order?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-danger">
                                            <i class="ki-filled ki-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="kt-card">
                            <div class="kt-card-content p-8 text-center">
                                <i class="ki-filled ki-information-2 text-3xl text-muted-foreground mb-3"></i>
                                <p class="text-secondary-foreground mb-4">No internal staff orders found.</p>
                                <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus"></i> Create Your First Internal Staff Order
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Internal Staff Orders')
@section('page-title', 'Internal Staff Orders')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Internal Staff Orders</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Internal Staff Orders List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Internal Staff Orders - {{ $command->name }}</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Internal Staff Order
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
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Description
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Created At
                                            @if(request('sort_by') === 'created_at')
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
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $order->order_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">
                                                {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ Str::limit($order->description ?? 'No description', 50) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('staff-officer.internal-staff-orders.show', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="View">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="Edit">
                                                    <i class="ki-filled ki-pencil"></i>
                                                </a>
                                                <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   target="_blank"
                                                   title="Print">
                                                    <i class="ki-filled ki-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <i class="ki-filled ki-file-up text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground mb-4">No internal staff orders found</p>
                                            <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary">
                                                Create First Internal Staff Order
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
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-info/10">
                                        <i class="ki-filled ki-file-up text-info text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">
                                            {{ $order->order_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Date: {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}
                                        </span>
                                        @if($order->description)
                                            <span class="text-xs text-secondary-foreground">
                                                {{ Str::limit($order->description, 40) }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-secondary-foreground">
                                            Created: {{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('staff-officer.internal-staff-orders.show', $order->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="View">
                                            <i class="ki-filled ki-eye"></i>
                                        </a>
                                        <a href="{{ route('staff-officer.internal-staff-orders.edit', $order->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           title="Edit">
                                            <i class="ki-filled ki-pencil"></i>
                                        </a>
                                        <a href="{{ route('print.internal-staff-order', $order->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost"
                                           target="_blank"
                                           title="Print">
                                            <i class="ki-filled ki-printer"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-file-up text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No internal staff orders found</p>
                                <a href="{{ route('staff-officer.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary">
                                    Create First Internal Staff Order
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


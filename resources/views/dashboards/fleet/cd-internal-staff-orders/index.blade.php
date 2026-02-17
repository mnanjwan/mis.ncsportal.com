@extends('layouts.app')

@section('title', 'Internal Staff Orders (Transport)')
@section('page-title', 'Internal Staff Orders (Transport)')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('fleet.cd.dashboard') }}">Fleet CD</a>
    <span>/</span>
    <span class="text-primary">Internal Staff Orders (Transport)</span>
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
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-foreground">Internal Staff Orders (Transport Officers)</h2>
            <a href="{{ route('fleet.cd.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus"></i> Create Internal Staff Order
            </a>
        </div>

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Internal Staff Orders (Transport)</h3>
                <div class="kt-card-toolbar">
                    <span class="kt-badge kt-badge-info kt-badge-sm">{{ $orders->total() }} Order(s)</span>
                </div>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('fleet.cd.internal-staff-orders.index') }}" class="mb-4">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by order number or description..." class="kt-input flex-1">
                        <div class="flex gap-2">
                            <button type="submit" class="kt-btn kt-btn-primary flex-1 sm:flex-none">
                                <i class="ki-filled ki-magnifier"></i> <span class="hidden sm:inline">Search</span><span class="sm:hidden">Search</span>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('fleet.cd.internal-staff-orders.index') }}" class="kt-btn kt-btn-ghost">
                                    <i class="ki-filled ki-cross"></i> <span class="hidden sm:inline">Clear</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order Number</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Target Unit</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Created</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4"><span class="text-sm font-medium text-foreground">{{ $order->order_number ?? 'N/A' }}</span></td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="py-3 px-4">
                                            @if($order->officer)
                                                <span class="text-sm text-foreground">{{ $order->officer->initials }} {{ $order->officer->surname }}<br><span class="text-xs text-secondary-foreground">{{ $order->officer->service_number }}</span></span>
                                            @else
                                                <span class="text-sm text-secondary-foreground">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">{{ $order->target_unit ?? 'N/A' }}<br>@if($order->target_role)<span class="text-xs text-secondary-foreground">Role: {{ $order->target_role }}</span>@endif</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($order->status === 'DRAFT')<span class="kt-badge kt-badge-info">DRAFT</span>
                                            @elseif($order->status === 'PENDING_APPROVAL')<span class="kt-badge kt-badge-warning">PENDING</span>
                                            @elseif($order->status === 'APPROVED')<span class="kt-badge kt-badge-success">APPROVED</span>
                                            @elseif($order->status === 'REJECTED')<span class="kt-badge kt-badge-danger">REJECTED</span>
                                            @else<span class="kt-badge kt-badge-secondary">{{ $order->status ?? 'N/A' }}</span>@endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('fleet.cd.internal-staff-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost" title="View"><i class="ki-filled ki-eye"></i></a>
                                                <a href="{{ route('print.internal-staff-order', $order->id) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-primary" title="Print"><i class="ki-filled ki-printer"></i></a>
                                                @if($order->status === 'DRAFT')
                                                    <a href="{{ route('fleet.cd.internal-staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                                    <button type="button" class="kt-btn kt-btn-sm kt-btn-danger" title="Delete" data-kt-modal-toggle="#delete-order-modal-{{ $order->id }}"><i class="ki-filled ki-trash"></i></button>
                                                    <div class="kt-modal" data-kt-modal="true" id="delete-order-modal-{{ $order->id }}">
                                                        <div class="kt-modal-content max-w-[400px]">
                                                            <div class="kt-modal-header py-4 px-5">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="flex items-center justify-center size-10 rounded-full bg-danger/10"><i class="ki-filled ki-information text-danger text-xl"></i></div>
                                                                    <h3 class="text-lg font-semibold text-foreground">Delete Order</h3>
                                                                </div>
                                                                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
                                                            </div>
                                                            <div class="kt-modal-body py-5 px-5">
                                                                <p class="text-sm text-secondary-foreground">Are you sure you want to delete this internal staff order? This action cannot be undone.</p>
                                                            </div>
                                                            <div class="kt-modal-footer py-4 px-5 flex items-center justify-end gap-2.5">
                                                                <button class="kt-btn kt-btn-secondary" data-kt-modal-dismiss="true">Cancel</button>
                                                                <form action="{{ route('fleet.cd.internal-staff-orders.destroy', $order->id) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="kt-btn kt-btn-danger"><i class="ki-filled ki-trash"></i> Delete Order</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 px-4 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="ki-filled ki-information-2 text-3xl text-muted-foreground"></i>
                                                <p class="text-secondary-foreground">No internal staff orders for Transport officers found.</p>
                                                <a href="{{ route('fleet.cd.internal-staff-orders.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-2"><i class="ki-filled ki-plus"></i> Create Internal Staff Order</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="lg:hidden space-y-4">
                    @forelse($orders as $order)
                        <div class="kt-card">
                            <div class="kt-card-content p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-foreground mb-1">{{ $order->order_number }}</h4>
                                        <p class="text-xs text-secondary-foreground">Date: {{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($order->officer)
                                    <p class="text-sm text-foreground mb-2"><strong>Officer:</strong> {{ $order->officer->initials }} {{ $order->officer->surname }} ({{ $order->officer->service_number }})</p>
                                @endif
                                @if($order->target_unit)
                                    <p class="text-sm text-foreground mb-2"><strong>Target:</strong> {{ $order->target_unit }} @if($order->target_role)({{ $order->target_role }})@endif</p>
                                @endif
                                <div class="mb-3">
                                    @if($order->status === 'DRAFT')<span class="kt-badge kt-badge-info">DRAFT</span>
                                    @elseif($order->status === 'PENDING_APPROVAL')<span class="kt-badge kt-badge-warning">PENDING</span>
                                    @elseif($order->status === 'APPROVED')<span class="kt-badge kt-badge-success">APPROVED</span>
                                    @elseif($order->status === 'REJECTED')<span class="kt-badge kt-badge-danger">REJECTED</span>@endif
                                </div>
                                <div class="flex items-center gap-2 pt-3 border-t border-border">
                                    <a href="{{ route('fleet.cd.internal-staff-orders.show', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost flex-1"><i class="ki-filled ki-eye"></i> View</a>
                                    <a href="{{ route('print.internal-staff-order', $order->id) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-primary flex-1"><i class="ki-filled ki-printer"></i> Print</a>
                                    @if($order->status === 'DRAFT')
                                        <a href="{{ route('fleet.cd.internal-staff-orders.edit', $order->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost"><i class="ki-filled ki-pencil"></i></a>
                                        <form action="{{ route('fleet.cd.internal-staff-orders.destroy', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this order?');">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-danger"><i class="ki-filled ki-trash"></i></button></form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="kt-card">
                            <div class="kt-card-content p-8 text-center">
                                <i class="ki-filled ki-information-2 text-3xl text-muted-foreground mb-3"></i>
                                <p class="text-secondary-foreground mb-4">No internal staff orders for Transport officers found.</p>
                                <a href="{{ route('fleet.cd.internal-staff-orders.create') }}" class="kt-btn kt-btn-primary"><i class="ki-filled ki-plus"></i> Create Internal Staff Order</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($orders->hasPages())
                    <div class="mt-4">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

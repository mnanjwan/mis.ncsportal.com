@extends('layouts.app')

@section('title', 'Controller Procurement Dashboard')
@section('page-title', 'Controller Procurement Dashboard')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Draft Procurements</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['draft'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 text-warning">
                            <i class="ki-filled ki-notepad-edit text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Submitted</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['submitted'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-info/10 text-info">
                            <i class="ki-filled ki-send text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Approved</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['approved'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-success/10 text-success">
                            <i class="ki-filled ki-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-normal text-secondary-foreground">Received</span>
                            <span class="text-2xl font-semibold text-mono">{{ $stats['received'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-center size-12 rounded-full bg-primary/10 text-primary">
                            <i class="ki-filled ki-package text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Quick Actions</h3>
            </div>
            <div class="kt-card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <a href="{{ route('pharmacy.procurements.create') }}"
                       class="kt-btn kt-btn-primary w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-plus"></i>
                        New Procurement
                    </a>
                    <a href="{{ route('pharmacy.procurements.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-document"></i>
                        All Procurements
                    </a>
                    <a href="{{ route('pharmacy.drugs.index') }}"
                       class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                        <i class="ki-filled ki-pill"></i>
                        Drug Catalog
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Procurements -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">My Recent Procurements</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('pharmacy.procurements.index') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($myProcurements->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myProcurements as $procurement)
                                    <tr>
                                        <td>
                                            <span class="font-medium">{{ $procurement->reference_number ?? 'DRAFT' }}</span>
                                        </td>
                                        <td>{{ $procurement->items->count() }} items</td>
                                        <td>
                                            <span class="kt-badge kt-badge-{{ $procurement->status === 'APPROVED' ? 'success' : ($procurement->status === 'REJECTED' ? 'danger' : ($procurement->status === 'DRAFT' ? 'warning' : 'info')) }} kt-badge-sm">
                                                {{ $procurement->status }}
                                            </span>
                                        </td>
                                        <td>{{ $procurement->submitted_at ? $procurement->submitted_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('pharmacy.procurements.show', $procurement->id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                                <i class="ki-filled ki-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-document text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No procurements created yet.</p>
                        <a href="{{ route('pharmacy.procurements.create') }}" class="kt-btn kt-btn-primary mt-4">
                            Create Your First Procurement
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

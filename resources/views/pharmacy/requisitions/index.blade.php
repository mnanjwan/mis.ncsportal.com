@extends('layouts.app')

@section('title', 'Requisitions')
@section('page-title', 'Pharmacy Requisitions')
@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="#">Pharmacy</a>
    <span>/</span>
    <span class="text-primary">Requisitions</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
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

        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requisitions</h3>
                <div class="kt-card-toolbar flex items-center gap-2">
                    <form method="GET" class="flex items-center gap-2">
                        <select name="status" class="kt-input kt-input-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="SUBMITTED" {{ $status === 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                            <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            <option value="ISSUED" {{ $status === 'ISSUED' ? 'selected' : '' }}>Issued</option>
                            <option value="DISPENSED" {{ $status === 'DISPENSED' ? 'selected' : '' }}>Dispensed</option>
                        </select>
                    </form>
                    @if(auth()->user()->hasRole('Command Pharmacist'))
                        <a href="{{ route('pharmacy.requisitions.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i>
                            New Requisition
                        </a>
                    @endif
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($requisitions->count() > 0)
                    <!-- Swipe hint for mobile -->
                    <div class="px-5 pb-5 lg:hidden">
                        <div class="flex items-center gap-2 text-xs text-secondary-foreground bg-secondary/5 p-2 rounded">
                            <i class="ki-filled ki-information-2 text-primary"></i>
                            <span>Swipe left to view more columns</span>
                        </div>
                    </div>

                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Reference</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Items</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Current Step</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Submitted</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitions as $requisition)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $requisition->reference_number ?? 'DRAFT-' . $requisition->id }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $requisition->command->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $requisition->items->count() }} items
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $requisition->status === 'ISSUED' ? 'success' : ($requisition->status === 'REJECTED' ? 'danger' : ($requisition->status === 'DRAFT' ? 'warning' : ($requisition->status === 'DISPENSED' ? 'primary' : 'info'))) }} kt-badge-sm">
                                                {{ $requisition->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($requisition->current_step_order)
                                                @php
                                                    $currentStep = $requisition->steps->where('step_order', $requisition->current_step_order)->first();
                                                @endphp
                                                <span class="text-sm text-secondary-foreground">{{ $currentStep->role_name ?? 'N/A' }}</span>
                                            @else
                                                <span class="text-sm text-secondary-foreground">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $requisition->submitted_at ? $requisition->submitted_at->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-5 pb-5">
                        {{ $requisitions->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No requisitions found.</p>
                        @if(auth()->user()->hasRole('Command Pharmacist'))
                            <a href="{{ route('pharmacy.requisitions.create') }}" class="kt-btn kt-btn-primary mt-4">
                                Create Your First Requisition
                            </a>
                        @endif
                    </div>
                @endif
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

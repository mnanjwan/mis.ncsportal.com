@extends('layouts.app')

@section('page-title', 'Stock Returns')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('pharmacy.returns.index') }}">Pharmacy</a>
    <span class="text-muted-foreground">/</span>
    <span class="text-muted-foreground">Stock Returns</span>
@endsection

@section('content')
    <div class="kt-container-fixed">
        @if(session('success'))
            <div class="kt-card mb-5 border-l-4 border-l-success">
                <div class="kt-card-content p-4 flex items-center gap-3">
                    <i class="ki-filled ki-check-circle text-success text-xl"></i>
                    <p class="text-sm font-medium text-foreground">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="kt-card mb-5 border-l-4 border-l-danger">
                <div class="kt-card-content p-4 flex items-center gap-3">
                    <i class="ki-filled ki-information-2 text-danger text-xl"></i>
                    <p class="text-sm font-medium text-foreground">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="kt-card overflow-hidden">
            <div class="kt-card-header flex items-center justify-between gap-3 py-4">
                <h3 class="kt-card-title">Stock Returns</h3>
                <div class="kt-card-toolbar">
                    @if(auth()->user()->hasRole('Command Pharmacist'))
                        <a href="{{ route('pharmacy.returns.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i>
                            New Return
                        </a>
                    @endif
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($returns->count() > 0)
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
                                @foreach($returns as $return)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $return->reference_number ?? 'DRAFT-' . $return->id }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $return->command->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $return->items->count() }} items
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $return->status === 'RECEIVED' ? 'success' : ($return->status === 'REJECTED' ? 'danger' : ($return->status === 'DRAFT' ? 'warning' : 'info')) }} kt-badge-sm">
                                                {{ $return->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @php
                                                $currentStep = $return->getCurrentStep();
                                            @endphp
                                            @if($currentStep)
                                                <span class="text-sm text-secondary-foreground">{{ $currentStep->role_name }}</span>
                                            @else
                                                <span class="text-sm text-secondary-foreground">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $return->submitted_at ? $return->submitted_at->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('pharmacy.returns.show', $return->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-5 pb-5">
                        {{ $returns->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No returns found.</p>
                        @if(auth()->user()->hasRole('Command Pharmacist'))
                            <a href="{{ route('pharmacy.returns.create') }}" class="kt-btn kt-btn-primary mt-4">
                                Initiate Your First Return
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .table-scroll-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .table-scroll-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .dark .table-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #1e293b;
        }
        .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
@endsection

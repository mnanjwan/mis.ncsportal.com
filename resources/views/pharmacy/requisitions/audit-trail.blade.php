@extends('layouts.app')

@section('title', 'Dispensing Audit Trail')
@section('page-title', 'Dispensing Audit Trail')
@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="#">Pharmacy</a>
    <span>/</span>
    <span class="text-primary">Audit Trail</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Dispensing Audit Trail</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($logs->count() > 0)
                    <!-- Swipe hint for mobile -->
                    <div class="px-5 pb-5 lg:hidden">
                        <div class="flex items-center gap-2 text-xs text-secondary-foreground bg-secondary/5 p-2 rounded">
                            <i class="ki-filled ki-information-2 text-primary"></i>
                            <span>Swipe left to view more columns</span>
                        </div>
                    </div>

                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 1000px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border bg-muted/30">
                                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Reference</th>
                                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Command</th>
                                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Requested By</th>
                                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Approved By</th>
                                    <th class="text-left py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Dispensed Date</th>
                                    <th class="text-right py-4 px-5 font-bold text-xs uppercase tracking-wider text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($logs as $requisition)
                                    <tr class="hover:bg-primary/5 transition-all duration-200">
                                        <td class="py-4 px-5">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-primary">{{ $requisition->reference_number }}</span>
                                                <span class="text-[10px] text-muted-foreground font-mono">Pharmacy REQ</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            <span class="text-xs font-semibold px-2 py-1 bg-secondary/10 rounded-full border border-secondary/20">
                                                {{ $requisition->command->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-5">
                                            @if($requisition->createdBy && $requisition->createdBy->officer)
                                                <div class="flex items-center gap-3">
                                                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                                        {{ substr($requisition->createdBy->officer->surname, 0, 1) }}
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-semibold text-foreground">
                                                            {{ $requisition->createdBy->officer->display_rank }} {{ $requisition->createdBy->officer->surname }}
                                                        </span>
                                                        <span class="text-[11px] text-secondary-foreground/70">
                                                            {{ $requisition->createdBy->officer->service_number }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-xs text-secondary-foreground">{{ $requisition->createdBy->email ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-5">
                                            @php
                                                $approvalStep = $requisition->steps->where('action', 'APPROVE')->first();
                                                $approver = $approvalStep ? $approvalStep->actedBy : null;
                                                $approverOfficer = $approver ? $approver->officer : null;
                                            @endphp
                                            @if($approverOfficer)
                                                <div class="flex items-center gap-3">
                                                    <div class="size-8 rounded-full bg-success/10 flex items-center justify-center text-success font-bold text-xs">
                                                        {{ substr($approverOfficer->surname, 0, 1) }}
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-semibold text-foreground">
                                                            {{ $approverOfficer->display_rank }} {{ $approverOfficer->surname }}
                                                        </span>
                                                        <span class="text-[11px] text-secondary-foreground/70">
                                                            {{ $approverOfficer->service_number }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif($approver)
                                                <span class="text-xs text-secondary-foreground">{{ $approver->email }}</span>
                                            @else
                                                <div class="flex items-center gap-2 text-muted-foreground/60">
                                                    <i class="ki-filled ki-lock text-xs"></i>
                                                    <span class="italic text-[11px] uppercase tracking-tight">No Approval Log</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-5 text-sm text-secondary-foreground">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $requisition->dispensed_at ? $requisition->dispensed_at->format('d M Y') : '-' }}</span>
                                                <span class="text-[11px] font-mono opacity-60">{{ $requisition->dispensed_at ? $requisition->dispensed_at->format('H:i:s') : '' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 text-right">
                                            <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-primary hover:scale-105 transition-transform">
                                                <i class="ki-filled ki-eye"></i> View Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-5 pb-5">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No dispensing audit logs found.</p>
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

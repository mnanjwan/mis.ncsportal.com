@extends('layouts.app')

@section('title', 'Requisition Details')
@section('page-title', 'Requisition Details')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.requisitions.index') }}" class="text-secondary-foreground hover:text-primary">Requisitions</a>
    <span>/</span>
    <span class="text-secondary-foreground">{{ $requisition->reference_number ?? 'Draft' }}</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        @if(session('success'))
            <div class="kt-alert kt-alert-success">
                <i class="ki-filled ki-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="kt-alert kt-alert-danger">
                <i class="ki-filled ki-information"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="kt-card">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-mono">
                            {{ $requisition->reference_number ?? 'DRAFT' }}
                        </h2>
                        <p class="text-sm text-secondary-foreground mt-1">
                            {{ $requisition->command->name ?? 'N/A' }} | Created by {{ $requisition->createdBy->email ?? 'N/A' }} on {{ $requisition->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="kt-badge kt-badge-{{ $requisition->status === 'ISSUED' ? 'success' : ($requisition->status === 'REJECTED' ? 'danger' : ($requisition->status === 'DRAFT' ? 'warning' : ($requisition->status === 'DISPENSED' ? 'primary' : 'info'))) }} kt-badge-lg">
                            {{ $requisition->status }}
                        </span>

                        @if($requisition->isDraft() && auth()->id() === $requisition->created_by)
                            <a href="{{ route('pharmacy.requisitions.edit', $requisition->id) }}" class="kt-btn kt-btn-light">
                                <i class="ki-filled ki-notepad-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('pharmacy.requisitions.submit', $requisition->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-send"></i> Submit for Approval
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Main Content -->
            <div class="xl:col-span-2 grid gap-5 lg:gap-7.5">
                <!-- Items -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Requisition Items</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="kt-table-responsive">
                            <table class="kt-table kt-table-rounded">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Drug</th>
                                        <th>Unit</th>
                                        <th>Requested</th>
                                        @if($requisition->status === 'ISSUED' || $requisition->status === 'DISPENSED' || $requisition->status === 'APPROVED')
                                            <th>Issued</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisition->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="font-medium">{{ $item->drug->name ?? 'Unknown' }}</td>
                                            <td>{{ $item->drug->unit_of_measure ?? 'units' }}</td>
                                            <td>{{ number_format($item->quantity_requested) }}</td>
                                            @if($requisition->status === 'ISSUED' || $requisition->status === 'DISPENSED' || $requisition->status === 'APPROVED')
                                                <td>{{ number_format($item->quantity_issued) }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- OC Pharmacy Approval Form -->
                @if(auth()->user()->hasRole('OC Pharmacy') && $requisition->status === 'SUBMITTED' && $requisition->current_step_order === 1)
                    <div class="kt-card border-warning">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">OC Pharmacy Approval</h3>
                        </div>
                        <div class="kt-card-content">
                            <form method="POST" action="{{ route('pharmacy.requisitions.act', $requisition->id) }}">
                                @csrf
                                <div class="grid gap-4">
                                    <div>
                                        <label class="kt-label">Comment (Optional)</label>
                                        <textarea name="comment" class="kt-input" rows="3" placeholder="Add your comment..."></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="submit" name="decision" value="APPROVED" class="kt-btn kt-btn-success">
                                            <i class="ki-filled ki-check"></i> Approve
                                        </button>
                                        <button type="submit" name="decision" value="REJECTED" class="kt-btn kt-btn-danger">
                                            <i class="ki-filled ki-cross"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Central Medical Store Issue Form -->
                @if(auth()->user()->hasRole('Central Medical Store') && $requisition->status === 'APPROVED' && $requisition->current_step_order === 2)
                    <div class="kt-card border-info">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Issue Items to Command</h3>
                        </div>
                        <div class="kt-card-content">
                            <form method="POST" action="{{ route('pharmacy.requisitions.issue', $requisition->id) }}">
                                @csrf
                                <div class="grid gap-4">
                                    @foreach($requisition->items as $item)
                                        @php
                                            $availableStock = isset($centralStock[$item->pharmacy_drug_id]) ? $centralStock[$item->pharmacy_drug_id]->sum('quantity') : 0;
                                        @endphp
                                        <div class="p-3 bg-muted/50 rounded-lg border border-input">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium">{{ $item->drug->name ?? 'Unknown' }}</span>
                                                <span class="text-xs text-secondary-foreground">Available: {{ number_format($availableStock) }}</span>
                                            </div>
                                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                            <div class="flex gap-3 items-center">
                                                <div class="flex-grow">
                                                    <label class="kt-label text-xs">Quantity to Issue (Requested: {{ $item->quantity_requested }})</label>
                                                    <input type="number" name="items[{{ $loop->index }}][quantity_issued]" 
                                                           class="kt-input kt-input-sm" 
                                                           value="{{ min($item->quantity_requested, $availableStock) }}" 
                                                           min="0" max="{{ min($item->quantity_requested, $availableStock) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div>
                                        <label class="kt-label">Comment (Optional)</label>
                                        <textarea name="comment" class="kt-input" rows="2" placeholder="Add any notes..."></textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="kt-btn kt-btn-info">
                                            <i class="ki-filled ki-exit-right"></i> Issue Items to Command
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Command Pharmacist Dispense Form -->
                @if(auth()->user()->hasRole('Command Pharmacist') && $requisition->status === 'ISSUED')
                    <div class="kt-card border-success">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Dispense Items</h3>
                        </div>
                        <div class="kt-card-content">
                            <form method="POST" action="{{ route('pharmacy.requisitions.dispense', $requisition->id) }}">
                                @csrf
                                <div class="grid gap-4">
                                    @foreach($requisition->items as $item)
                                        @if($item->quantity_issued > 0)
                                            <div class="p-3 bg-muted/50 rounded-lg border border-input">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-medium">{{ $item->drug->name ?? 'Unknown' }}</span>
                                                    <span class="text-xs text-secondary-foreground">Available: {{ number_format($item->quantity_issued) }}</span>
                                                </div>
                                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                                <div class="flex gap-3 items-center">
                                                    <div class="flex-grow">
                                                        <label class="kt-label text-xs">Quantity to Dispense</label>
                                                        <input type="number" name="items[{{ $loop->index }}][quantity_dispensed]" 
                                                               class="kt-input kt-input-sm" 
                                                               value="{{ $item->quantity_issued }}" 
                                                               min="0" max="{{ $item->quantity_issued }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <div>
                                        <label class="kt-label">Dispensing Notes (Optional)</label>
                                        <textarea name="comment" class="kt-input" rows="2" placeholder="Patient info, purpose, etc..."></textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="kt-btn kt-btn-success">
                                            <i class="ki-filled ki-pill"></i> Mark as Dispensed
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if($requisition->notes)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Notes</h3>
                        </div>
                        <div class="kt-card-content">
                            <p class="text-secondary-foreground">{{ $requisition->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar - Workflow -->
            <div class="grid gap-5 lg:gap-7.5">
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Workflow Timeline</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex flex-col gap-4">
                            @foreach($requisition->steps as $step)
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center justify-center size-8 rounded-full {{ $step->isCompleted() ? ($step->isRejected() ? 'bg-danger text-white' : 'bg-success text-white') : ($requisition->current_step_order === $step->step_order ? 'bg-warning text-white' : 'bg-muted text-muted-foreground') }}">
                                            @if($step->isCompleted())
                                                @if($step->isRejected())
                                                    <i class="ki-filled ki-cross text-sm"></i>
                                                @else
                                                    <i class="ki-filled ki-check text-sm"></i>
                                                @endif
                                            @else
                                                {{ $step->step_order }}
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-8 {{ $step->isCompleted() ? 'bg-success' : 'bg-muted' }}"></div>
                                        @endif
                                    </div>
                                    <div class="flex-grow">
                                        <div class="font-medium text-sm">{{ $step->role_name }}</div>
                                        <div class="text-xs text-secondary-foreground">{{ $step->getActionLabel() }}</div>
                                        @if($step->isCompleted())
                                            <div class="text-xs text-secondary-foreground mt-1">
                                                {{ $step->getDecisionLabel() }} by {{ $step->actedBy->officer->full_name ?? $step->actedBy->email ?? 'N/A' }}
                                                <br>{{ $step->acted_at->format('d M Y H:i') }}
                                            </div>
                                            @if($step->comment)
                                                <div class="text-xs mt-1 p-2 bg-muted rounded">{{ $step->comment }}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Details</h3>
                    </div>
                    <div class="kt-card-content">
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Status</span>
                                <span class="text-sm font-medium">{{ $requisition->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Command</span>
                                <span class="text-sm font-medium">{{ $requisition->command->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Total Items</span>
                                <span class="text-sm font-medium">{{ $requisition->items->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Created</span>
                                <span class="text-sm font-medium">{{ $requisition->created_at->format('d M Y') }}</span>
                            </div>
                            @if($requisition->submitted_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Submitted</span>
                                    <span class="text-sm font-medium">{{ $requisition->submitted_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($requisition->approved_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Approved</span>
                                    <span class="text-sm font-medium">{{ $requisition->approved_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($requisition->issued_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Issued</span>
                                    <span class="text-sm font-medium">{{ $requisition->issued_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($requisition->dispensed_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Dispensed</span>
                                    <span class="text-sm font-medium">{{ $requisition->dispensed_at->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

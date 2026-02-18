@extends('layouts.app')

@section('title', 'Procurement Details')
@section('page-title', 'Procurement Details')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.procurements.index') }}" class="text-secondary-foreground hover:text-primary">Procurements</a>
    <span>/</span>
    <span class="text-secondary-foreground">{{ $procurement->reference_number ?? 'Draft' }}</span>
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
                            {{ $procurement->reference_number ?? 'DRAFT' }}
                        </h2>
                        <p class="text-sm text-secondary-foreground mt-1">
                            Created by {{ $procurement->createdBy->email ?? 'N/A' }} on {{ $procurement->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="kt-badge kt-badge-{{ $procurement->status === 'APPROVED' ? 'success' : ($procurement->status === 'REJECTED' ? 'danger' : ($procurement->status === 'DRAFT' ? 'warning' : ($procurement->status === 'RECEIVED' ? 'primary' : 'info'))) }} kt-badge-lg">
                            {{ $procurement->status }}
                        </span>

                        @if($procurement->isDraft() && auth()->id() === $procurement->created_by)
                            <a href="{{ route('pharmacy.procurements.edit', $procurement->id) }}" class="kt-btn kt-btn-light">
                                <i class="ki-filled ki-notepad-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('pharmacy.procurements.submit', $procurement->id) }}" class="inline">
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
                        <h3 class="kt-card-title">Procurement Items</h3>
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
                                        @if($procurement->status === 'RECEIVED' || $procurement->status === 'APPROVED')
                                            <th>Received</th>
                                            <th>Batch</th>
                                            <th>Expiry</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($procurement->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="font-medium">{{ $item->getDisplayName() }}</td>
                                            <td>{{ $item->getDisplayUnit() }}</td>
                                            <td>{{ number_format($item->quantity_requested) }}</td>
                                            @if($procurement->status === 'RECEIVED' || $procurement->status === 'APPROVED')
                                                <td>{{ number_format($item->quantity_received) }}</td>
                                                <td>{{ $item->batch_number ?? '-' }}</td>
                                                <td>{{ $item->expiry_date ? $item->expiry_date->format('d M Y') : '-' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- OC Pharmacy Approval Form -->
                @if(auth()->user()->hasRole('OC Pharmacy') && $procurement->status === 'SUBMITTED' && $procurement->current_step_order === 1)
                    <div class="kt-card border-warning">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Controller Pharmacy Approval</h3>
                        </div>
                        <div class="kt-card-content">
                            <form method="POST" action="{{ route('pharmacy.procurements.act', $procurement->id) }}">
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

                <!-- Central Medical Store Receipt Form -->
                @if(auth()->user()->hasRole('Central Medical Store') && $procurement->status === 'APPROVED' && $procurement->current_step_order === 2)
                    <div class="kt-card border-success">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Receive Procurement Items</h3>
                        </div>
                        <div class="kt-card-content">
                            <form method="POST" action="{{ route('pharmacy.procurements.receive', $procurement->id) }}">
                                @csrf
                                <div class="grid gap-4">
                                    @foreach($procurement->items as $item)
                                        <div class="p-3 bg-muted/50 rounded-lg border border-input">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium">{{ $item->getDisplayName() }}</span>
                                                <span class="text-sm text-secondary-foreground">Requested: {{ $item->quantity_requested }} {{ $item->getDisplayUnit() }}</span>
                                            </div>
                                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="kt-label text-xs">Quantity Received</label>
                                                    <input type="number" name="items[{{ $loop->index }}][quantity_received]" 
                                                           class="kt-input kt-input-sm" 
                                                           value="{{ $item->quantity_requested }}" 
                                                           min="0" max="{{ $item->quantity_requested }}" required>
                                                </div>
                                                <div>
                                                    <label class="kt-label text-xs">Batch Number</label>
                                                    <input type="text" name="items[{{ $loop->index }}][batch_number]" 
                                                           class="kt-input kt-input-sm" placeholder="e.g., BTH-001">
                                                </div>
                                                <div>
                                                    <label class="kt-label text-xs">Expiry Date</label>
                                                    <input type="date" name="items[{{ $loop->index }}][expiry_date]" 
                                                           class="kt-input kt-input-sm" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div>
                                        <label class="kt-label">Comment (Optional)</label>
                                        <textarea name="comment" class="kt-input" rows="2" placeholder="Add any notes..."></textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="kt-btn kt-btn-success">
                                            <i class="ki-filled ki-check"></i> Confirm Receipt & Update Stock
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if($procurement->notes)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Notes</h3>
                        </div>
                        <div class="kt-card-content">
                            <p class="text-secondary-foreground">{{ $procurement->notes }}</p>
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
                            @foreach($procurement->steps as $step)
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center justify-center size-8 rounded-full {{ $step->isCompleted() ? ($step->isRejected() ? 'bg-danger text-white' : 'bg-success text-white') : ($procurement->current_step_order === $step->step_order ? 'bg-warning text-white' : 'bg-muted text-muted-foreground') }}">
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
                                <span class="text-sm font-medium">{{ $procurement->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Total Items</span>
                                <span class="text-sm font-medium">{{ $procurement->items->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-secondary-foreground">Created</span>
                                <span class="text-sm font-medium">{{ $procurement->created_at->format('d M Y') }}</span>
                            </div>
                            @if($procurement->submitted_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Submitted</span>
                                    <span class="text-sm font-medium">{{ $procurement->submitted_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($procurement->approved_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Approved</span>
                                    <span class="text-sm font-medium">{{ $procurement->approved_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($procurement->received_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-secondary-foreground">Received</span>
                                    <span class="text-sm font-medium">{{ $procurement->received_at->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

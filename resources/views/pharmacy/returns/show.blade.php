@extends('layouts.app')

@section('page-title', 'Return Details - ' . ($return->reference_number ?? 'Draft'))

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('pharmacy.returns.index') }}">Pharmacy</a>
    <span class="text-muted-foreground">/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('pharmacy.returns.index') }}">Stock Returns</a>
    <span class="text-muted-foreground">/</span>
    <span class="text-muted-foreground">{{ $return->reference_number ?? 'Return Detail' }}</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Return Items Card -->
                <div class="kt-card overflow-hidden">
                    <div class="kt-card-header py-4 flex items-center justify-between">
                        <h3 class="kt-card-title">Return Summary</h3>
                        <span class="kt-badge kt-badge-{{ $return->status === 'RECEIVED' ? 'success' : ($return->status === 'REJECTED' ? 'danger' : ($return->status === 'DRAFT' ? 'warning' : 'info')) }} kt-badge-sm">
                            {{ $return->status }}
                        </span>
                    </div>
                    <div class="kt-card-content p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-muted/50">
                                    <tr class="border-b border-border">
                                        <th class="py-3 px-5 text-xs font-bold text-secondary-foreground uppercase tracking-wider">Drug / Item</th>
                                        <th class="py-3 px-5 text-xs font-bold text-secondary-foreground uppercase tracking-wider">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->items as $item)
                                        <tr class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                                            <td class="py-4 px-5">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-foreground">{{ $item->drug->name }}</span>
                                                    <span class="text-[10px] text-secondary-foreground font-medium uppercase tracking-tighter">{{ $item->drug->sku_code ?? 'NO-SKU' }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-5">
                                                <span class="text-sm font-bold text-foreground">{{ $item->quantity }}</span>
                                                <span class="text-xs text-secondary-foreground">{{ $item->drug->unit_of_measure }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($return->notes)
                            <div class="p-5 border-t border-border bg-muted/10 mt-2">
                                <h4 class="text-xs font-bold text-secondary-foreground uppercase tracking-wider mb-2">Notes / Reason</h4>
                                <p class="text-sm text-foreground leading-relaxed">{{ $return->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Workflow History Card -->
                <div class="kt-card overflow-hidden">
                    <div class="kt-card-header py-4">
                        <h3 class="kt-card-title">Workflow History</h3>
                    </div>
                    <div class="kt-card-content p-5">
                        <div class="relative space-y-6 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-border">
                            <!-- Initiation -->
                            <div class="relative pl-8">
                                <span class="absolute left-0 top-1 w-6 h-6 rounded-full bg-primary flex items-center justify-center ring-4 ring-background z-10">
                                    <i class="ki-filled ki-plus text-[10px] text-primary-foreground"></i>
                                </span>
                                <div class="flex flex-col">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-bold text-foreground">Return Initiated</span>
                                        <span class="text-[10px] text-secondary-foreground bg-muted px-2 py-0.5 rounded font-medium">{{ $return->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <span class="text-xs text-secondary-foreground font-medium uppercase tracking-tight">{{ $return->createdBy->officer->full_name ?? $return->createdBy->email }} (Command Pharmacist)</span>
                                </div>
                            </div>

                            @foreach($return->steps as $step)
                                <div class="relative pl-8">
                                    <span class="absolute left-0 top-1 w-6 h-6 rounded-full {{ $step->acted_at ? ($step->decision === 'REJECTED' ? 'bg-danger' : 'bg-success') : 'bg-muted' }} flex items-center justify-center ring-4 ring-background z-10">
                                        <i class="ki-filled ki-{{ $step->acted_at ? ($step->decision === 'REJECTED' ? 'cross' : 'check') : 'time' }} text-[10px] text-white"></i>
                                    </span>
                                    <div class="flex flex-col">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-bold text-foreground">{{ $step->getActionLabel() }} ({{ $step->role_name }})</span>
                                            @if($step->acted_at)
                                                <span class="text-[10px] text-secondary-foreground bg-muted px-2 py-0.5 rounded font-medium">{{ $step->acted_at->format('d M Y, H:i') }}</span>
                                            @else
                                                <span class="text-[10px] text-warning bg-warning/10 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Pending</span>
                                            @endif
                                        </div>
                                        @if($step->actedBy)
                                            <span class="text-xs text-secondary-foreground font-medium uppercase tracking-tight">{{ $step->actedBy->officer->full_name ?? $step->actedBy->email }}</span>
                                        @endif
                                        
                                        @if($step->decision)
                                            <div class="mt-2 p-3 rounded-lg {{ $step->decision === 'REJECTED' ? 'bg-danger/5 border border-danger/10 text-danger' : 'bg-success/5 border border-success/10 text-success font-semibold' }} text-xs">
                                                <strong>Decision:</strong> {{ $step->getDecisionLabel() }}
                                                @if($step->comment)
                                                    <p class="mt-1 font-normal opacity-80">{{ $step->comment }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($return->status === 'RECEIVED')
                                <div class="relative pl-8">
                                    <span class="absolute left-0 top-1 w-6 h-6 rounded-full bg-success flex items-center justify-center ring-4 ring-background z-10">
                                        <i class="ki-filled ki-package text-[10px] text-success-foreground"></i>
                                    </span>
                                    <div class="flex flex-col">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-bold text-foreground">Items Received</span>
                                            <span class="text-[10px] text-secondary-foreground bg-muted px-2 py-0.5 rounded font-medium">{{ $return->received_at->format('d M Y, H:i') }}</span>
                                        </div>
                                        <span class="text-xs text-secondary-foreground font-medium uppercase">Central Medical Store</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Metadata -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="kt-card overflow-hidden border-t-4 border-t-{{ $return->status === 'RECEIVED' ? 'success' : ($return->status === 'REJECTED' ? 'danger' : ($return->status === 'DRAFT' ? 'warning' : 'info')) }}">
                    <div class="kt-card-content p-5">
                        <div class="flex flex-col gap-4">
                            <div>
                                <h4 class="text-xs font-bold text-secondary-foreground uppercase tracking-widest mb-1">Status</h4>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-{{ $return->status === 'RECEIVED' ? 'success' : ($return->status === 'REJECTED' ? 'danger' : ($return->status === 'DRAFT' ? 'warning' : 'info')) }}"></div>
                                    <span class="text-lg font-black text-foreground">{{ $return->status }}</span>
                                </div>
                            </div>

                            @if($return->status === 'DRAFT' && auth()->id() === $return->created_by)
                                <form action="{{ route('pharmacy.returns.submit', $return->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="kt-btn kt-btn-primary w-full group">
                                        <i class="ki-filled ki-send group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                                        Submit Return
                                    </button>
                                </form>
                                <a href="{{ route('pharmacy.returns.edit', $return->id) }}" class="kt-btn kt-btn-ghost w-full">
                                    <i class="ki-filled ki-notepad-edit"></i> Edit Draft
                                </a>
                            @endif

                            @php
                                $currentStep = $return->getCurrentStep();
                            @endphp

                            @if($currentStep && auth()->user()->hasRole($currentStep->role_name))
                                <div class="pt-4 border-t border-border">
                                    <h4 class="text-sm font-bold text-foreground mb-4 italic underline decoration-primary/30">Action Required</h4>
                                    
                                    @if($currentStep->action === 'APPROVE')
                                        <form action="{{ route('pharmacy.returns.act', $return->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-secondary-foreground mb-2">Comment</label>
                                                <textarea name="comment" rows="2" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Optional comments..."></textarea>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="submit" name="decision" value="APPROVED" class="kt-btn kt-btn-success kt-btn-sm font-bold">Approve</button>
                                                <button type="submit" name="decision" value="REJECTED" class="kt-btn kt-btn-danger kt-btn-sm font-bold">Reject</button>
                                            </div>
                                        </form>
                                    @elseif($currentStep->action === 'REVIEW')
                                        <form action="{{ route('pharmacy.returns.receive', $return->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-secondary-foreground mb-2">Comment</label>
                                                <textarea name="comment" rows="2" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Optional comments..."></textarea>
                                            </div>
                                            <button type="submit" class="kt-btn kt-btn-success w-full font-bold">Confirm Receipt</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="kt-card bg-muted/20">
                    <div class="kt-card-content p-5 space-y-4">
                        <div>
                            <span class="text-[10px] font-bold text-secondary-foreground uppercase tracking-wider block mb-1">Initiated By</span>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                    {{ substr($return->createdBy->officer->surname ?? $return->createdBy->email, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-foreground">{{ $return->createdBy->officer->full_name ?? $return->createdBy->email }}</span>
                                    <span class="text-[10px] text-secondary-foreground font-medium uppercase tracking-tighter">{{ $return->command->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-border/50">
                            <span class="text-[10px] font-bold text-secondary-foreground uppercase tracking-wider block mb-1">Reference Number</span>
                            <span class="text-sm font-black text-foreground tracking-widest">{{ $return->reference_number ?? 'DRAFT' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

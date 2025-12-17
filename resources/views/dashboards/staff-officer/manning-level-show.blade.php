@extends('layouts.app')

@section('title', 'Manning Request Details')
@section('page-title', 'Manning Request Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.manning-level') }}">Manning Level</a>
    <span>/</span>
    <span class="text-primary">View Details</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold text-foreground">Manning Request Details</h1>
            <p class="text-sm text-secondary-foreground mt-1">
                Request #{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}
            </p>
        </div>
        <div class="flex-shrink-0 flex gap-2">
            @if($request->status === 'DRAFT')
                <a href="{{ route('staff-officer.manning-level.edit', $request->id) }}" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
            @endif
            <a href="{{ route('staff-officer.manning-level') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Request Header -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Request Information</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex items-center gap-4 mb-4 flex-wrap">
                        @php
                            $statusColors = [
                                'DRAFT' => 'secondary',
                                'SUBMITTED' => 'warning',
                                'APPROVED' => 'success',
                                'REJECTED' => 'danger',
                                'FULFILLED' => 'info',
                            ];
                            $statusColor = $statusColors[$request->status] ?? 'secondary';
                        @endphp
                        <span class="kt-badge kt-badge-{{ $statusColor }} kt-badge-lg">
                            {{ $request->status }}
                        </span>
                        @if($request->submitted_at)
                            <span class="text-sm text-secondary-foreground">
                                Submitted: {{ $request->submitted_at->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Command</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->command->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Requested By</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->requestedBy->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Created Date</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->created_at->format('d M Y') }}</p>
                        </div>
                        @if($request->submitted_at)
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Submitted Date</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->submitted_at->format('d M Y') }}</p>
                        </div>
                        @endif
                        @if($request->approved_by)
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Approved By</label>
                            <p class="text-sm text-foreground mt-1">
                                {{ $request->approvedBy->initials ?? '' }} {{ $request->approvedBy->surname ?? 'N/A' }}
                            </p>
                        </div>
                        @endif
                        @if($request->approved_at)
                        <div>
                            <label class="text-sm font-medium text-secondary-foreground">Approved Date</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->approved_at->format('d M Y') }}</p>
                        </div>
                        @endif
                    </div>
                    @if($request->notes)
                        <div class="pt-4 border-t border-border mt-4">
                            <label class="text-sm font-medium text-secondary-foreground">Notes</label>
                            <p class="text-sm text-foreground mt-1">{{ $request->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

    <!-- Request Items -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Manning Requirements</h3>
        </div>
        <div class="kt-card-content">
            @if($request->items && $request->items->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Quantity</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Sex Requirement</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Qualification</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($request->items as $item)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $item->rank }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $item->quantity_needed }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $item->sex_requirement === 'ANY' ? 'Any' : ($item->sex_requirement === 'M' ? 'Male' : 'Female') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $item->qualification_requirement ?? 'Any' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($item->matched_officer_id)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Matched</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @foreach($request->items as $item)
                            <div class="kt-card shadow-none bg-muted/30 border border-input">
                                <div class="kt-card-content p-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-foreground">{{ $item->rank }}</span>
                                            <span class="kt-badge kt-badge-{{ $item->matched_officer_id ? 'success' : 'warning' }} kt-badge-sm">
                                                {{ $item->matched_officer_id ? 'Matched' : 'Pending' }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-secondary-foreground space-y-1">
                                            <div>Quantity: {{ $item->quantity_needed }}</div>
                                            <div>Sex: {{ $item->sex_requirement === 'ANY' ? 'Any' : ($item->sex_requirement === 'M' ? 'Male' : 'Female') }}</div>
                                            @if($item->qualification_requirement)
                                            <div>Qualification: {{ $item->qualification_requirement }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-people text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No requirements specified</p>
                </div>
            @endif
        </div>
    </div>

        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            @if($request->status === 'DRAFT')
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Actions</h3>
                </div>
                <div class="kt-card-content">
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('staff-officer.manning-level.edit', $request->id) }}" class="kt-btn kt-btn-primary w-full">
                            <i class="ki-filled ki-pencil"></i> Edit Request
                        </a>
                        <form action="{{ route('staff-officer.manning-level.submit', $request->id) }}" method="POST" class="inline w-full">
                            @csrf
                            <button type="submit" class="kt-btn kt-btn-success w-full" onclick="return confirm('Submit this request to Area Controller for approval?')">
                                <i class="ki-filled ki-check"></i> Submit for Approval
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection


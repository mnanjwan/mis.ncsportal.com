@extends('layouts.app')

@section('title', 'Requisitions')
@section('page-title', 'Pharmacy Requisitions')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Requisitions</span>
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

        <div class="kt-card">
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
            <div class="kt-card-content">
                @if($requisitions->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Command</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Current Step</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitions as $requisition)
                                    <tr>
                                        <td>
                                            <span class="font-medium">{{ $requisition->reference_number ?? 'DRAFT-' . $requisition->id }}</span>
                                        </td>
                                        <td>{{ $requisition->command->name ?? 'N/A' }}</td>
                                        <td>{{ $requisition->items->count() }} items</td>
                                        <td>
                                            <span class="kt-badge kt-badge-{{ $requisition->status === 'ISSUED' ? 'success' : ($requisition->status === 'REJECTED' ? 'danger' : ($requisition->status === 'DRAFT' ? 'warning' : ($requisition->status === 'DISPENSED' ? 'primary' : 'info'))) }} kt-badge-sm">
                                                {{ $requisition->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($requisition->current_step_order)
                                                @php
                                                    $currentStep = $requisition->steps->where('step_order', $requisition->current_step_order)->first();
                                                @endphp
                                                <span class="text-sm">{{ $currentStep->role_name ?? 'N/A' }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $requisition->submitted_at ? $requisition->submitted_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                                <i class="ki-filled ki-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
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
@endsection

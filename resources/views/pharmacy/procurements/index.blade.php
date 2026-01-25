@extends('layouts.app')

@section('title', 'Procurements')
@section('page-title', 'Pharmacy Procurements')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <span class="text-secondary-foreground">Procurements</span>
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
                <h3 class="kt-card-title">Procurements</h3>
                <div class="kt-card-toolbar flex items-center gap-2">
                    <form method="GET" class="flex items-center gap-2">
                        <select name="status" class="kt-input kt-input-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="SUBMITTED" {{ $status === 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                            <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                            <option value="RECEIVED" {{ $status === 'RECEIVED' ? 'selected' : '' }}>Received</option>
                        </select>
                    </form>
                    @if(auth()->user()->hasRole('Controller Procurement'))
                        <a href="{{ route('pharmacy.procurements.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i>
                            New Procurement
                        </a>
                    @endif
                </div>
            </div>
            <div class="kt-card-content">
                @if($procurements->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Items</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Current Step</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procurements as $procurement)
                                    <tr>
                                        <td>
                                            <span class="font-medium">{{ $procurement->reference_number ?? 'DRAFT-' . $procurement->id }}</span>
                                        </td>
                                        <td>{{ $procurement->items->count() }} items</td>
                                        <td>{{ $procurement->createdBy->email ?? 'N/A' }}</td>
                                        <td>
                                            <span class="kt-badge kt-badge-{{ $procurement->status === 'APPROVED' ? 'success' : ($procurement->status === 'REJECTED' ? 'danger' : ($procurement->status === 'DRAFT' ? 'warning' : ($procurement->status === 'RECEIVED' ? 'primary' : 'info'))) }} kt-badge-sm">
                                                {{ $procurement->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($procurement->current_step_order)
                                                @php
                                                    $currentStep = $procurement->steps->where('step_order', $procurement->current_step_order)->first();
                                                @endphp
                                                <span class="text-sm">{{ $currentStep->role_name ?? 'N/A' }}</span>
                                            @else
                                                -
                                            @endif
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

                    <div class="mt-4">
                        {{ $procurements->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-document text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No procurements found.</p>
                        @if(auth()->user()->hasRole('Controller Procurement'))
                            <a href="{{ route('pharmacy.procurements.create') }}" class="kt-btn kt-btn-primary mt-4">
                                Create Your First Procurement
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

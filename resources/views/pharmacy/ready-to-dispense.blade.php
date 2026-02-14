@extends('layouts.app')

@section('title', 'Ready to Dispense')
@section('page-title', 'Ready to Dispense')
@section('breadcrumbs')
    <span class="text-secondary-foreground">Pharmacy</span>
    <span>/</span>
    <a href="{{ route('pharmacy.command-pharmacist.dashboard') }}" class="text-secondary-foreground hover:text-primary">Dashboard</a>
    <span>/</span>
    <span class="text-secondary-foreground">Ready to Dispense</span>
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
                <h3 class="kt-card-title">Requisitions Ready to Dispense</h3>
                <p class="text-sm text-secondary-foreground">Issued requisitions for {{ $commandName ?? 'your command' }}. Click Dispense to open the requisition and dispense items to patients.</p>
            </div>
            <div class="kt-card-content">
                @if($requisitions->count() > 0)
                    <div class="kt-table-responsive">
                        <table class="kt-table kt-table-rounded">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Drugs in this requisition</th>
                                    <th>Items</th>
                                    <th>Issued</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitions as $requisition)
                                    <tr>
                                        <td>
                                            <span class="font-medium text-mono">{{ $requisition->reference_number }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm text-foreground">
                                                @php
                                                    $drugNames = $requisition->items
                                                        ->map(fn ($i) => $i->drug->name ?? 'Unknown')
                                                        ->filter()
                                                        ->unique()
                                                        ->values();
                                                @endphp
                                                {{ $drugNames->implode(', ') ?: '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $requisition->items->count() }} item(s)</td>
                                        <td>{{ $requisition->issued_at ? $requisition->issued_at->format('d M Y') : '—' }}</td>
                                        <td>
                                            <a href="{{ route('pharmacy.requisitions.show', $requisition->id) }}#dispense-form" class="kt-btn kt-btn-sm kt-btn-success">
                                                <i class="ki-filled ki-pill"></i> Dispense
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-pill text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No requisitions ready to dispense.</p>
                        <p class="text-sm text-muted-foreground mt-1">Issued requisitions will appear here. Go to Requisitions to view all.</p>
                        <a href="{{ route('pharmacy.command-pharmacist.dashboard') }}" class="kt-btn kt-btn-light mt-4">
                            <i class="ki-filled ki-home-3"></i> Back to Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

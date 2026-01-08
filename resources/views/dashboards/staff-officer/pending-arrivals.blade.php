@extends('layouts.app')

@section('title', 'Pending Arrivals')
@section('page-title', 'Pending Arrivals')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Pending Arrivals</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">Pending Arrivals</h2>
                <p class="text-sm text-secondary-foreground mt-1">
                    Officers posted to {{ $command->name ?? 'your command' }} awaiting acceptance
                </p>
            </div>
            <a href="{{ route('staff-officer.dashboard') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                <i class="ki-filled ki-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Pending Arrivals List -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Officers Awaiting Acceptance</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5">
                @if($pendingArrivals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Order</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Release Letter Printed</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingArrivals as $posting)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="text-sm font-medium">
                                                {{ $posting->officer->initials ?? '' }} {{ $posting->officer->surname ?? '' }}
                                            </div>
                                            <div class="text-xs text-secondary-foreground">
                                                {{ $posting->officer->service_number ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $posting->officer->substantive_rank ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $posting->officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            @if($posting->movementOrder)
                                                <span class="kt-badge kt-badge-sm">MO: {{ $posting->movementOrder->order_number }}</span>
                                            @elseif($posting->staffOrder)
                                                <span class="kt-badge kt-badge-sm">SO: {{ $posting->staffOrder->order_number }}</span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            @if($posting->release_letter_printed_at)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">
                                                    {{ $posting->release_letter_printed_at->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <form action="{{ route('staff-officer.postings.accept', $posting->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="kt-btn kt-btn-sm kt-btn-success"
                                                        onclick="return confirm('Accept {{ $posting->officer->initials }} {{ $posting->officer->surname }} into {{ $command->name }}? This will complete the transfer.')">
                                                    <i class="ki-filled ki-check"></i> Accept Officer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-check-circle text-4xl text-success mb-4"></i>
                        <p class="text-secondary-foreground">No pending arrivals. All officers have been accepted.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Print Pass Application')

@section('content')
<div class="max-w-4xl mx-auto p-8 bg-white">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold">NIGERIA CUSTOMS SERVICE</h1>
        <h2 class="text-xl font-semibold mt-2">Pass Application</h2>
    </div>
    
    @php
        $application = \App\Models\PassApplication::with('officer')->findOrFail($id);
    @endphp
    
    <div class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Service Number:</p>
                <p class="font-semibold">{{ $application->officer->service_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Name:</p>
                <p class="font-semibold">{{ $application->officer->initials ?? '' }} {{ $application->officer->surname ?? '' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Rank:</p>
                <p class="font-semibold">{{ $application->officer->substantive_rank ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Number of Days:</p>
                <p class="font-semibold">{{ $application->number_of_days }} days</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Start Date:</p>
                <p class="font-semibold">{{ $application->start_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">End Date:</p>
                <p class="font-semibold">{{ $application->end_date->format('d M Y') }}</p>
            </div>
            @if($application->approved_at)
            <div>
                <p class="text-sm text-gray-600">Approved Date:</p>
                <p class="font-semibold">{{ $application->approved_at->format('d M Y') }}</p>
            </div>
            @endif
        </div>
        
        @if($application->reason)
        <div>
            <p class="text-sm text-gray-600">Reason:</p>
            <p class="mt-1">{{ $application->reason }}</p>
        </div>
        @endif
    </div>
    
    <div class="mt-8 text-center">
        <button onclick="window.print()" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-printer"></i> Print
        </button>
    </div>
</div>

<style>
@media print {
    button { display: none; }
}
</style>
@endsection


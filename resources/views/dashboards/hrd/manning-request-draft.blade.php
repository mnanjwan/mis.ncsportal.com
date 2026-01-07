@extends('layouts.app')

@section('title', 'View Draft Items')
@section('page-title', 'View Draft Items for Manning Request')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests') }}">Manning Requests</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}">Request Details</a>
    <span>/</span>
    <span class="text-primary">View Draft</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('hrd.manning-requests.show', $manningRequest->id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
            <i class="ki-filled ki-arrow-left"></i> Back to Request Details
        </a>
        <a href="{{ route('hrd.manning-deployments.draft') }}" class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-file-add"></i> Go to Draft Deployment
        </a>
    </div>

    <!-- Request Header -->
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="mb-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-sm text-info font-medium">
                    <i class="ki-filled ki-information"></i> 
                    <strong>Preview:</strong> This is a preview of all officers from Manning Request #{{ $manningRequest->id }} that are currently in the draft deployment. Click "Go to Draft Deployment" to manage, swap, remove, or publish these officers.
                </p>
            </div>
            <h3 class="text-lg font-semibold mb-4">Manning Request Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-secondary-foreground">Request ID:</span>
                    <span class="font-semibold text-mono ml-2">#{{ $manningRequest->id }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Command:</span>
                    <span class="font-semibold text-mono ml-2">{{ $manningRequest->command->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Total Items in Draft:</span>
                    <span class="font-semibold text-mono ml-2">{{ $assignments->count() }} officer(s)</span>
                </div>
                <div>
                    <span class="text-secondary-foreground">Ranks in Draft:</span>
                    <span class="font-semibold text-mono ml-2">{{ $assignmentsByItem->count() }} rank(s)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Officers in Draft -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">
                Officers in Draft Deployment
                <span class="text-sm font-normal text-secondary-foreground">
                    ({{ $assignments->count() }} officer(s))
                </span>
            </h3>
        </div>
        <div class="kt-card-content">
            @if($assignments->count() > 0)
                @foreach($assignmentsByItem as $itemId => $itemAssignments)
                    @php
                        $item = $itemAssignments->first()->manningRequestItem;
                    @endphp
                    <div class="mb-6 last:mb-0">
                        <div class="mb-3 pb-2 border-b border-primary/20">
                            <h4 class="text-md font-semibold text-primary">
                                {{ $item->rank ?? 'Unknown Rank' }} 
                                <span class="text-sm font-normal text-secondary-foreground">
                                    ({{ $itemAssignments->count() }} of {{ $item->quantity_needed ?? 0 }} needed)
                                </span>
                            </h4>
                        </div>
                        
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <table class="kt-table w-full">
                                    <thead>
                                        <tr class="border-b border-border">
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Officer</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Service No</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Rank</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">From Command</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">To Command</th>
                                            <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">Zone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemAssignments as $assignment)
                                            <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                                <td class="py-3 px-4">
                                                    <span class="text-sm font-medium text-foreground">
                                                        {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground font-mono">
                                                    {{ $assignment->officer->service_number ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->officer->substantive_rank ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->fromCommand->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->toCommand->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3 px-4 text-sm text-secondary-foreground">
                                                    {{ $assignment->officer->presentStation->zone->name ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden">
                            <div class="flex flex-col gap-3">
                                @foreach($itemAssignments as $assignment)
                                    <div class="p-4 rounded-lg bg-muted/50 border border-input">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground font-mono">
                                                {{ $assignment->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs text-secondary-foreground">
                                            <div>Rank: <span class="font-semibold">{{ $assignment->officer->substantive_rank ?? 'N/A' }}</span></div>
                                            <div>Zone: <span class="font-semibold">{{ $assignment->officer->presentStation->zone->name ?? 'N/A' }}</span></div>
                                            <div>From: <span class="font-semibold">{{ $assignment->fromCommand->name ?? 'N/A' }}</span></div>
                                            <div>To: <span class="font-semibold">{{ $assignment->toCommand->name ?? 'N/A' }}</span></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers from this request are currently in the draft deployment.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


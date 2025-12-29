@extends('layouts.app')

@section('title', 'Investigation Details')
@section('page-title', 'Investigation Details')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.dashboard') }}">Investigation Unit</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('investigation.index') }}">Investigations</a>
    <span>/</span>
    <span class="text-primary">Details</span>
@endsection

@section('content')
@if(session('success'))
    <div class="kt-card bg-success/10 border border-success/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-check-circle text-success text-xl"></i>
                <p class="text-sm text-success">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="kt-card bg-danger/10 border border-danger/20 mb-5">
        <div class="kt-card-content p-4">
            <div class="flex items-center gap-3">
                <i class="ki-filled ki-information text-danger text-xl"></i>
                <p class="text-sm text-danger">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="grid gap-5 lg:gap-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Investigation Details</h3>
            <div class="kt-card-toolbar">
                @if($investigation->status !== 'RESOLVED')
                    <a href="{{ route('investigation.edit', $investigation->id) }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil"></i> Update Status
                    </a>
                @endif
            </div>
        </div>
        <div class="kt-card-content">
            <div class="grid gap-5">
                <!-- Officer Information -->
                <div class="p-4 bg-muted/50 rounded-lg border border-input">
                    <h4 class="font-semibold mb-3">Officer Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-secondary-foreground">Name:</span>
                            <p class="font-medium">{{ $investigation->officer->initials }} {{ $investigation->officer->surname }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Service Number:</span>
                            <p class="font-medium">{{ $investigation->officer->service_number }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Rank:</span>
                            <p class="font-medium">{{ $investigation->officer->substantive_rank }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-secondary-foreground">Command:</span>
                            <p class="font-medium">{{ $investigation->officer->presentStation->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Investigation Status -->
                <div>
                    <label class="block text-sm font-medium mb-2">Investigation Status</label>
                    <div>
                        @if($investigation->status === 'INVITED')
                            <span class="kt-badge kt-badge-info text-base">Invited</span>
                        @elseif($investigation->status === 'ONGOING_INVESTIGATION')
                            <span class="kt-badge kt-badge-warning text-base">Ongoing Investigation</span>
                        @elseif($investigation->status === 'INTERDICTED')
                            <span class="kt-badge kt-badge-danger text-base">Interdicted</span>
                        @elseif($investigation->status === 'SUSPENDED')
                            <span class="kt-badge kt-badge-danger text-base">Suspended</span>
                        @else
                            <span class="kt-badge kt-badge-success text-base">Resolved</span>
                        @endif
                    </div>
                </div>

                <!-- Invitation Message -->
                <div>
                    <label class="block text-sm font-medium mb-2">Invitation Message</label>
                    <div class="p-4 bg-muted/30 rounded-lg border border-input">
                        <p class="text-sm whitespace-pre-wrap">{{ $investigation->invitation_message }}</p>
                    </div>
                </div>

                <!-- Notes -->
                @if($investigation->notes)
                <div>
                    <label class="block text-sm font-medium mb-2">Investigation Notes</label>
                    <div class="p-4 bg-muted/30 rounded-lg border border-input">
                        <p class="text-sm whitespace-pre-wrap">{{ $investigation->notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Resolution Notes -->
                @if($investigation->resolution_notes)
                <div>
                    <label class="block text-sm font-medium mb-2">Resolution Notes</label>
                    <div class="p-4 bg-muted/30 rounded-lg border border-input">
                        <p class="text-sm whitespace-pre-wrap">{{ $investigation->resolution_notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Timeline -->
                <div>
                    <label class="block text-sm font-medium mb-2">Investigation Timeline</label>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-8 rounded-full bg-info/10">
                                <i class="ki-filled ki-calendar text-info"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Invited</p>
                                <p class="text-xs text-secondary-foreground">
                                    {{ $investigation->invited_at ? $investigation->invited_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        @if($investigation->status_changed_at)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-8 rounded-full bg-warning/10">
                                <i class="ki-filled ki-calendar-tick text-warning"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Status Changed</p>
                                <p class="text-xs text-secondary-foreground">
                                    {{ $investigation->status_changed_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                        @endif
                        @if($investigation->resolved_at)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center size-8 rounded-full bg-success/10">
                                <i class="ki-filled ki-check-circle text-success"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Resolved</p>
                                <p class="text-xs text-secondary-foreground">
                                    {{ $investigation->resolved_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Investigation Officer -->
                <div>
                    <label class="block text-sm font-medium mb-2">Investigation Officer</label>
                    <p class="text-sm">{{ $investigation->investigationOfficer->email ?? 'N/A' }}</p>
                </div>

                <!-- Actions -->
                @if($investigation->status !== 'RESOLVED')
                <div class="flex items-center gap-3 pt-4 border-t border-border">
                    <a href="{{ route('investigation.edit', $investigation->id) }}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil"></i> Update Status
                    </a>
                    <form action="{{ route('investigation.resolve', $investigation->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="kt-btn kt-btn-success"
                                onclick="return confirm('Are you sure you want to resolve this investigation? This will clear all investigation statuses for this officer.')">
                            <i class="ki-filled ki-check-circle"></i> Resolve Investigation
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


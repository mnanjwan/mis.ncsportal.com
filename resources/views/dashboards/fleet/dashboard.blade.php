@extends('layouts.app')

@section('title', $title ?? 'Fleet Dashboard')
@section('page-title', $title ?? 'Fleet Dashboard')

@section('content')
    @php
        $toneBg = [
            'primary' => 'bg-primary/10 text-primary',
            'secondary' => 'bg-muted text-foreground',
            'success' => 'bg-success/10 text-success',
            'warning' => 'bg-warning/10 text-warning',
            'info' => 'bg-info/10 text-info',
        ];
    @endphp

    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card bg-primary/10 border border-primary/20">
            <div class="kt-card-content p-5 lg:p-7.5">
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                    <span class="text-primary uppercase tracking-wide">{{ $roleName ?? 'Fleet' }}</span>
                    <span class="text-foreground">|</span>
                    <span class="text-secondary-foreground">Inbox:</span>
                    <span class="text-foreground">{{ number_format($inboxCount ?? 0) }}</span>
                    @if(!empty($commandId))
                        <span class="text-foreground">|</span>
                        <span class="text-secondary-foreground">Command ID:</span>
                        <span class="text-foreground">{{ $commandId }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($cards))
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-7.5">
                @foreach($cards as $card)
                    <div class="kt-card transition hover:shadow-sm hover:-translate-y-0.5">
                        <div class="kt-card-content flex flex-col gap-4 p-5 lg:p-7.5">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-normal text-secondary-foreground">{{ $card['label'] }}</span>
                                    <span class="text-2xl font-semibold text-mono">{{ $card['value'] }}</span>
                                </div>
                                <div class="flex items-center justify-center size-12 rounded-full {{ $toneBg[$card['tone']] ?? 'bg-muted text-foreground' }}">
                                    <i class="ki-filled {{ $card['icon'] }} text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($quickLinks))
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Quick Actions</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        @foreach($quickLinks as $link)
                            <a href="{{ $link['href'] }}"
                               class="kt-btn kt-btn-outline w-full transition hover:-translate-y-0.5">
                                <i class="ki-filled {{ $link['icon'] }}"></i>
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Requests Inbox</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('fleet.requests.index') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if(isset($inboxItems) && $inboxItems->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($inboxItems as $item)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-input">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-foreground">
                                        Request #{{ $item->id }} • {{ $item->originCommand->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Status: {{ $item->status }} | Step: {{ $item->current_step_order ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-secondary-foreground">
                                        Created by: {{ $item->createdBy->email ?? 'N/A' }}
                                    </span>
                                </div>
                                <a href="{{ route('fleet.requests.show', $item->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-eye"></i> Open
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="ki-filled ki-inbox text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No requests waiting for your action.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


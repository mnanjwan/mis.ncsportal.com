@extends('layouts.app')

@section('title', 'Zone Details')
@section('page-title', 'Zone Details: {{ $zone->name }}')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.zones.index') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.zones.index') }}">Zones</a>
    <span>/</span>
    <span class="text-primary">{{ $zone->name }}</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Zone Info Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">{{ $zone->name }}</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.zones.edit', $zone->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-notepad-edit"></i> Edit Zone
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Zone Code</label>
                        <p class="text-sm font-mono text-foreground mt-1">{{ $zone->code }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Status</label>
                        <p class="mt-1">
                            <span class="kt-badge kt-badge-{{ $zone->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                {{ $zone->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    @if($zone->description)
                        <div class="md:col-span-2">
                            <label class="text-xs text-secondary-foreground uppercase">Description</label>
                            <p class="text-sm text-foreground mt-1">{{ $zone->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Commands Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Commands in {{ $zone->name }}</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.commands.create', ['zone_id' => $zone->id]) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Add Command
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                @if($zone->commands->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Command Name
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Code
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Location
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Status
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zone->commands as $command)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $command->name }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-secondary-foreground">{{ $command->code }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $command->location ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $command->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $command->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('hrd.commands.edit', $command->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground mb-4">No commands assigned to this zone</p>
                        <a href="{{ route('hrd.commands.create', ['zone_id' => $zone->id]) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i> Add First Command
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Command Details')
@section('page-title')
Command Details: {{ $command->name }}
@endsection

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Settings</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Commands</a>
    <span>/</span>
    <span class="text-primary">{{ $command->name }}</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Command Info Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">{{ $command->name }}</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.commands.edit', $command->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-notepad-edit"></i> Edit Command
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Command Code</label>
                        <p class="text-sm font-mono text-foreground mt-1">{{ $command->code }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Zone</label>
                        <p class="text-sm text-foreground mt-1">
                            @if($command->zone)
                                {{ $command->zone->name }} ({{ $command->zone->code }})
                            @else
                                <span class="text-secondary-foreground italic">No Zone Assigned</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Location</label>
                        <p class="text-sm text-foreground mt-1">{{ $command->location ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-secondary-foreground uppercase">Status</label>
                        <p class="mt-1">
                            <span class="kt-badge kt-badge-{{ $command->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                {{ $command->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    @if($command->areaController)
                        <div>
                            <label class="text-xs text-secondary-foreground uppercase">Area Controller</label>
                            <p class="text-sm text-foreground mt-1">
                                {{ $command->areaController->initials }} {{ $command->areaController->surname }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Officers Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    Officers in {{ $command->name }}
                    @if($command->zone)
                        <span class="text-sm text-secondary-foreground font-normal">({{ $command->zone->name }})</span>
                    @endif
                </h3>
            </div>
            <div class="kt-card-content">
                @if($officers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer Name
                                            @if(request('sort_by') === 'name' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Service Number
                                            @if(request('sort_by') === 'service_number')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'rank', 'sort_order' => request('sort_by') === 'rank' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Rank
                                            @if(request('sort_by') === 'rank')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Status
                                            @if(request('sort_by') === 'status')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($officers->take(20) as $officer)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $officer->initials }} {{ $officer->surname }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-secondary-foreground">{{ $officer->service_number }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $officer->substantive_rank }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $officer->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $officer->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($officers->count() > 20)
                        <p class="text-sm text-secondary-foreground mt-4 text-center">
                            Showing 20 of {{ $officers->count() }} officers. 
                            <a href="{{ route('hrd.officers', ['command_id' => $command->id]) }}" class="text-primary hover:underline">
                                View all officers
                            </a>
                        </p>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No officers assigned to this command</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


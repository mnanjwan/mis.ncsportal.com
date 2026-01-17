@extends('layouts.app')

@section('title', 'Zones')
@section('page-title', 'Zones Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.zones.index') }}">Settings</a>
    <span>/</span>
    <span class="text-primary">Zones</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="kt-card bg-success/10 border border-success/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-check-circle text-success text-xl"></i>
                        <p class="text-sm text-success font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="kt-card bg-danger/10 border border-danger/20">
                <div class="kt-card-content p-4">
                    <div class="flex items-center gap-3">
                        <i class="ki-filled ki-information text-danger text-xl"></i>
                        <p class="text-sm text-danger font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Zones Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Zones</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.zones.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Zone
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <p class="text-sm text-secondary-foreground mb-4">
                    Manage organizational zones. All commands must belong to a zone.
                </p>

                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Zone Name
                                            @if(request('sort_by') === 'name' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_order' => request('sort_by') === 'code' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Code
                                            @if(request('sort_by') === 'code')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'commands_count', 'sort_order' => request('sort_by') === 'commands_count' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Commands
                                            @if(request('sort_by') === 'commands_count')
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
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($zones as $zone)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-foreground">
                                                    {{ $zone->name }}
                                                </span>
                                                @if($zone->description)
                                                    <span class="text-xs text-secondary-foreground mt-1">
                                                        {{ Str::limit($zone->description, 60) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-secondary-foreground">
                                                {{ $zone->code }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground font-medium">
                                                {{ $zone->commands_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $zone->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('hrd.zones.show', $zone->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('hrd.zones.edit', $zone->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="Edit">
                                                    <i class="ki-filled ki-notepad-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No zones found</p>
                                            <a href="{{ route('hrd.zones.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">
                                                <i class="ki-filled ki-plus"></i> Create First Zone
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        @forelse($zones as $zone)
                            <div class="kt-card">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-foreground mb-1">{{ $zone->name }}</h4>
                                            <p class="text-xs text-secondary-foreground font-mono mb-2">{{ $zone->code }}</p>
                                            @if($zone->description)
                                                <p class="text-xs text-secondary-foreground">{{ Str::limit($zone->description, 100) }}</p>
                                            @endif
                                        </div>
                                        <span class="kt-badge kt-badge-{{ $zone->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                            {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-secondary-foreground">
                                            Commands: <strong>{{ $zone->commands_count ?? 0 }}</strong>
                                        </span>
                                        <div class="flex gap-2">
                                            <a href="{{ route('hrd.zones.show', $zone->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-eye"></i>
                                            </a>
                                            <a href="{{ route('hrd.zones.edit', $zone->id) }}" 
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No zones found</p>
                                <a href="{{ route('hrd.zones.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-plus"></i> Create First Zone
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                <x-pagination :paginator="$zones->withQueryString()" item-name="zones" />
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Commands')
@section('page-title', 'Commands Management')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.commands.index') }}">Settings</a>
    <span>/</span>
    <span class="text-primary">Commands</span>
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

        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Commands</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('hrd.commands.index') }}" class="flex flex-col gap-4">
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="kt-input pl-10 w-full" 
                                       placeholder="Search by name, code, or location...">
                            </div>
                        </div>

                        <!-- Zone Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Zone</label>
                            <select name="zone_id" class="kt-input w-full">
                                <option value="">All Zones</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ (string)request('zone_id') === (string)$zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'zone_id']))
                                <a href="{{ route('hrd.commands.index') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Commands Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">All Commands</h3>
                <div class="kt-card-toolbar">
                    <a href="{{ route('hrd.commands.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                        <i class="ki-filled ki-plus"></i> Create Command
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Command Name
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'zone', 'sort_order' => request('sort_by') === 'zone' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Zone
                                            @if(request('sort_by') === 'zone')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'location', 'sort_order' => request('sort_by') === 'location' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Location
                                            @if(request('sort_by') === 'location')
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
                                @forelse($commands as $command)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $command->name }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-mono text-secondary-foreground">{{ $command->code }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($command->zone)
                                                <span class="text-sm text-foreground">{{ $command->zone->name }}</span>
                                            @else
                                                <span class="text-xs text-secondary-foreground italic">No Zone</span>
                                            @endif
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
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('hrd.commands.show', $command->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('hrd.commands.edit', $command->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost"
                                                   title="Edit">
                                                    <i class="ki-filled ki-notepad-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No commands found</p>
                                            <a href="{{ route('hrd.commands.create') }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">
                                                <i class="ki-filled ki-plus"></i> Create First Command
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
                        @forelse($commands as $command)
                            <div class="kt-card">
                                <div class="kt-card-content p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-foreground mb-1">{{ $command->name }}</h4>
                                            <p class="text-xs text-secondary-foreground font-mono mb-2">{{ $command->code }}</p>
                                            @if($command->zone)
                                                <p class="text-xs text-secondary-foreground">Zone: {{ $command->zone->name }}</p>
                                            @endif
                                        </div>
                                        <span class="kt-badge kt-badge-{{ $command->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                            {{ $command->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('hrd.commands.show', $command->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            <i class="ki-filled ki-eye"></i>
                                        </a>
                                        <a href="{{ route('hrd.commands.edit', $command->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-ghost">
                                            <i class="ki-filled ki-notepad-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground mb-4">No commands found</p>
                                <a href="{{ route('hrd.commands.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                    <i class="ki-filled ki-plus"></i> Create First Command
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($commands->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $commands->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Zone Officers')
@section('page-title')
Zone Officers
@if($coordinatorZone)
    <span class="text-sm text-secondary-foreground font-normal">({{ $coordinatorZone->name }})</span>
@endif
@endsection

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('zone-coordinator.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span class="text-primary">Zone Officers</span>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <!-- Filters Card -->
    <div class="kt-card">
        <div class="kt-card-content">
            <form method="GET" action="{{ route('zone-coordinator.officers') }}" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="kt-form-label">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Service number, name, email..."
                           class="kt-input">
                </div>
                <div>
                    <label class="kt-form-label">Rank</label>
                    <select name="rank" class="kt-input">
                        <option value="">All Ranks</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank }}" {{ request('rank') === $rank ? 'selected' : '' }}>
                                {{ $rank }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="kt-form-label">Command</label>
                    <select name="command_id" class="kt-input">
                        <option value="">All Commands</option>
                        @foreach($commands as $command)
                            <option value="{{ $command->id }}" {{ request('command_id') == $command->id ? 'selected' : '' }}>
                                {{ $command->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="kt-btn kt-btn-primary flex-1">
                        <i class="ki-filled ki-magnifier"></i> Filter
                    </button>
                    <a href="{{ route('zone-coordinator.officers') }}" class="kt-btn kt-btn-ghost">
                        <i class="ki-filled ki-cross"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Officers Table -->
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers in {{ $coordinatorZone->name ?? 'Your Zone' }}</h3>
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
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'command', 'sort_order' => request('sort_by') === 'command' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center gap-1 hover:text-primary transition-colors">
                                        Command
                                        @if(request('sort_by') === 'command')
                                            <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                        @else
                                            <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                    Grade Level
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
                            @foreach($officers as $officer)
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
                                    <td class="py-3 px-4 text-sm text-secondary-foreground">
                                        {{ $officer->presentStation->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="kt-badge kt-badge-{{ (int)filter_var($officer->salary_grade_level, FILTER_SANITIZE_NUMBER_INT) <= 7 ? 'success' : 'warning' }} kt-badge-sm">
                                            {{ $officer->salary_grade_level }}
                                        </span>
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
                
                <div class="mt-4">
                    {{ $officers->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers found in your zone</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


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
        <div class="kt-card-header">
            <h3 class="kt-card-title">Filter Officers</h3>
        </div>
        <div class="kt-card-content">
            <form method="GET" action="{{ route('zone-coordinator.officers') }}" class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- Search Input -->
                    <div class="flex-1 min-w-[250px] w-full md:w-auto">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               class="kt-input w-full" 
                               placeholder="Search by service number, name, email...">
                    </div>

                    <!-- Rank Select -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Rank</label>
                        <select name="rank" class="kt-input w-full">
                            <option value="">All Ranks</option>
                            @foreach($ranks as $rank)
                                <option value="{{ $rank }}" {{ request('rank') === $rank ? 'selected' : '' }}>
                                    {{ $rank }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Command Select -->
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                        <select name="command_id" class="kt-input w-full">
                            <option value="">All Commands</option>
                            @foreach($commands as $command)
                                <option value="{{ $command->id }}" {{ request('command_id') == $command->id ? 'selected' : '' }}>
                                    {{ $command->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="submit" class="kt-btn kt-btn-primary w-full md:w-auto">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                        @if(request()->anyFilled(['search', 'rank', 'command_id', 'sort_by', 'sort_order']))
                            <a href="{{ route('zone-coordinator.officers') }}" class="kt-btn kt-btn-outline w-full md:w-auto">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Officers Table -->
    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Officers in {{ $coordinatorZone->name ?? 'Your Zone' }}</h3>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
            @if($officers->count() > 0)
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                    Grade Level
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
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
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-medium text-foreground">
                                            {{ $officer->initials }} {{ $officer->surname }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="text-sm font-mono text-foreground">{{ $officer->service_number }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $officer->substantive_rank }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        {{ $officer->presentStation->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="kt-badge kt-badge-{{ (int)filter_var($officer->salary_grade_level, FILTER_SANITIZE_NUMBER_INT) <= 7 ? 'success' : 'warning' }} kt-badge-sm">
                                            {{ $officer->salary_grade_level }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4" style="white-space: nowrap;">
                                        <span class="kt-badge kt-badge-{{ $officer->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                            {{ $officer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($officers->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $officers->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12 px-4">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No officers found in your zone</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Prevent page from expanding beyond viewport on mobile */
    @media (max-width: 768px) {
        body {
            overflow-x: hidden;
        }

        .kt-card {
            max-width: 100vw;
        }
    }

    /* Smooth scrolling for mobile */
    .table-scroll-wrapper {
        position: relative;
        max-width: 100%;
    }

    /* Custom scrollbar for webkit browsers */
    .scrollbar-thin::-webkit-scrollbar {
        height: 8px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endsection


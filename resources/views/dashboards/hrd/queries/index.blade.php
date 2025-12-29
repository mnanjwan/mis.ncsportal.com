@extends('layouts.app')

@section('title', 'Accepted Queries')
@section('page-title', 'Accepted Queries')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('hrd.dashboard') }}">HRD</a>
    <span>/</span>
    <span class="text-primary">Queries</span>
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
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-foreground">Accepted Queries (Disciplinary Record)</h2>
    </div>

    <div class="kt-card overflow-hidden">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Query List</h3>
        </div>
        <div class="kt-card-content p-5">
            <form method="GET" action="{{ route('hrd.queries.index') }}" class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <!-- Search Input -->
                    <div class="w-full md:flex-1 md:min-w-[250px]">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               class="kt-input w-full" 
                               placeholder="Search by officer name, service number, reason...">
                    </div>

                    <!-- Command Select -->
                    <div class="w-full md:w-48 flex-shrink-0">
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
                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary whitespace-nowrap">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                        @if(request('search') || request('command_id'))
                            <a href="{{ route('hrd.queries.index') }}" class="kt-btn kt-btn-sm kt-btn-outline whitespace-nowrap">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="kt-card-content p-0 md:p-5 overflow-x-hidden pt-0">
            @if($queries->count() > 0)
                <!-- Table with horizontal scroll wrapper -->
                <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                    <table class="kt-table" style="min-width: 900px; width: 100%;">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Command</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Reason</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Issued By</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Issued Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Reviewed Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queries as $query)
                                <tr class="border-b border-border last:border-0 hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <span class="font-medium">{{ $query->officer->initials }} {{ $query->officer->surname }}</span>
                                            <div class="text-xs text-muted-foreground">{{ $query->officer->service_number }}</div>
                                            <div class="text-xs text-muted-foreground">{{ $query->officer->presentStation->name ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="max-w-xs truncate" title="{{ $query->reason }}">
                                            {{ Str::limit($query->reason, 50) }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issuedBy->name ?? $query->issuedBy->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->issued_at ? $query->issued_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $query->reviewed_at ? $query->reviewed_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('hrd.queries.show', $query->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 md:px-5 py-4 border-t border-border">
                    {{ $queries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-secondary-foreground">No accepted queries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


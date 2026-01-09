@extends('layouts.app')

@section('title', 'Processed Emoluments History')
@section('page-title', 'Processed Emoluments History')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Processed History</span>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter & Export</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('accounts.processed-history') }}" class="flex flex-col gap-4" id="filter-form">
                    <!-- Filter Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Search</label>
                            <div class="relative">
                                <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-10">
                            </div>
                        </div>

                        <!-- Zone Select -->
                        <div>
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

                        <!-- Command Select -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <select name="command_id" class="kt-input w-full">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ (string)request('command_id') === (string)$command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Select -->
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Year</label>
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date From</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}" 
                                   class="kt-input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Date To</label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}" 
                                   class="kt-input w-full">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-filter"></i> Filter
                        </button>
                        @if(request()->anyFilled(['search', 'zone_id', 'command_id', 'year', 'date_from', 'date_to']))
                            <a href="{{ route('accounts.processed-history') }}" class="kt-btn kt-btn-outline">
                                Clear
                            </a>
                        @endif
                        <button type="button" 
                                class="kt-btn kt-btn-primary"
                                onclick="printReport()">
                            <i class="ki-filled ki-printer"></i> Print
                        </button>
                        <button type="button" 
                                class="kt-btn kt-btn-success"
                                onclick="exportReport('csv')">
                            <i class="ki-filled ki-file-down"></i> Export CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Processed Emoluments List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Processed Emoluments</h3>
                <div class="kt-card-toolbar">
                    <span class="text-sm text-secondary-foreground">
                        Total: {{ $emoluments->total() }} records
                    </span>
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer_id', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer
                                            @if(request('sort_by') === 'officer_id')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_by') === 'year' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Year
                                            @if(request('sort_by') === 'year' || !request('sort_by'))
                                                <i class="ki-filled ki-arrow-{{ (request('sort_by') === 'year' && request('sort_order') === 'desc') || !request('sort_by') ? 'down' : 'up' }} text-xs"></i>
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
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
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
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'desc' ? 'down' : 'up' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'processed_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Processed Date
                                            @if(request('sort_by') === 'processed_at' || !request('sort_by'))
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
                                @forelse($emoluments as $emolument)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">
                                                {{ $emolument->officer->initials ?? '' }}
                                                {{ $emolument->officer->surname ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-mono">
                                                {{ $emolument->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            {{ $emolument->year }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->presentStation->zone->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            {{ $emolument->processed_at ? $emolument->processed_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                               class="kt-btn kt-btn-sm kt-btn-ghost">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No processed emoluments found</p>
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
                        @forelse($emoluments as $emolument)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10">
                                        <i class="ki-filled ki-wallet text-success text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->year }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                | {{ $emolument->officer->presentStation->zone->name ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                | {{ $emolument->officer->presentStation->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-secondary-foreground">
                                            Processed: {{ $emolument->processed_at ? $emolument->processed_at->format('d/m/Y') : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a href="{{ route('accounts.emoluments.show', $emolument->id) }}"
                                       class="kt-btn kt-btn-ghost kt-btn-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No processed emoluments found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($emoluments->hasPages())
                    <div class="mt-6 pt-4 border-t border-border">
                        {{ $emoluments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function exportReport(format) {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            formData.append('format', format);
            
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            window.location.href = '{{ route("accounts.processed-history.export") }}?' + params.toString();
        }

        function printReport() {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Preserve sort parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('sort_by')) {
                params.append('sort_by', urlParams.get('sort_by'));
            }
            if (urlParams.get('sort_order')) {
                params.append('sort_order', urlParams.get('sort_order'));
            }
            
            const printUrl = '{{ route("accounts.processed-history.print") }}?' + params.toString();
            window.open(printUrl, '_blank');
        }
    </script>
    @endpush
@endsection


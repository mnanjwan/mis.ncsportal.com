@extends('layouts.app')

@section('title', 'Emoluments for Validation')
@section('page-title', 'Emoluments for Validation')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('validator.dashboard') }}">Validator</a>
    <span>/</span>
    <span class="text-primary">Emoluments</span>
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
                <h3 class="kt-card-title">Filter Emoluments</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('validator.emoluments') }}" class="flex flex-col gap-4">
                    <!-- Preserve sort params -->
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request('sort_order'))
                        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                    @endif

                    <!-- Filter Controls -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <div class="relative">
                                <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-10">
                            </div>
                        </div>

                        <!-- Status Select -->
                        <div class="w-full sm:w-48">
                            <select name="status" class="kt-input w-full">
                                <option value="">All Statuses</option>
                                <option value="ASSESSED" {{ request('status') == 'ASSESSED' ? 'selected' : '' }}>Assessed</option>
                                <option value="VALIDATED" {{ request('status') == 'VALIDATED' ? 'selected' : '' }}>Validated</option>
                            </select>
                        </div>

                        <!-- Year Select -->
                        <div class="w-full sm:w-32">
                            <select name="year" class="kt-input w-full">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status', 'year']))
                                <a href="{{ route('validator.emoluments') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Emoluments List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Emoluments for Validation</h3>
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
                                        Officer
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'year', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Year
                                            @if(request('sort_by') === 'year')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Status
                                            @if(request('sort_by') === 'status')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'assessed_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Assessed
                                            @if(request('sort_by') === 'assessed_at' || !request('sort_by'))
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
                                    @php
                                        $statusClass = match ($emolument->status) {
                                            'ASSESSED' => 'primary',
                                            'VALIDATED' => 'success',
                                            default => 'secondary'
                                        };
                                    @endphp
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
                                        <td class="py-3 px-4">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">
                                            @php
                                                $assessedDate = $emolument->assessed_at 
                                                    ?? ($emolument->assessment ? $emolument->assessment->created_at : null);
                                            @endphp
                                            {{ $assessedDate ? $assessedDate->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($emolument->status === 'ASSESSED')
                                                <a href="{{ route('validator.emoluments.validate', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-primary">
                                                    Validate
                                                </a>
                                            @else
                                                <a href="{{ route('validator.emoluments.show', $emolument->id) }}"
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No emoluments found</p>
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
                            @php
                                $statusClass = match ($emolument->status) {
                                    'ASSESSED' => 'primary',
                                    'VALIDATED' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10">
                                        <i class="ki-filled ki-wallet text-primary text-xl"></i>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-mono">
                                            {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $emolument->officer->service_number ?? 'N/A' }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="kt-badge kt-badge-{{ $statusClass }} kt-badge-sm">
                                                {{ $emolument->status }}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {{ $emolument->year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="text-xs text-secondary-foreground">
                                        @php
                                            $assessedDate = $emolument->assessed_at 
                                                ?? ($emolument->assessment ? $emolument->assessment->created_at : null);
                                        @endphp
                                        {{ $assessedDate ? $assessedDate->format('d/m/Y') : 'N/A' }}
                                    </span>
                                    @if($emolument->status === 'ASSESSED')
                                        <a href="{{ route('validator.emoluments.validate', $emolument->id) }}"
                                           class="kt-btn kt-btn-primary kt-btn-sm">
                                            Validate
                                        </a>
                                    @else
                                        <a href="{{ route('validator.emoluments.show', $emolument->id) }}"
                                           class="kt-btn kt-btn-ghost kt-btn-sm">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-information text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No emoluments found</p>
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
@endsection


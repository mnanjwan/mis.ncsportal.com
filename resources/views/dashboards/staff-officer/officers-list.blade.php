@extends('layouts.app')

@section('title', 'Command Officers')
@section('page-title', 'Command Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('staff-officer.dashboard') }}">Staff Officer</a>
    <span>/</span>
    <span class="text-primary">Officers</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Filters Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Filter Officers</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('staff-officer.officers') }}" class="flex flex-col gap-4">
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
                                       placeholder="Search by name, service number, or email...">
                            </div>
                        </div>

                        <!-- Rank Select -->
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Rank</label>
                            <select name="rank" class="kt-input w-full">
                                <option value="">All Ranks</option>
                                @foreach($ranks as $rank)
                                    <option value="{{ $rank }}" {{ request('rank') == $rank ? 'selected' : '' }}>
                                        {{ $rank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'rank', 'sort_by', 'sort_order']))
                                <a href="{{ route('staff-officer.officers') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Officers List Card -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Command Officers</h3>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 900px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer Details
                                            @if(request('sort_by') === 'name')
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
                                        Documentation Status
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Status
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($officers as $officer)
                                    @php
                                        $initials = $officer->initials ?? '';
                                        $surname = $officer->surname ?? '';
                                        $fullName = trim("{$initials} {$surname}");
                                        $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                        $isDocumented = $officer->currentPosting && $officer->currentPosting->documented_at;
                                    @endphp
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm" style="flex-shrink: 0;">
                                                    {{ $avatarInitials }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-foreground">{{ $fullName }}</div>
                                                    <div class="text-xs text-secondary-foreground">{{ $officer->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="text-sm font-mono text-foreground">
                                                {{ $officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground" style="white-space: nowrap;">
                                            {{ $officer->substantive_rank ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            @if($isDocumented)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">
                                                    <i class="ki-filled ki-check-circle"></i> Documented
                                                </span>
                                                @if($officer->currentPosting->documented_at)
                                                    <div class="text-xs text-secondary-foreground mt-1">
                                                        {{ $officer->currentPosting->documented_at->format('M d, Y') }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                    <i class="ki-filled ki-time"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4" style="white-space: nowrap;">
                                            <span class="kt-badge kt-badge-{{ $officer->is_active ? 'success' : 'danger' }} kt-badge-sm">
                                                {{ $officer->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right" style="white-space: nowrap;">
                                            <div class="flex items-center justify-end gap-2">
                                                @if(!$isDocumented)
                                                    <form action="{{ route('staff-officer.officers.document', $officer->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary" onclick="return confirm('Document this officer? This confirms their arrival at the command.')">
                                                            <i class="ki-filled ki-file-check"></i> Document
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('staff-officer.officers.show', $officer->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    <i class="ki-filled ki-eye"></i> View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center">
                                            <i class="ki-filled ki-information-2 text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-secondary-foreground">No officers found</p>
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
                        @forelse($officers as $officer)
                            @php
                                $initials = $officer->initials ?? '';
                                $surname = $officer->surname ?? '';
                                $fullName = trim("{$initials} {$surname}");
                                $avatarInitials = strtoupper(($initials[0] ?? '') . ($surname[0] ?? ''));
                                $isDocumented = $officer->currentPosting && $officer->currentPosting->documented_at;
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50 border border-input hover:bg-muted transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                        {{ $avatarInitials }}
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-foreground">{{ $fullName }}</span>
                                        <span class="text-xs text-secondary-foreground">
                                            SVC: {{ $officer->service_number ?? 'N/A' }}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {{ $officer->substantive_rank ?? 'N/A' }}
                                        </span>
                                        <div class="mt-1">
                                            @if($isDocumented)
                                                <span class="kt-badge kt-badge-success kt-badge-sm">Documented</span>
                                            @else
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">Pending Documentation</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    @if(!$isDocumented)
                                        <form action="{{ route('staff-officer.officers.document', $officer->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary" onclick="return confirm('Document this officer?')">
                                                <i class="ki-filled ki-file-check"></i> Document
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('staff-officer.officers.show', $officer->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost">
                                        <i class="ki-filled ki-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="ki-filled ki-profile-circle text-4xl text-muted-foreground mb-4"></i>
                                <p class="text-secondary-foreground">No officers found</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($officers->hasPages())
                    <div class="mt-6 pt-4 border-t border-border px-4">
                        {{ $officers->withQueryString()->links() }}
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


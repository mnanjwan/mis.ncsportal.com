@extends('layouts.app')

@section('title', 'Emolument Validation')
@section('page-title', 'Emolument Validation')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('area-controller.dashboard') }}">Area Controller</a>
    <span>/</span>
    <span class="text-primary">Emoluments</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-mono">Emolument Validation</h2>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="kt-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Pending Validation</p>
                        <h3 class="text-2xl font-bold text-foreground mt-1">{{ $pendingValidation ?? 0 }}</h3>
                    </div>
                    <div class="h-10 w-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600">
                        <i class="ki-filled ki-time text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="kt-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Validated Today</p>
                        <h3 class="text-2xl font-bold text-foreground mt-1">{{ $validatedToday ?? 0 }}</h3>
                    </div>
                    <div class="h-10 w-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                        <i class="ki-filled ki-check-circle text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="kt-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Total Processed</p>
                        <h3 class="text-2xl font-bold text-foreground mt-1">{{ $totalProcessed ?? 0 }}</h3>
                    </div>
                    <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="ki-filled ki-chart-pie-simple text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pending Validations</h3>
            </div>
            <div class="kt-card-content">
                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="kt-table w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'officer', 'sort_order' => request('sort_by') === 'officer' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Officer
                                            @if(request('sort_by') === 'officer')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'service_number', 'sort_order' => request('sort_by') === 'service_number' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Service No
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
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'assessor', 'sort_order' => request('sort_by') === 'assessor' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Assessed By
                                            @if(request('sort_by') === 'assessor')
                                                <i class="ki-filled ki-arrow-{{ request('sort_order') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                            @else
                                                <i class="ki-filled ki-arrow-up-down text-xs opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'assessed_at', 'sort_order' => request('sort_by') === 'assessed_at' && request('sort_order') === 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center gap-1 hover:text-primary transition-colors">
                                            Date Assessed
                                            @if(request('sort_by') === 'assessed_at')
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
                                                {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-foreground">
                                                {{ $emolument->officer->service_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $emolument->officer->display_rank }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                @if($emolument->assessment && $emolument->assessment->assessor)
                                                    {{ $emolument->assessment->assessor->name ?? $emolument->assessment->assessor->email }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-sm text-secondary-foreground">
                                                {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('area-controller.emoluments.show', $emolument->id) }}" 
                                                   class="kt-btn kt-btn-sm kt-btn-ghost">
                                                    View
                                                </a>
                                                @if($emolument->status === 'ASSESSED')
                                                    <a href="{{ route('area-controller.emoluments.validate', $emolument->id) }}" 
                                                       class="kt-btn kt-btn-sm kt-btn-primary">
                                                        Validate
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-8 text-muted-foreground">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-secondary flex items-center justify-center mb-3">
                                                    <i class="ki-filled ki-check-square text-xl text-muted-foreground"></i>
                                                </div>
                                                <p class="text-base font-medium">No pending validations</p>
                                                <p class="text-sm text-muted-foreground mt-1">All assessed emoluments have been validated</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4">
                    @forelse($emoluments as $emolument)
                        <div class="kt-card p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-foreground">
                                        {{ $emolument->officer->initials ?? '' }} {{ $emolument->officer->surname ?? '' }}
                                    </h4>
                                    <p class="text-sm text-secondary-foreground mt-1">
                                        {{ $emolument->officer->service_number ?? 'N/A' }} • {{ $emolument->officer->display_rank }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('area-controller.emoluments.show', $emolument->id) }}" 
                                       class="kt-btn kt-btn-sm kt-btn-ghost">
                                        View
                                    </a>
                                    @if($emolument->status === 'ASSESSED')
                                        <a href="{{ route('area-controller.emoluments.validate', $emolument->id) }}" 
                                           class="kt-btn kt-btn-sm kt-btn-primary">
                                            Validate
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-secondary-foreground">Assessed By:</span>
                                    <span class="text-foreground ml-1">
                                        @if($emolument->assessment && $emolument->assessment->assessor)
                                            {{ $emolument->assessment->assessor->name ?? $emolument->assessment->assessor->email }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-secondary-foreground">Date:</span>
                                    <span class="text-foreground ml-1">
                                        {{ $emolument->assessed_at ? $emolument->assessed_at->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-8 text-muted-foreground">
                            <div class="h-12 w-12 rounded-full bg-secondary flex items-center justify-center mb-3 mx-auto">
                                <i class="ki-filled ki-check-square text-xl text-muted-foreground"></i>
                            </div>
                            <p class="text-base font-medium">No pending validations</p>
                            <p class="text-sm text-muted-foreground mt-1">All assessed emoluments have been validated</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @if($emoluments->hasPages())
                <div class="kt-card-footer border-t border-border">
                    {{ $emoluments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Pharmacy Stock')
@section('page-title', 'Pharmacy Stock')
@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="#">Pharmacy</a>
    <span>/</span>
    <span class="text-primary">Stock</span>
@endsection

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
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

        <!-- Filters -->
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    @if(!auth()->user()->hasRole('Command Pharmacist') || auth()->user()->hasRole('Comptroller Pharmacy') || auth()->user()->hasRole('Central Medical Store'))
                    <div class="w-full sm:w-auto min-w-[180px]">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Location Type</label>
                        <select name="location_type" class="kt-input" onchange="this.form.submit()">
                            <option value="CENTRAL_STORE" {{ $locationType === 'CENTRAL_STORE' ? 'selected' : '' }}>Central Medical Store</option>
                            <option value="COMMAND_PHARMACY" {{ $locationType === 'COMMAND_PHARMACY' ? 'selected' : '' }}>Command Pharmacy</option>
                        </select>
                    </div>
                    @endif
                    @if($locationType === 'COMMAND_PHARMACY' && $commands->count() > 0)
                        <div class="w-full sm:w-48">
                            <label class="block text-sm font-medium text-secondary-foreground mb-1">Command</label>
                            <select name="command_id" class="kt-input" onchange="this.form.submit()">
                                <option value="">All Commands</option>
                                @foreach($commands as $command)
                                    <option value="{{ $command->id }}" {{ $commandId == $command->id ? 'selected' : '' }}>
                                        {{ $command->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex-grow min-w-[200px]">
                        <label class="block text-sm font-medium text-secondary-foreground mb-1">Search Drug / Item</label>
                        <input type="text" name="search" class="kt-input" 
                               value="{{ $search }}" placeholder="Search by name...">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-magnifier"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="kt-card overflow-hidden">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    {{ $locationType === 'CENTRAL_STORE' ? 'Central Medical Store Stock' : 'Command Pharmacy Stock' }}
                </h3>
                <div class="kt-card-toolbar">
                    @if(auth()->user()->hasRole('Comptroller Pharmacy'))
                        <a href="{{ route('pharmacy.reports.stock-balance') }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                            <i class="ki-filled ki-chart-line"></i> Reports
                        </a>
                    @endif
                </div>
            </div>
            <div class="kt-card-content p-0 md:p-5 overflow-x-hidden">
                @if($stocks->count() > 0)
                    <!-- Swipe hint for mobile -->
                    <div class="px-5 pb-5 lg:hidden">
                        <div class="flex items-center gap-2 text-xs text-secondary-foreground bg-secondary/5 p-2 rounded">
                            <i class="ki-filled ki-information-2 text-primary"></i>
                            <span>Swipe left to view more columns</span>
                        </div>
                    </div>

                    <!-- Table with horizontal scroll wrapper -->
                    <div class="table-scroll-wrapper overflow-x-auto -webkit-overflow-scrolling-touch scrollbar-thin">
                        <table class="kt-table" style="min-width: 1000px; width: 100%;">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Drug / Item</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Category</th>
                                    @if($locationType === 'COMMAND_PHARMACY')
                                        <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Command</th>
                                    @endif
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Quantity</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Unit</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Batch</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Expiry Date</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground" style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                    <tr class="border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <span class="text-sm font-medium text-foreground">{{ $stock->drug->name ?? 'Unknown' }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $stock->drug->category ?? '-' }}</td>
                                        @if($locationType === 'COMMAND_PHARMACY')
                                            <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $stock->command->name ?? '-' }}</td>
                                        @endif
                                        <td class="py-3 px-4 text-sm font-medium">
                                            <span class="{{ $stock->quantity < 10 ? 'text-danger' : 'text-foreground' }}">
                                                {{ number_format($stock->quantity) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $stock->drug->unit_of_measure ?? 'units' }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-foreground">{{ $stock->batch_number ?? '-' }}</td>
                                        <td class="py-3 px-4">
                                            @if($stock->expiry_date)
                                                @php
                                                    $daysUntilExpiry = $stock->getDaysUntilExpiry();
                                                    $warningLevel = $stock->getExpiryWarningLevel();
                                                    $expiryClass = match($warningLevel) {
                                                        'expired' => 'text-danger font-semibold',
                                                        'critical' => 'text-danger font-semibold',
                                                        'warning' => 'text-warning font-semibold',
                                                        'caution' => 'text-info font-semibold',
                                                        default => ''
                                                    };
                                                @endphp
                                                <div class="flex flex-col">
                                                    <span class="text-sm {{ $expiryClass }}">
                                                        {{ $stock->expiry_date->format('d M Y') }}
                                                    </span>
                                                    @if($daysUntilExpiry !== null && $daysUntilExpiry >= 0)
                                                        <span class="text-[10px] uppercase tracking-wider {{ $expiryClass }}">
                                                            @if($daysUntilExpiry === 0)
                                                                Expires today!
                                                            @elseif($daysUntilExpiry === 1)
                                                                1 day left
                                                            @else
                                                                {{ $daysUntilExpiry }} days left
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-secondary-foreground">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($stock->isExpired())
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-cross-circle"></i> Expired
                                                </span>
                                            @elseif($stock->isExpiringVerySoon(30))
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Critical
                                                </span>
                                            @elseif($stock->isExpiringModerately(60))
                                                <span class="kt-badge kt-badge-warning kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Warning
                                                </span>
                                            @elseif($stock->isExpiringSoon(90))
                                                <span class="kt-badge kt-badge-info kt-badge-sm">
                                                    <i class="ki-filled ki-information"></i> Caution
                                                </span>
                                            @elseif($stock->quantity < 10)
                                                <span class="kt-badge kt-badge-danger kt-badge-sm">
                                                    <i class="ki-filled ki-arrow-down"></i> Low Stock
                                                </span>
                                            @else
                                                <span class="kt-badge kt-badge-success kt-badge-sm">
                                                    <i class="ki-filled ki-check-circle"></i> OK
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('pharmacy.stocks.show', $stock->pharmacy_drug_id) }}" class="kt-btn kt-btn-sm kt-btn-ghost">
                                                <i class="ki-filled ki-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-5 pb-5">
                        {{ $stocks->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="ki-filled ki-package text-5xl text-muted-foreground mb-4"></i>
                        <p class="text-secondary-foreground">No stock records found.</p>
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

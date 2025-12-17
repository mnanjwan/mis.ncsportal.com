@extends('layouts.app')

@section('title', 'Deceased Officers')
@section('page-title', 'Deceased Officers')

@section('breadcrumbs')
    <a class="text-secondary-foreground hover:text-primary" href="{{ route('accounts.dashboard') }}">Accounts</a>
    <span>/</span>
    <span class="text-primary">Deceased Officers</span>
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
                <h3 class="kt-card-title">Filter Deceased Officers</h3>
            </div>
            <div class="kt-card-content">
                <form method="GET" action="{{ route('accounts.deceased-officers') }}" class="flex flex-col gap-4">
                    <!-- Filter Controls -->
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <!-- Search Input -->
                        <div class="flex-1 min-w-[250px]">
                            <div class="relative">
                                <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search Service No or Name..." 
                                       class="kt-input w-full pl-10">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-shrink-0">
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-filter"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search']))
                                <a href="{{ route('accounts.deceased-officers') }}" class="kt-btn kt-btn-outline">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deceased Officers List Card -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Deceased Officers</h3>
                <div class="kt-card-toolbar">
                    <button class="kt-btn kt-btn-sm kt-btn-outline">
                        <i class="ki-filled ki-file-down"></i> Export Report
                    </button>
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
                                        Officer Details
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Service No
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Date of Death
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Next of Kin
                                    </th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Status
                                    </th>
                                    <th class="text-right py-3 px-4 font-semibold text-sm text-secondary-foreground">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="officers-table-body">
                                <tr>
                                    <td colspan="6" class="text-center p-8 text-secondary-foreground">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                                            <p class="text-base font-medium">No deceased officers found</p>
                                            <p class="text-sm text-muted-foreground mt-1">Records will appear here when reported</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="flex flex-col gap-4">
                        <div class="text-center py-12">
                            <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                            <p class="text-secondary-foreground">No deceased officers found</p>
                            <p class="text-sm text-muted-foreground mt-1">Records will appear here when reported</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Placeholder for future API integration
            document.addEventListener('DOMContentLoaded', async () => {
                // API call will be implemented here
            });
        </script>
    @endpush
@endsection

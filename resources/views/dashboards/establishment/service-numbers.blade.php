@extends('layouts.app')

@section('title', 'Service Number Allocation')
@section('page-title', 'Service Number Allocation')

@section('content')
    <div class="grid gap-5 lg:gap-7.5">
        <!-- Actions -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-mono">Service Number Allocation</h2>
            <div class="flex items-center gap-3">
                <button class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-plus"></i> Allocate New Batch
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="kt-card p-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="ki-filled ki-user-tick text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Last Allocated</p>
                        <h3 class="text-2xl font-bold text-foreground">57616</h3>
                    </div>
                </div>
            </div>
            <div class="kt-card p-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                        <i class="ki-filled ki-chart-line-up text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Next Available</p>
                        <h3 class="text-2xl font-bold text-foreground">57617</h3>
                    </div>
                </div>
            </div>
            <div class="kt-card p-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                        <i class="ki-filled ki-calendar text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Allocated Today</p>
                        <h3 class="text-2xl font-bold text-foreground">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="kt-card">
            <div class="kt-card-header border-b border-input p-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-mono">Recent Allocations</h3>
                <div class="relative">
                    <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" class="kt-input pl-10" placeholder="Search service number..." style="width: 250px;">
                </div>
            </div>
            <div class="kt-card-content p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-input bg-muted/20">
                                <th class="text-left p-4 text-sm font-semibold text-mono">Service No</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Officer Name</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Rank</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Date Allocated</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono">Allocated By</th>
                                <th class="text-left p-4 text-sm font-semibold text-mono text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-input">
                            <tr>
                                <td colspan="6" class="text-center p-8 text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="h-12 w-12 rounded-full bg-secondary flex items-center justify-center mb-3">
                                            <i class="ki-filled ki-search-list text-xl text-muted-foreground"></i>
                                        </div>
                                        <p class="text-base font-medium">No recent allocations</p>
                                        <p class="text-sm text-muted-foreground mt-1">Start by allocating a new batch</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection